<?php
// $pageTitle и $activePage должны быть определены до подключения

$navItems = [
	['href' => '/admin/dashboard.php',  'label' => 'Главная',    'icon' => 'home',     'id' => 'dashboard'],
	['href' => '/admin/products.php',   'label' => 'Каталог',    'icon' => 'grid',     'id' => 'products'],
	['href' => '/admin/subscribers.php','label' => 'Подписки',   'icon' => 'mail',     'id' => 'subscribers'],
	['href' => '/admin/settings.php',   'label' => 'Настройки',  'icon' => 'settings', 'id' => 'settings'],
	['href' => '/admin/help.php',       'label' => 'Инструкция', 'icon' => 'help',     'id' => 'help'],
];

$icons = [
	'home'     => '<path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
	'grid'     => '<rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/>',
	'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><polyline points="3 7 12 13 21 7"/>',
	'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z"/>',
	'help'     => '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?= htmlspecialchars($pageTitle ?? 'Админка') ?> — Вектор пола</title>
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body class="admin-layout">

<!-- ===== Sidebar ===== -->
<aside class="sidebar" id="sidebar">
	<div class="sidebar-logo">
		<span class="logo-plate"><img src="/source/img/logo.webp" alt="Вектор пола" width="830" height="440"></span>
	</div>

	<nav class="sidebar-nav">
		<?php foreach ($navItems as $item): ?>
		<a href="<?= $item['href'] ?>"
		   class="nav-item <?= ($activePage ?? '') === $item['id'] ? 'nav-item--active' : '' ?>">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
				<?= $icons[$item['icon']] ?>
			</svg>
			<?= htmlspecialchars($item['label']) ?>
		</a>
		<?php endforeach; ?>
	</nav>

	<div class="sidebar-footer">
		<a href="/" target="_blank" class="nav-item">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
			Открыть сайт
		</a>
		<a href="/admin/?logout=1" class="nav-item nav-item--logout">
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
			Выйти
		</a>
	</div>
</aside>

<!-- ===== Шапка (мобильная) ===== -->
<header class="admin-topbar">
	<button class="burger-btn" id="sidebarToggle" aria-label="Открыть меню">
		<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
	</button>
	<span class="admin-topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></span>
	<a href="/admin/?logout=1" class="topbar-logout" aria-label="Выйти">
		<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
	</a>
</header>

<!-- Оверлей для мобильного sidebar -->
<div class="sidebar-overlay hidden" id="sidebarOverlay"></div>

<!-- ===== Основной контент ===== -->
<main class="admin-main">