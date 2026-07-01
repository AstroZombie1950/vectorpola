<?php
/* ===================================================
   Приём формы подписки на акции с главной.
   Пишем в data/subscribers.json (источник для админки).
   Telegram НЕ дёргаем — по решению, только хранилище.
   Папка /data/ закрыта в .htaccess и robots.
   =================================================== */

header('Content-Type: application/json; charset=utf-8');

/* Только POST */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	http_response_code(405);
	echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
	exit;
}

/* ===== Читаем и чистим поля ===== */
$name  = trim(strip_tags($_POST['name']  ?? ''));
$email = trim(strip_tags($_POST['email'] ?? ''));
$phone = trim(strip_tags($_POST['phone'] ?? ''));

/* ===== Валидация (все три поля обязательны) ===== */
$errors = [];

if ($name === '' || mb_strlen($name) < 2) {
	$errors[] = 'Укажите имя';
}
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
	$errors[] = 'Укажите корректный e-mail';
}
$phoneDigits = preg_replace('/\D/', '', $phone);
if ($phone === '' || strlen($phoneDigits) < 10 || strlen($phoneDigits) > 12) {
	$errors[] = 'Укажите корректный телефон';
}

if (!empty($errors)) {
	http_response_code(422);
	echo json_encode(['ok' => false, 'errors' => $errors]);
	exit;
}

/* ===== Пишем в хранилище (лок + дедуп по email) ===== */
$file = __DIR__ . '/../../data/subscribers.json';

$fp = @fopen($file, 'c+');   // c+ — создаёт файл, если нет, курсор в начале
if (!$fp) {
	error_log('subscribe.php: не открыть ' . $file);
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'Не удалось сохранить']);
	exit;
}

flock($fp, LOCK_EX);
$raw  = stream_get_contents($fp);
$list = $raw ? (json_decode($raw, true) ?: []) : [];

$now     = date('Y-m-d H:i:s');
$emailLc = mb_strtolower($email, 'UTF-8');

/* Дубли не плодим: тот же email — обновляем имя/телефон/дату */
$found = false;
foreach ($list as &$row) {
	if (mb_strtolower($row['email'] ?? '', 'UTF-8') === $emailLc) {
		$row['name']  = $name;
		$row['phone'] = $phone;
		$row['date']  = $now;
		$found = true;
		break;
	}
}
unset($row);

if (!$found) {
	$list[] = ['name' => $name, 'email' => $email, 'phone' => $phone, 'date' => $now];
}

/* Перезаписываем файл целиком */
ftruncate($fp, 0);
rewind($fp);
$ok = fwrite($fp, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
fflush($fp);
flock($fp, LOCK_UN);
fclose($fp);

if ($ok === false) {
	error_log('subscribe.php: ошибка записи в ' . $file);
	http_response_code(500);
	echo json_encode(['ok' => false, 'error' => 'Не удалось сохранить']);
	exit;
}

echo json_encode(['ok' => true]);