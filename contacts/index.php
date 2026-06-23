<?php
$pageTitle = 'Контакты — Вектор пола';
$pageDesc  = 'Два салона напольных покрытий: Москва, Волоколамское шоссе 71/13 и Красногорск, Ильинское шоссе 1А. Ежедневно с 10:00 до 20:00.';
$canonical = 'https://vectorpola.ru/contacts/';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- ===== SEO ===== -->
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta name="author" content="Вектор пола">
	<link rel="canonical" href="<?= $canonical ?>">

	<!-- ===== Open Graph ===== -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?= $canonical ?>">
	<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta property="og:image" content="https://vectorpola.ru/og-image.jpg">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:site_name" content="Вектор пола">

	<!-- ===== Фавикон ===== -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/inner-pages.css">

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ HERO СТРАНИЦЫ ============ -->
	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<span>Контакты</span>
			</nav>
			<h1>Контакты</h1>
		</div>
	</section>

	<!-- ============ КОНТАКТНЫЕ ДАННЫЕ + КАРТА ============ -->
	<section class="section">
		<div class="container">

			<!-- Общие данные -->
			<div class="contacts-top">
				<div class="contacts-info">
					<div class="contact-item">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.8 19.8 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.8 19.8 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.13 1 .37 1.97.72 2.9a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.93.35 1.9.59 2.9.72A2 2 0 0122 16.92z"/></svg>
						<div>
							<b>Телефон</b>
							<a href="tel:+79258211744">+7 (925) 821-17-44</a>
						</div>
					</div>
					<div class="contact-item">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
						<div>
							<b>E-mail</b>
							<a href="mailto:zakaz@vectorpola.ru">zakaz@vectorpola.ru</a>
						</div>
					</div>
					<div class="contact-item">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
						<div>
							<b>График работы</b>
							<span>Ежедневно с 10:00 до 20:00</span>
						</div>
					</div>
				</div>

				<!-- Кнопки связи -->
				<div class="contacts-actions">
					<a href="tel:+79258211744" class="btn btn--accent">Позвонить</a>
					<a href="https://wa.me/79258211744" target="_blank" rel="noopener" class="btn btn--outline">Написать в WhatsApp</a>
				</div>
			</div>

			<!-- Два адреса -->
			<div class="salons-grid">
				<div class="salon-card">
					<div class="salon-card__badge">Салон 1</div>
					<h2>Москва</h2>
					<p>Волоколамское шоссе 71/13, к. 1, пом. 30Н</p>
					<a href="https://yandex.ru/maps/?pt=37.3638,55.8198&z=17&l=map" target="_blank" rel="noopener" class="btn btn--outline">Построить маршрут</a>
				</div>
				<div class="salon-card">
					<div class="salon-card__badge">Салон 2</div>
					<h2>Красногорск</h2>
					<p>Ильинское шоссе, дом 1А, пом. 6</p>
					<a href="https://yandex.ru/maps/?pt=37.3341,55.8218&z=17&l=map" target="_blank" rel="noopener" class="btn btn--outline">Построить маршрут</a>
				</div>
			</div>

			<!-- Яндекс.Карта — вставить embed-код от Яндекс.Карт с двумя точками -->
			<div class="map-wrap">
				<!-- ВСТАВИТЬ: iframe от Яндекс.Карты (Конструктор карт → две метки → получить код) -->
				<div class="map-placeholder">
					<p>Карта загружается</p>
				</div>
			</div>

		</div>
	</section>
    
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>