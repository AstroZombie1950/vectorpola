<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

/* Принимаем только POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
	exit;
}

/* ===== Читаем и чистим поля ===== */
$name    = trim(strip_tags($_POST['name']    ?? ''));
$phone   = trim(strip_tags($_POST['phone']   ?? ''));
$email   = trim(strip_tags($_POST['email']   ?? ''));  // необязательное (только форма дизайнерам)
$address = trim(strip_tags($_POST['address'] ?? ''));  // только форма доставки
$comment = trim(strip_tags($_POST['comment'] ?? ''));
$source  = trim(strip_tags($_POST['source']  ?? 'Сайт'));

/* Поля карточки товара / заказа (необязательные) */
$product  = trim(strip_tags($_POST['product']  ?? ''));
$quantity = trim(strip_tags($_POST['quantity'] ?? ''));
$total    = trim(strip_tags($_POST['total']    ?? ''));
$items    = trim(strip_tags($_POST['items']    ?? ''));  // состав заказа из корзины (многострочно)

/* ===== Валидация ===== */
$errors = [];

if ($name === '' || mb_strlen($name) < 2) {
	$errors[] = 'Укажите имя';
}

$phoneDigits = preg_replace('/\D/', '', $phone);
if ($phone === '' || strlen($phoneDigits) < 10 || strlen($phoneDigits) > 12) {
	$errors[] = 'Укажите корректный номер телефона';
}

/* E-mail валидируем только если поле пришло непустым */
if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = 'Укажите корректный e-mail';
}

if (!empty($errors)) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'errors' => $errors]);
	exit;
}

/* ===== Собираем сообщение для Telegram ===== */
$lines = [];
$lines[] = "📋 <b>Новая заявка</b> · {$source}";
$lines[] = "👤 <b>Имя:</b> {$name}";
$lines[] = "📞 <b>Телефон:</b> {$phone}";

if ($email !== '') {
	$lines[] = "✉️ <b>E-mail:</b> {$email}";
}
if ($address !== '') {
	$lines[] = "📍 <b>Адрес доставки:</b> {$address}";
}
if ($product !== '') {
	$lines[] = "🛒 <b>Товар:</b> {$product}";
}
if ($quantity !== '') {
	$lines[] = "🔢 <b>Количество:</b> {$quantity}";
}
if ($items !== '') {
	$lines[] = "🛒 <b>Состав заказа:</b>\n{$items}";
}
if ($total !== '') {
	$lines[] = "💰 <b>Сумма:</b> {$total}";
}
if ($comment !== '') {
	$lines[] = "💬 <b>Комментарий:</b> {$comment}";
}

$text = implode("\n", $lines);

/* ===== Отправляем в Telegram ===== */
$url = "https://api.telegram.org/bot" . TG_TOKEN . "/sendMessage";
$payload = [
	'chat_id'    => TG_CHAT_ID,
	'text'       => $text,
	'parse_mode' => 'HTML',
];

$ch = curl_init($url);
curl_setopt_array($ch, [
	CURLOPT_POST           => true,
	CURLOPT_POSTFIELDS     => http_build_query($payload),
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_TIMEOUT        => 10,
]);
$result = curl_exec($ch);
$err    = curl_error($ch);
curl_close($ch);

if ($err) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'Ошибка отправки']);
	exit;
}

$tgResponse = json_decode($result, true);
if (empty($tgResponse['ok'])) {
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'Telegram отклонил запрос']);
	exit;
}

echo json_encode(['ok' => true]);