<?php
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pageTitle  = 'Настройки';
$activePage = 'settings';

$success = '';
$error   = '';

/* ===== Обработка форм ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$action = $_POST['action'] ?? '';

	// --- Смена логина ---
	if ($action === 'change_login') {
		$newLogin = trim($_POST['new_login'] ?? '');
		if (mb_strlen($newLogin) < 3) {
			$error = 'Логин должен быть не короче 3 символов';
		} else {
			saveAdminConfig($newLogin, ADMIN_PASSWORD, ADMIN_EMAIL);
			$success = 'Логин успешно изменён. При следующем входе используйте новый логин.';
		}
	}

	// --- Смена пароля ---
	if ($action === 'change_password') {
		$current = $_POST['current_password'] ?? '';
		$new1    = $_POST['new_password']     ?? '';
		$new2    = $_POST['new_password2']    ?? '';

		if ($current !== ADMIN_PASSWORD) {
			$error = 'Текущий пароль указан неверно';
		} elseif (mb_strlen($new1) < 4) {
			$error = 'Новый пароль должен быть не короче 4 символов';
		} elseif ($new1 !== $new2) {
			$error = 'Пароли не совпадают';
		} else {
			saveAdminConfig(ADMIN_LOGIN, $new1, ADMIN_EMAIL);
			$success = 'Пароль успешно изменён';
		}
	}

	// --- Смена email ---
	if ($action === 'change_email') {
		$newEmail = trim($_POST['recovery_email'] ?? '');
		if ($newEmail !== '' && !filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
			$error = 'Некорректный адрес e-mail';
		} else {
			saveAdminConfig(ADMIN_LOGIN, ADMIN_PASSWORD, $newEmail);
			$success = 'Email для восстановления сохранён';
		}
	}
}

include __DIR__ . '/php/layout-top.php';
?>

<div class="page-header">
	<h1>Настройки</h1>
	<p class="page-subtitle">Управление доступом к панели администратора</p>
</div>

<?php if ($success): ?>
	<div class="alert alert--success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
	<div class="alert alert--error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Подсказка -->
<div class="hint-block">
	<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
	Логин восстановить нельзя — запишите его в надёжном месте. Пароль можно сбросить через email.
</div>

<div class="settings-grid">

	<!-- Смена логина -->
	<div class="card">
		<h2 class="card-title">Смена логина</h2>
		<form method="POST" action="">
			<input type="hidden" name="action" value="change_login">
			<div class="field">
				<label>Новый логин</label>
				<input type="text" name="new_login" value="<?= htmlspecialchars(ADMIN_LOGIN) ?>" autocomplete="off" required>
			</div>
			<button type="submit" class="btn-primary">Сохранить логин</button>
		</form>
	</div>

	<!-- Смена пароля -->
	<div class="card">
		<h2 class="card-title">Смена пароля</h2>
		<form method="POST" action="">
			<input type="hidden" name="action" value="change_password">
			<div class="field">
				<label>Текущий пароль</label>
				<input type="password" name="current_password" autocomplete="current-password" required>
			</div>
			<div class="field">
				<label>Новый пароль</label>
				<input type="password" name="new_password" autocomplete="new-password" required>
			</div>
			<div class="field">
				<label>Повторите новый пароль</label>
				<input type="password" name="new_password2" autocomplete="new-password" required>
			</div>
			<button type="submit" class="btn-primary">Изменить пароль</button>
		</form>
	</div>

	<!-- Email для восстановления -->
	<div class="card">
		<h2 class="card-title">Email для восстановления пароля</h2>
		<p class="card-desc">На этот адрес придёт новый пароль при нажатии «Забыли пароль?» на странице входа. Уведомление также дублируется в Telegram.</p>
		<form method="POST" action="">
			<input type="hidden" name="action" value="change_email">
			<div class="field">
				<label>Email</label>
				<input type="email" name="recovery_email" value="<?= htmlspecialchars(ADMIN_EMAIL) ?>" placeholder="example@mail.ru">
			</div>
			<button type="submit" class="btn-primary">Сохранить email</button>
		</form>
	</div>

</div>

<?php include __DIR__ . '/php/layout-bottom.php'; ?>
