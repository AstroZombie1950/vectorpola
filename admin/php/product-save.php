<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/products-data.php';
require_once __DIR__ . '/upload.php';
requireLogin();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405); echo json_encode(['ok' => false, 'error' => 'Method not allowed']); exit;
}

$action = $_POST['action'] ?? '';

/* ===== Удалить товар ===== */
if ($action === 'delete') {
	$id = $_POST['id'] ?? '';
	if (!$id) { echo json_encode(['ok' => false, 'error' => 'Не указан ID']); exit; }

	$p = productGet($id);
	if ($p) {
		// Удаляем изображения с диска
		foreach ($p['images'] ?? [] as $imgUrl) {
			imageDelete($imgUrl);
		}
		productDelete($id);
	}
	echo json_encode(['ok' => true]);
	exit;
}

/* ===== Удалить одно изображение ===== */
if ($action === 'delete_image') {
	$id  = $_POST['id']  ?? '';
	$url = $_POST['url'] ?? '';
	$p   = $id ? productGet($id) : null;

	if ($p) {
		imageDelete($url);
		$p['images'] = array_values(array_filter($p['images'], fn($u) => $u !== $url));
		productUpsert($p);
	}
	echo json_encode(['ok' => true]);
	exit;
}

/* ===== Загрузить изображение ===== */
if ($action === 'upload_image') {
	$file = $_FILES['image'] ?? null;
	if (!$file) { echo json_encode(['ok' => false, 'error' => 'Файл не передан']); exit; }

	$result = imageUploadFromFile($file);
	echo json_encode($result);
	exit;
}

/* ===== Сохранить товар (создать / обновить) ===== */
if ($action === 'save') {
	$id   = $_POST['id'] ?? '';
	$existing = $id ? productGet($id) : null;

	// Specs из JSON-строки
	$specs = [];
	$rawSpecs = $_POST['specs'] ?? '';
	if ($rawSpecs) {
		$decoded = json_decode($rawSpecs, true);
		if (is_array($decoded)) $specs = $decoded;
	}

	// Изображения: берём существующий список из POST (порядок мог измениться)
	$images = [];
	$rawImages = $_POST['images'] ?? '';
	if ($rawImages) {
		$decoded = json_decode($rawImages, true);
		if (is_array($decoded)) $images = $decoded;
	}

	// Если загружено новое фото — добавляем в начало
	if (!empty($_FILES['new_image']['name'])) {
		$result = imageUploadFromFile($_FILES['new_image']);
		if ($result['ok']) {
			array_unshift($images, $result['url']);
		}
	}

	$product = productNormalize([
		'name'            => $_POST['name']            ?? '',
		'slug'            => $_POST['slug']            ?? '',
		'sku'             => $_POST['sku']             ?? '',
		'category'        => $_POST['category']        ?? '',
		'brand'           => $_POST['brand']           ?? '',
		'price'           => $_POST['price']           ?? 0,
		'old_price'       => $_POST['old_price']       ?? '',
		'unit'            => $_POST['unit']            ?? 'м²',
		'pack_area'       => $_POST['pack_area']       ?? 0,
		'in_stock'        => !empty($_POST['in_stock']),
		'description'     => $_POST['description']     ?? '',
		'images'          => $images,
		'specs'           => $specs,
		'seo_title'       => $_POST['seo_title']       ?? '',
		'seo_description' => $_POST['seo_description'] ?? '',
		'active'          => !empty($_POST['active']),
	], $existing ? $existing['id'] : null);

	if (productUpsert($product)) {
		echo json_encode(['ok' => true, 'id' => $product['id']]);
	} else {
		echo json_encode(['ok' => false, 'error' => 'Ошибка записи данных']);
	}
	exit;
}

echo json_encode(['ok' => false, 'error' => 'Неизвестное действие']);