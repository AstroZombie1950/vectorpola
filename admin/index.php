<?php
require_once __DIR__ . '/php/auth.php';

// Уже залогинен — сразу на дашборд
if (isLoggedIn()) {
	header('Location: /admin/dashboard.php');
	exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Вход — Вектор пола</title>
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body class="login-page">

<div class="login-wrap">
	<div class="login-logo">
		<svg width="160" height="80" viewBox="0 0 240 120" fill="none" xmlns="http://www.w3.org/2000/svg">
			<rect x="1" y="1" width="238" height="118" rx="16" stroke="#E7DFD2" stroke-width="2"/>
			<text x="120" y="58" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="32" font-weight="700" fill="#2B2F38">VP</text>
			<text x="120" y="82" text-anchor="middle" font-family="Inter, Arial, sans-serif" font-size="11" letter-spacing="3" fill="#6E6A62">vectorpola</text>
		</svg>
		<span>Панель управления</span>
	</div>

	<?php if ($authError): ?>
		<div class="alert alert--error"><?= htmlspecialchars($authError) ?></div>
	<?php endif; ?>
	<?php if ($authSuccess): ?>
		<div class="alert alert--success"><?= htmlspecialchars($authSuccess) ?></div>
	<?php endif; ?>

	<!-- Форма входа -->
	<form class="login-form" method="POST" action="">
		<input type="hidden" name="action" value="login">

		<div class="field">
			<label for="login">Логин</label>
			<input type="text" id="login" name="login" autocomplete="username" autofocus required>
		</div>

		<div class="field">
			<label for="password">Пароль</label>
			<input type="password" id="password" name="password" autocomplete="current-password" required>
		</div>

		<?php
			$cap = captchaGenerate();
		?>
		<div class="field">
			<label for="captcha">Сколько будет <?= $cap['a'] ?> + <?= $cap['b'] ?>?</label>
			<input type="number" id="captcha" name="captcha" autocomplete="off" required>
		</div>

		<?php if (bruteIsLocked()): ?>
			<div class="alert alert--error">Слишком много попыток. Подождите <?= BRUTE_LOCKOUT_SEC / 60 ?> минут.</div>
		<?php else: ?>
			<button type="submit" class="btn-primary btn-full">Войти</button>
		<?php endif; ?>
	</form>

	<!-- Восстановление пароля -->
	<div class="login-recover">
		<button type="button" class="link-btn" id="recoverToggle">Забыли пароль?</button>

		<form class="recover-form hidden" method="POST" action="" id="recoverForm">
			<input type="hidden" name="action" value="recover">
			<p class="recover-hint">Новый пароль будет отправлен на email, указанный в настройках.</p>
			<button type="submit" class="btn-secondary btn-full">Отправить новый пароль</button>
		</form>
	</div>
</div>

<script>
	/* Переключение формы восстановления */
	document.getElementById('recoverToggle').addEventListener('click', function () {
		const form = document.getElementById('recoverForm');
		form.classList.toggle('hidden');
		this.textContent = form.classList.contains('hidden') ? 'Забыли пароль?' : 'Скрыть';
	});
</script>

</body>
</html>