<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

/* ===== Учётные данные ===== */
define('ADMIN_LOGIN',    'vector');
define('ADMIN_PASSWORD', '123');        // при первом входе клиент меняет
define('ADMIN_EMAIL',    '');           // email для восстановления пароля (пустой до настройки)
