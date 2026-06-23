<?php
require_once __DIR__ . '/admin-config.php';
require_once __DIR__ . '/../../source/php/config.php';

session_name('vp_admin');
session_start();

/* ===== Хелперы ===== */

function isLoggedIn(): bool {
	return !empty($_SESSION['admin_logged_in']);
}

function requireLogin(): void {
	if (!isLoggedIn()) {
		header('Location: /admin/');
		exit;
	}
}

// Перезаписываем admin-config.php с новыми значениями
function saveAdminConfig(string $login, string $password, string $email): void {
	$configPath = __DIR__ . '/admin-config.php';
	$login    = addslashes($login);
	$password = addslashes($password);
	$email    = addslashes($email);

	$content = "<?php\n";
	$content .= "// Запрещаем прямой вызов\n";
	$content .= "if (basename(__FILE__) === basename(\$_SERVER['SCRIPT_FILENAME'])) {\n";
	$content .= "\thttp_response_code(403); exit;\n";
	$content .= "}\n\n";
	$content .= "/* ===== Учётные данные ===== */\n";
	$content .= "define('ADMIN_LOGIN',    '{$login}');\n";
	$content .= "define('ADMIN_PASSWORD', '{$password}');\n";
	$content .= "define('ADMIN_EMAIL',    '{$email}');\n";

	file_put_contents($configPath, $content);
}

// Генерируем случайный пароль
function generatePassword(int $len = 10): string {
	$chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789';
	$pass  = '';
	for ($i = 0; $i < $len; $i++) {
		$pass .= $chars[random_int(0, strlen($chars) - 1)];
	}
	return $pass;
}

// Уведомление в Telegram
function notifyTelegram(string $message): void {
	if (!defined('TG_TOKEN') || TG_TOKEN === 'TG_TOKEN-hid') return;

	$url     = 'https://api.telegram.org/bot' . TG_TOKEN . '/sendMessage';
	$payload = ['chat_id' => TG_CHAT_ID, 'text' => $message, 'parse_mode' => 'HTML'];

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_POST           => true,
		CURLOPT_POSTFIELDS     => http_build_query($payload),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT        => 8,
	]);
	curl_exec($ch);
	curl_close($ch);
}

// Уведомление на email
function notifyEmail(string $to, string $subject, string $body): bool {
	if ($to === '') return false;

	$headers  = "From: noreply@vectorpola.ru\r\n";
	$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
	return mail($to, $subject, $body, $headers);
}

/* ===== Защита от брутфорса ===== */

define('BRUTE_MAX_ATTEMPTS', 5);   // попыток до блокировки
define('BRUTE_LOCKOUT_SEC',  300); // блокировка 5 минут
define('BRUTE_FILE', sys_get_temp_dir() . '/vp_admin_brute.json');

function bruteLoad(): array {
	if (!file_exists(BRUTE_FILE)) return [];
	return json_decode(file_get_contents(BRUTE_FILE), true) ?: [];
}

function bruteSave(array $data): void {
	file_put_contents(BRUTE_FILE, json_encode($data));
}

function bruteGetIp(): string {
	return $_SERVER['HTTP_X_FORWARDED_FOR']
		? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]
		: ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function bruteIsLocked(): bool {
	$ip   = bruteGetIp();
	$data = bruteLoad();
	if (!isset($data[$ip])) return false;

	$entry = $data[$ip];
	// Сброс если время блокировки вышло
	if (time() - $entry['last'] > BRUTE_LOCKOUT_SEC) {
		unset($data[$ip]);
		bruteSave($data);
		return false;
	}
	return $entry['attempts'] >= BRUTE_MAX_ATTEMPTS;
}

function bruteRegisterFail(): void {
	$ip   = bruteGetIp();
	$data = bruteLoad();
	if (!isset($data[$ip])) {
		$data[$ip] = ['attempts' => 0, 'last' => time()];
	}
	$data[$ip]['attempts']++;
	$data[$ip]['last'] = time();
	bruteSave($data);
}

function bruteReset(): void {
	$ip   = bruteGetIp();
	$data = bruteLoad();
	unset($data[$ip]);
	bruteSave($data);
}

function bruteAttemptsLeft(): int {
	$ip   = bruteGetIp();
	$data = bruteLoad();
	if (!isset($data[$ip])) return BRUTE_MAX_ATTEMPTS;
	return max(0, BRUTE_MAX_ATTEMPTS - $data[$ip]['attempts']);
}

/* ===== Капча ===== */

function captchaGenerate(): array {
	$a = random_int(1, 9);
	$b = random_int(1, 9);
	$_SESSION['captcha_answer'] = $a + $b;
	return ['a' => $a, 'b' => $b];
}

function captchaCheck(string $input): bool {
	$expected = $_SESSION['captcha_answer'] ?? null;
	// Сбрасываем после проверки
	unset($_SESSION['captcha_answer']);
	return $expected !== null && (int)trim($input) === (int)$expected;
}

/* ===== Обработка POST-запросов ===== */

$authError   = '';
$authSuccess = '';

// Инициализируем капчу для первого показа
if (!isset($_SESSION['captcha_answer'])) {
	captchaGenerate();
}

// --- Вход ---
if (isset($_POST['action']) && $_POST['action'] === 'login') {

	if (bruteIsLocked()) {
		$authError = 'Слишком много неудачных попыток. Подождите ' . (BRUTE_LOCKOUT_SEC / 60) . ' минут.';

	} elseif (!captchaCheck($_POST['captcha'] ?? '')) {
		bruteRegisterFail();
		captchaGenerate();
		$left      = bruteAttemptsLeft();
		$authError = 'Неверный ответ на вопрос.' . ($left > 0 ? " Осталось попыток: {$left}." : '');

	} else {
		$login    = trim($_POST['login']    ?? '');
		$password = trim($_POST['password'] ?? '');

		if ($login === ADMIN_LOGIN && $password === ADMIN_PASSWORD) {
			bruteReset();
			$_SESSION['admin_logged_in'] = true;
			header('Location: /admin/dashboard.php');
			exit;
		} else {
			bruteRegisterFail();
			captchaGenerate();
			$left      = bruteAttemptsLeft();
			$authError = 'Неверный логин или пароль.' . ($left > 0 ? " Осталось попыток: {$left}." : ' Аккаунт временно заблокирован.');
		}
	}
}

// --- Выход ---
if (isset($_GET['logout'])) {
	session_destroy();
	header('Location: /admin/');
	exit;
}

// --- Восстановление пароля ---
if (isset($_POST['action']) && $_POST['action'] === 'recover') {
	if (ADMIN_EMAIL === '') {
		$authError = 'Email для восстановления не указан. Обратитесь к разработчику.';
	} else {
		$newPass = generatePassword();
		saveAdminConfig(ADMIN_LOGIN, $newPass, ADMIN_EMAIL);

		// Логин намеренно не включаем в сообщение
		$msg = "🔑 Новый пароль от админки Вектор пола:\n\n<b>{$newPass}</b>";
		notifyTelegram($msg);
		notifyEmail(ADMIN_EMAIL, 'Восстановление пароля — Вектор пола', "Новый пароль от панели управления Вектор пола:\n\n{$newPass}");

		$authSuccess = 'Новый пароль отправлен на ' . ADMIN_EMAIL;
	}
}
