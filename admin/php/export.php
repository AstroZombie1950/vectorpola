<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/products-data.php';
require_once __DIR__ . '/xlsx-writer.php';
requireLogin();

$format = $_GET['format'] ?? '';
$mode   = $_GET['mode']   ?? 'export'; // export | template

$products   = productsLoad();
$categories = productCategories();

// Фиксированные заголовки (порядок = порядок колонок в файле)
$fixedHeaders = [
	'Название',
	'URL (slug)',
	'Артикул',
	'Категория',
	'Бренд',
	'Цена',
	'Старая цена',
	'Единица',
	'м² в упаковке',
	'В наличии (1/0)',
	'Описание',
	'Изображение',
	'Активен (1/0)',
	'SEO title',
	'SEO description',
];
$fixedCount = count($fixedHeaders);

// Собираем все уникальные ключи specs из существующих товаров
$specKeys = [];
foreach ($products as $p) {
	foreach (array_keys($p['specs'] ?? []) as $k) {
		if (!in_array($k, $specKeys, true)) $specKeys[] = $k;
	}
}

$allHeaders = array_merge($fixedHeaders, $specKeys);

/* Фиксированная часть строки товара (тот же порядок, что и $fixedHeaders) */
function exportFixedRow(array $p, array $categories): array {
	return [
		$p['name'] ?? '',
		$p['slug'] ?? '',
		$p['sku']  ?? '',
		$categories[$p['category'] ?? ''] ?? ($p['category'] ?? ''),
		$p['brand'] ?? '',
		$p['price'] ?? '',
		($p['old_price'] ?? null) ?: '',
		$p['unit'] ?? '',
		$p['pack_area'] ?? '',
		!empty($p['in_stock']) ? '1' : '0',
		$p['description'] ?? '',
		$p['images'][0] ?? '',
		!empty($p['active']) ? '1' : '0',
		$p['seo_title'] ?? '',
		$p['seo_description'] ?? '',
	];
}

/* Строка-пример для шаблона */
function exportExampleRow(): array {
	return [
		'Пример: Ламинат Quick-Step Impressive 8мм',
		'quick-step-impressive-8mm',
		'QS-IM-001',
		'Ламинат',
		'Quick-Step',
		'1890',
		'2190',
		'м²',
		'1.835',
		'1',
		'Описание товара',
		'https://example.com/image.jpg',
		'1',
		'',
		'',
	];
}

/* ===== XLSX ===== */
if ($format === 'xlsx') {
	$filename = $mode === 'template' ? 'шаблон_товары.xlsx' : 'товары_' . date('Y-m-d') . '.xlsx';

	$w = new XlsxWriter();
	$w->addSheet('Товары');
	$w->writeHeader($allHeaders, $fixedCount);
	$w->freezeFirstRow();

	if ($mode === 'export') {
		$i = 0;
		foreach ($products as $p) {
			$row = exportFixedRow($p, $categories);
			foreach ($specKeys as $k) $row[] = $p['specs'][$k] ?? '';
			$w->writeRow($row, $i++);
		}
	} else {
		$example = exportExampleRow();
		foreach ($specKeys as $k) $example[] = '';
		$w->writeRow($example, 0);
	}

	$w->output($filename);
	exit;
}

/* ===== CSV ===== */
if ($format === 'csv') {
	$filename = $mode === 'template' ? 'шаблон_товары.csv' : 'товары_' . date('Y-m-d') . '.csv';

	header('Content-Type: text/csv; charset=UTF-8');
	header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
	header('Cache-Control: no-cache');

	$out = fopen('php://output', 'w');
	fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM для Excel
	fputcsv($out, $allHeaders, ';');

	if ($mode === 'export') {
		foreach ($products as $p) {
			$row = exportFixedRow($p, $categories);
			foreach ($specKeys as $k) $row[] = $p['specs'][$k] ?? '';
			fputcsv($out, $row, ';');
		}
	} else {
		$example = exportExampleRow();
		foreach ($specKeys as $k) $example[] = '';
		fputcsv($out, $example, ';');
	}

	fclose($out);
	exit;
}

// Неизвестный формат
http_response_code(400);
echo 'Неизвестный формат';