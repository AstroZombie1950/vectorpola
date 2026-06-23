<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/products-data.php';
require_once __DIR__ . '/xlsx-writer.php';
requireLogin();

$format = $_GET['format'] ?? '';
$mode   = $_GET['mode']   ?? 'export'; // export | template

$products   = productsLoad();
$categories = productCategories();

// Фиксированные заголовки
$fixedHeaders = [
	'Название',
	'Артикул',
	'Категория',
	'Бренд',
	'Цена',
	'Единица',
	'Описание',
	'Изображение',
	'Активен (1/0)',
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
			$row = [
				$p['name']        ?? '',
				$p['sku']         ?? '',
				$categories[$p['category'] ?? ''] ?? ($p['category'] ?? ''),
				$p['brand']       ?? '',
				$p['price']       ?? '',
				$p['unit']        ?? '',
				$p['description'] ?? '',
				$p['images'][0]   ?? '',
				$p['active'] ? '1' : '0',
			];
			foreach ($specKeys as $k) {
				$row[] = $p['specs'][$k] ?? '';
			}
			$w->writeRow($row, $i++);
		}
	} else {
		// Пустой шаблон — одна строка-пример
		$example = [
			'Пример: Ламинат Quick-Step Impressive 8мм',
			'QS-IM-001',
			'Ламинат',
			'Quick-Step',
			'1890',
			'м²',
			'Описание товара',
			'https://example.com/image.jpg',
			'1',
		];
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

	// UTF-8 BOM чтобы Excel открывал корректно
	fwrite($out, "\xEF\xBB\xBF");

	// Заголовки
	fputcsv($out, $allHeaders, ';');

	if ($mode === 'export') {
		foreach ($products as $p) {
			$row = [
				$p['name']        ?? '',
				$p['sku']         ?? '',
				$categories[$p['category'] ?? ''] ?? ($p['category'] ?? ''),
				$p['brand']       ?? '',
				$p['price']       ?? '',
				$p['unit']        ?? '',
				$p['description'] ?? '',
				$p['images'][0]   ?? '',
				$p['active'] ? '1' : '0',
			];
			foreach ($specKeys as $k) {
				$row[] = $p['specs'][$k] ?? '';
			}
			fputcsv($out, $row, ';');
		}
	} else {
		// Шаблон — одна строка-пример
		$example = [
			'Пример: Ламинат Quick-Step Impressive 8мм',
			'QS-IM-001',
			'Ламинат',
			'Quick-Step',
			'1890',
			'м²',
			'Описание товара',
			'https://example.com/image.jpg',
			'1',
		];
		foreach ($specKeys as $k) $example[] = '';
		fputcsv($out, $example, ';');
	}

	fclose($out);
	exit;
}

// Неизвестный формат
http_response_code(400);
echo 'Неизвестный формат';
