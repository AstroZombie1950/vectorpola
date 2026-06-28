<?php
/* ===================================================
   Ручная пересборка catalog.sqlite из products.json.
   На случай рассинхрона (правка json мимо админки и т.п.).
   В обычной работе база пересобирается сама после
   сохранения/импорта — этот инструмент нужен редко.
   Доступ — только под логином.
   =================================================== */

require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/products-data.php'; // тянет за собой catalog.php
requireLogin();

$done = false;
$ok   = false;
if (($_GET['go'] ?? '') === '1') {
	$ok   = vp_rebuild_sqlite();
	$done = true;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Пересборка базы каталога</title>
	<link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body style="padding:40px;max-width:640px;margin:0 auto">
	<h1>Пересборка базы каталога</h1>
	<p class="text-muted">Перечитывает <code>data/products.json</code> и заново строит быстрый индекс <code>data/catalog.sqlite</code>, по которому работает витрина.</p>

	<?php if ($done): ?>
		<?php if ($ok): ?>
			<p style="color:#2e7d32;font-weight:600;margin:20px 0">✓ База пересобрана успешно.</p>
		<?php else: ?>
			<p style="color:#c62828;font-weight:600;margin:20px 0">✗ Не удалось пересобрать. Проверьте, что <code>data/products.json</code> на месте и у папки <code>data/</code> есть права на запись.</p>
		<?php endif; ?>
		<a href="/admin/products.php" class="btn btn--accent">← К товарам</a>
	<?php else: ?>
		<div style="margin:24px 0;display:flex;gap:10px">
			<a href="/admin/rebuild-db.php?go=1" class="btn btn--accent">Пересобрать сейчас</a>
			<a href="/admin/products.php" class="btn btn--outline">Отмена</a>
		</div>
	<?php endif; ?>
</body>
</html>
