<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

/* ===== Telegram ===== */
define('TG_TOKEN',   'TG_TOKEN_hid');
define('TG_CHAT_ID', 'TG_CHAT_ID_hid');
