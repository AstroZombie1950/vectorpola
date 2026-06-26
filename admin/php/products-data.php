<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

// Единый источник категорий и slug-хелперы
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';

define('PRODUCTS_FILE', $_SERVER['DOCUMENT_ROOT'] . '/data/products.json');
define('UPLOADS_DIR',   $_SERVER['DOCUMENT_ROOT'] . '/uploads/products/');
define('UPLOADS_URL',   '/uploads/products/');

/* ===== Чтение всех товаров =====
   Формат файла: {"products":[...]} — один и тот же для фронта и админки. */
function productsLoad(): array {
	if (!file_exists(PRODUCTS_FILE)) return [];
	$json = file_get_contents(PRODUCTS_FILE);
	$data = json_decode($json, true);
	if (!is_array($data)) return [];
	// поддержка обёртки и (на всякий) старого голого массива
	return $data['products'] ?? (isset($data[0]) ? $data : []);
}

/* ===== Запись всех товаров (всегда в обёртке) ===== */
function productsSave(array $products): bool {
	$dir = dirname(PRODUCTS_FILE);
	if (!is_dir($dir)) mkdir($dir, 0755, true);
	return (bool) file_put_contents(
		PRODUCTS_FILE,
		json_encode(['products' => array_values($products)], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
	);
}

/* ===== Получить один товар по ID ===== */
function productGet(string $id): ?array {
	foreach (productsLoad() as $p) {
		if (($p['id'] ?? '') === $id) return $p;
	}
	return null;
}

/* ===== Сохранить / обновить один товар ===== */
function productUpsert(array $product): bool {
	$products = productsLoad();
	foreach ($products as $i => $p) {
		if (($p['id'] ?? '') === $product['id']) {
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
	$filtered = array_filter($products, fn($p) => ($p['id'] ?? '') !== $id);
	return productsSave($filtered);
}

/* ===== Генерация нового ID ===== */
function productNewId(): string {
	return 'p_' . bin2hex(random_bytes(6));
}

/* ===== Список категорий — из единого VP_CATEGORIES ===== */
function productCategories(): array {
	return VP_CATEGORIES;
}

/* ===== Единицы измерения ===== */
function productUnits(): array {
	return ['м²' => 'м²', 'уп.' => 'уп.', 'шт.' => 'шт.', 'пм' => 'пм'];
}

/* ===== Уникальный slug (с учётом существующих, кроме самого товара) ===== */
function productUniqueSlug(string $slug, ?string $excludeId = null): string {
	$slug = vp_slugify($slug);
	$taken = [];
	foreach (productsLoad() as $p) {
		if (($p['id'] ?? '') === $excludeId) continue;
		if (!empty($p['slug'])) $taken[$p['slug']] = true;
	}
	if (empty($taken[$slug])) return $slug;
	$i = 2;
	while (!empty($taken[$slug . '-' . $i])) $i++;
	return $slug . '-' . $i;
}

/* ===== Нормализация товара из POST / импорта ===== */
function productNormalize(array $raw, ?string $existingId = null): array {
	$cats = productCategories();
	$id   = $existingId ?? productNewId();

	// slug: берём явный, иначе из name; гарантируем уникальность
	$slugRaw = trim($raw['slug'] ?? '');
	$slug    = productUniqueSlug($slugRaw !== '' ? $slugRaw : ($raw['name'] ?? ''), $existingId);

	// old_price: пусто/0 → null (нет скидки)
	$oldRaw   = str_replace([' ', ','], ['', '.'], (string)($raw['old_price'] ?? ''));
	$oldPrice = ($oldRaw !== '' && (float)$oldRaw > 0) ? (float)$oldRaw : null;

	// pack_area: м² в упаковке
	$packArea = (float) str_replace([' ', ','], ['', '.'], (string)($raw['pack_area'] ?? 0));

	return [
		'id'              => $id,
		'slug'            => $slug,
		'name'            => trim($raw['name'] ?? ''),
		'sku'             => trim($raw['sku'] ?? ''),
		'category'        => isset($cats[$raw['category'] ?? '']) ? $raw['category'] : '',
		'brand'           => trim($raw['brand'] ?? ''),
		'price'           => (float) str_replace([' ', ','], ['', '.'], (string)($raw['price'] ?? 0)),
		'old_price'       => $oldPrice,
		'unit'            => $raw['unit'] ?? 'м²',
		'pack_area'       => $packArea,
		'in_stock'        => !empty($raw['in_stock']),
		'description'     => trim($raw['description'] ?? ''),
		'images'          => (array) ($raw['images'] ?? []),
		'specs'           => (array) ($raw['specs'] ?? []),
		'seo_title'       => trim($raw['seo_title'] ?? ''),
		'seo_description' => trim($raw['seo_description'] ?? ''),
		'active'          => !empty($raw['active']),
		'updated_at'      => date('Y-m-d H:i:s'),
	];
}