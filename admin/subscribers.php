<?php
/* ===================================================
   Подписки на акции — список, удаление, выгрузка (CSV / XLSX).
   Источник — data/subscribers.json (пишет subscribe.php).
   Доступ только под логином.
   =================================================== */

require_once __DIR__ . '/php/auth.php';
requireLogin();

$file = $_SERVER['DOCUMENT_ROOT'] . '/data/subscribers.json';

/* ===== Удаление подписчика (POST) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
	$email = mb_strtolower(trim($_POST['email'] ?? ''), 'UTF-8');
	if ($email !== '' && is_file($file)) {
		$fp = fopen($file, 'c+');
		if ($fp) {
			flock($fp, LOCK_EX);
			$raw  = stream_get_contents($fp);
			$list = $raw ? (json_decode($raw, true) ?: []) : [];
			$list = array_values(array_filter(
				$list,
				fn($s) => mb_strtolower($s['email'] ?? '', 'UTF-8') !== $email
			));
			ftruncate($fp, 0);
			rewind($fp);
			fwrite($fp, json_encode($list, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
			fflush($fp);
			flock($fp, LOCK_UN);
			fclose($fp);
		}
	}
	// PRG — чтобы обновление страницы (F5) не повторяло удаление
	header('Location: /admin/subscribers.php');
	exit;
}

/* ===== Читаем подписчиков ===== */
$subs = is_file($file) ? (json_decode(file_get_contents($file), true) ?: []) : [];

/* Новые — сверху */
usort($subs, fn($a, $b) => strcmp($b['date'] ?? '', $a['date'] ?? ''));

/* ===== Выгрузка (до вывода layout — шлём файл) ===== */
$export = $_GET['export'] ?? '';

if ($export === 'csv') {
	$fn = 'подписки_' . date('Y-m-d') . '.csv';
	header('Content-Type: text/csv; charset=utf-8');
	header('Content-Disposition: attachment; filename="' . $fn . '"');
	echo "\xEF\xBB\xBF"; // BOM — чтобы Excel не ломал кириллицу
	$out = fopen('php://output', 'w');
	fputcsv($out, ['Имя', 'Email', 'Телефон', 'Дата'], ';');
	foreach ($subs as $s) {
		fputcsv($out, [$s['name'] ?? '', $s['email'] ?? '', $s['phone'] ?? '', $s['date'] ?? ''], ';');
	}
	fclose($out);
	exit;
}

if ($export === 'xlsx') {
	require_once __DIR__ . '/php/xlsx-writer.php';
	$w = new XlsxWriter();
	$w->addSheet('Подписки');
	$w->writeHeader(['Имя', 'Email', 'Телефон', 'Дата']);
	$w->freezeFirstRow();
	$i = 0;
	foreach ($subs as $s) {
		$w->writeRow([$s['name'] ?? '', $s['email'] ?? '', $s['phone'] ?? '', $s['date'] ?? ''], $i++);
	}
	$w->output('подписки_' . date('Y-m-d') . '.xlsx');
	exit;
}

$pageTitle  = 'Подписки';
$activePage = 'subscribers';
include __DIR__ . '/php/layout-top.php';
?>

<div class="page-header">
	<h1>Подписки на акции</h1>
	<p class="page-subtitle">Заявки с формы подписки на главной. Всего: <?= count($subs) ?></p>
</div>

<?php if (!empty($subs)): ?>
<div style="display:flex; gap:10px; margin-bottom:16px; flex-wrap:wrap;">
	<a href="/admin/subscribers.php?export=csv" class="btn-secondary">Выгрузить CSV</a>
	<a href="/admin/subscribers.php?export=xlsx" class="btn-secondary">Выгрузить XLSX</a>
</div>
<?php endif; ?>

<?php if (empty($subs)): ?>
	<div class="empty-state">
		<p>Пока нет подписок. Они появятся, когда посетитель заполнит форму на главной.</p>
	</div>
<?php else: ?>
	<div class="products-table-wrap">
		<table class="products-table">
			<thead>
				<tr>
					<th>Имя</th>
					<th>Email</th>
					<th>Телефон</th>
					<th style="width:170px">Дата</th>
					<th style="width:56px"></th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($subs as $s): ?>
				<tr>
					<td><?= htmlspecialchars($s['name'] ?? '') ?></td>
					<td><a href="mailto:<?= htmlspecialchars($s['email'] ?? '') ?>"><?= htmlspecialchars($s['email'] ?? '') ?></a></td>
					<td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
					<td class="text-muted"><?= htmlspecialchars($s['date'] ?? '') ?></td>
					<td>
						<!-- Удаление подписчика (POST + подтверждение) -->
						<form method="post" style="margin:0" onsubmit="return confirm('Удалить подписку <?= htmlspecialchars($s['email'] ?? '', ENT_QUOTES) ?>?')">
							<input type="hidden" name="action" value="delete">
							<input type="hidden" name="email" value="<?= htmlspecialchars($s['email'] ?? '', ENT_QUOTES) ?>">
							<button type="submit" class="btn-icon btn-icon--danger" title="Удалить">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
							</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endif; ?>

<?php include __DIR__ . '/php/layout-bottom.php'; ?>