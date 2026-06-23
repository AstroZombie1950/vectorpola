<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

/* ===== Telegram ===== */
define('TG_TOKEN',   '8776087593:AAFNFQ5Arf_znKFtfEMRZbzSGsVxEWCARzs');
define('TG_CHAT_ID', '-1003799035992');
