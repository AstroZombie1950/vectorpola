<?php
require_once __DIR__ . '/php/auth.php';
requireLogin();

$pageTitle  = 'Главная';
$activePage = 'dashboard';

// Считаем товары
$productsFile = $_SERVER['DOCUMENT_ROOT'] . '/data/products.json';
$products     = file_exists($productsFile) ? json_decode(file_get_contents($productsFile), true) : [];
$totalProducts  = count($products);
$activeProducts = count(array_filter($products, fn($p) => !empty($p['active'])));

include __DIR__ . '/php/layout-top.php';
?>

<div class="page-header">
	<h1>Главная</h1>
	<p class="page-subtitle">Добро пожаловать в панель управления</p>
</div>

<!-- Карточки статистики -->
<div class="stats-grid">
	<div class="stat-card">
		<div class="stat-value"><?= $totalProducts ?></div>
		<div class="stat-label">Товаров всего</div>
	</div>
	<div class="stat-card">
		<div class="stat-value"><?= $activeProducts ?></div>
		<div class="stat-label">Активных товаров</div>
	</div>
	<div class="stat-card">
		<div class="stat-value"><?= $totalProducts - $activeProducts ?></div>
		<div class="stat-label">Скрытых товаров</div>
	</div>
</div>

<!-- Быстрые действия -->
<div class="quick-actions">
	<h2 class="section-title">Быстрые действия</h2>
	<div class="action-grid">
		<a href="/admin/products.php?tab=add" class="action-card">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
			<span>Добавить товар</span>
		</a>
		<a href="/admin/products.php?tab=import" class="action-card">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
			<span>Импорт товаров</span>
		</a>
		<a href="/admin/products.php?tab=export" class="action-card">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
			<span>Экспорт / шаблон</span>
		</a>
		<a href="/admin/settings.php" class="action-card">
			<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
			<span>Настройки</span>
		</a>
	</div>
</div>

<?php include __DIR__ . '/php/layout-bottom.php'; ?>
