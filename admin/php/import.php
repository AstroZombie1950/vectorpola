<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/products-data.php';
require_once __DIR__ . '/upload.php';
require_once __DIR__ . '/xlsx-reader.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405); echo json_encode(['ok' => false, 'error' => 'Method not allowed']); exit;
}

$format = $_POST['format'] ?? '';
$file   = $_FILES['file']  ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
	echo json_encode(['ok' => false, 'error' => 'Файл не загружен']); exit;
}

// ===== Читаем строки =====
try {
	if ($format === 'xlsx') {
		$reader = new XlsxReader($file['tmp_name']);
		$rows   = $reader->getRows();
	} elseif ($format === 'csv') {
		$rows = csvRead($file['tmp_name']);
	} else {
		echo json_encode(['ok' => false, 'error' => 'Неизвестный формат']); exit;
	}
} catch (Throwable $e) {
	echo json_encode(['ok' => false, 'error' => 'Ошибка чтения файла: ' . $e->getMessage()]); exit;
}

if (count($rows) < 2) {
	echo json_encode(['ok' => false, 'error' => 'Файл пустой или содержит только заголовки']); exit;
}

// ===== Заголовки из первой строки =====
$headers   = array_map('trim', $rows[0]);
$dataRows  = array_slice($rows, 1);
$categories = productCategories();

// Фиксированные столбцы по имени
$fixedMap  = [
	'Название'        => 'name',
	'URL (slug)'      => 'slug',
	'Артикул'         => 'sku',
	'Категория'       => 'category',
	'Бренд'           => 'brand',
	'Цена'            => 'price',
	'Старая цена'     => 'old_price',
	'Единица'         => 'unit',
	'м² в упаковке'   => 'pack_area',
	'В наличии (1/0)' => 'in_stock',
	'Описание'        => 'description',
	'Изображение'     => 'image',
	'Активен (1/0)'   => 'active',
	'SEO title'       => 'seo_title',
	'SEO description' => 'seo_description',
];

// Определяем какие столбцы фиксированные, какие — specs
$colMap = [];
foreach ($headers as $i => $h) {
	if (isset($fixedMap[$h])) {
		$colMap[$i] = ['type' => 'fixed', 'key' => $fixedMap[$h]];
	} elseif ($h !== '') {
		$colMap[$i] = ['type' => 'spec', 'key' => $h];
	}
}

/* ===== Нормализация артикула ===== */
// Google Таблицы и Excel сохраняют целые числа как "1.0" — приводим к "1"
function skuNormalize(string $sku): string {
	$sku = trim($sku);
	// Если выглядит как число с нулевой дробной частью — обрезаем
	if (preg_match('/^\d+\.0+$/', $sku)) {
		$sku = (string)(int)$sku;
	}
	return $sku;
}

// ===== Существующие товары (для upsert по slug, затем по артикулу) =====
$existing  = productsLoad();
$bySku     = [];
$bySlug    = [];
foreach ($existing as $p) {
	$skuKey = skuNormalize($p['sku'] ?? '');
	if ($skuKey !== '') $bySku[$skuKey] = $p;
	if (!empty($p['slug'])) $bySlug[$p['slug']] = $p;
}

$created = 0;
$updated = 0;
$errors  = [];

foreach ($dataRows as $rowIdx => $row) {
	$rowNum  = $rowIdx + 2; // номер строки для сообщений об ошибках
	$fixed   = [];
	$specs   = [];

	foreach ($colMap as $i => $col) {
		$val = trim($row[$i] ?? '');
		if ($col['type'] === 'fixed') {
			$fixed[$col['key']] = $val;
		} else {
			if ($val !== '') $specs[$col['key']] = $val;
		}
	}

	// Пропускаем пустые строки
	if (($fixed['name'] ?? '') === '') continue;

	// Категория: ищем по названию
	$catKey = '';
	foreach ($categories as $k => $v) {
		if (mb_strtolower($v) === mb_strtolower($fixed['category'] ?? '')) {
			$catKey = $k; break;
		}
	}

	// Upsert: сначала по slug, потом по артикулу (sku → 1.0 нормализуем)
	$slugCol = trim($fixed['slug'] ?? '');
	$sku     = skuNormalize(trim($fixed['sku'] ?? ''));
	$existing_p = null;
	if ($slugCol !== '' && isset($bySlug[$slugCol]))      $existing_p = $bySlug[$slugCol];
	elseif ($sku !== '' && isset($bySku[$sku]))           $existing_p = $bySku[$sku];

	// in_stock: если колонки нет — наследуем (или true для нового)
	$inStock = array_key_exists('in_stock', $fixed)
		? ($fixed['in_stock'] !== '0' && $fixed['in_stock'] !== '')
		: (isset($existing_p) ? !empty($existing_p['in_stock']) : true);

	$product = productNormalize([
		'name'            => $fixed['name']            ?? '',
		'slug'            => $slugCol,
		'sku'             => $sku,
		'category'        => $catKey,
		'brand'           => $fixed['brand']           ?? '',
		'price'           => $fixed['price']           ?? 0,
		'old_price'       => $fixed['old_price']       ?? '',
		'unit'            => $fixed['unit']            ?? 'м²',
		'pack_area'       => $fixed['pack_area']        ?? 0,
		'in_stock'        => $inStock,
		'description'     => $fixed['description']     ?? '',
		'images'          => $existing_p['images']     ?? [],
		'specs'           => $specs,
		'seo_title'       => $fixed['seo_title']       ?? '',
		'seo_description' => $fixed['seo_description'] ?? '',
		'active'          => ($fixed['active'] ?? '1') !== '0',
	], $existing_p['id'] ?? null);

	// Изображение по ссылке
	$imgUrl = trim($fixed['image'] ?? '');
	if ($imgUrl !== '' && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
		$result = imageUploadFromUrl($imgUrl);
		if ($result['ok']) {
			$product['images'] = array_merge([$result['url']], $product['images']);
		} else {
			$errors[] = "Строка {$rowNum}: не удалось загрузить фото — " . $result['error'];
		}
	}

	if ($existing_p) { $updated++; } else { $created++; }
	if (productUpsert($product) === false) {
		$errors[] = "Строка {$rowNum}: ошибка сохранения";
	}
}

echo json_encode([
	'ok'      => true,
	'created' => $created,
	'updated' => $updated,
	'errors'  => $errors,
]);

/* ===== Чтение CSV ===== */
function csvRead(string $path): array {
	// Читаем файл и убираем BOM
	$content = file_get_contents($path);
	$content = ltrim($content, "\xEF\xBB\xBF");

	// Определяем разделитель: ; или ,
	$firstLine = strtok($content, "\n");
	$sep       = substr_count($firstLine, ';') >= substr_count($firstLine, ',') ? ';' : ',';

	$rows   = [];
	$handle = fopen('data://text/plain,' . urlencode($content), 'r');
	while (($row = fgetcsv($handle, 0, $sep)) !== false) {
		$rows[] = $row;
	}
	fclose($handle);
	return $rows;
}