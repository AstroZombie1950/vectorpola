<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

define('PRODUCTS_FILE', $_SERVER['DOCUMENT_ROOT'] . '/data/products.json');
define('UPLOADS_DIR',   $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/');
define('UPLOADS_URL',   '/uploads/products/');

/* ===== Чтение всех товаров ===== */
function productsLoad(): array {
	if (!file_exists(PRODUCTS_FILE)) return [];
	$json = file_get_contents(PRODUCTS_FILE);
	return json_decode($json, true) ?: [];
}

/* ===== Запись всех товаров ===== */
function productsSave(array $products): bool {
	$dir = dirname(PRODUCTS_FILE);
	if (!is_dir($dir)) mkdir($dir, 0755, true);
	return (bool) file_put_contents(
		PRODUCTS_FILE,
		json_encode(array_values($products), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
	);
}

/* ===== Получить один товар по ID ===== */
function productGet(string $id): ?array {
	foreach (productsLoad() as $p) {
		if ($p['id'] === $id) return $p;
	}
	return null;
}

/* ===== Сохранить / обновить один товар ===== */
function productUpsert(array $product): bool {
	$products = productsLoad();
	foreach ($products as $i => $p) {
		if ($p['id'] === $product['id']) {
			$products[$i] = $product;
			return productsSave($products);
		}
	}
	// Новый — добавляем в конец
	$products[] = $product;
	return productsSave($products);
}

/* ===== Удалить товар по ID ===== */
function productDelete(string $id): bool {
	$products = productsLoad();
	$filtered = array_filter($products, fn($p) => $p['id'] !== $id);
	return productsSave($filtered);
}

/* ===== Генерация нового ID ===== */
function productNewId(): string {
	return 'p_' . bin2hex(random_bytes(6));
}

/* ===== Список категорий (из ТЗ) ===== */
function productCategories(): array {
	return [
		'laminat'          => 'Ламинат',
		'kvartsvinil'      => 'Кварцвинил / SPC',
		'vinilovye'        => 'Виниловые полы',
		'inzhenernaya'     => 'Инженерная доска',
		'parketnaya'       => 'Паркетная доска',
		'massiv'           => 'Массивная доска',
		'probka'           => 'Пробковые покрытия',
		'plintus'          => 'Плинтусы и подложка',
		'soputstvuyushchie'=> 'Сопутствующие товары',
	];
}

/* ===== Единицы измерения ===== */
function productUnits(): array {
	return ['м²' => 'м²', 'уп.' => 'уп.', 'шт.' => 'шт.', 'пм' => 'пм'];
}

/* ===== Нормализация товара из POST / импорта ===== */
function productNormalize(array $raw, ?string $existingId = null): array {
	$cats = productCategories();

	return [
		'id'          => $existingId ?? productNewId(),
		'name'        => trim($raw['name']     ?? ''),
		'sku'         => trim($raw['sku']      ?? ''),
		'category'    => isset($cats[$raw['category'] ?? '']) ? $raw['category'] : '',
		'brand'       => trim($raw['brand']    ?? ''),
		'price'       => (float) str_replace([' ', ','], ['', '.'], $raw['price'] ?? 0),
		'unit'        => $raw['unit']          ?? 'м²',
		'description' => trim($raw['description'] ?? ''),
		'images'      => (array) ($raw['images'] ?? []),
		'specs'       => (array) ($raw['specs']  ?? []),
		'active'      => !empty($raw['active']),
		'updated_at'  => date('Y-m-d H:i:s'),
	];
}
