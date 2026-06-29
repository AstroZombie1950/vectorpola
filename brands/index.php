<?php
$pageTitle = 'Бренды — Вектор пола';
$pageDesc  = 'Производители напольных покрытий, с которыми работает «Вектор пола»: ламинат, кварцвинил, SPC, инженерная и паркетная доска, плинтусы и сопутствующие материалы.';
$canonical = 'https://vectorpola.ru/brands/';
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

	<link rel="stylesheet" href="/source/css/main.css?v=10">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ HERO СТРАНИЦЫ ============ -->
	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<span>Бренды</span>
			</nav>
			<h1>Бренды</h1>
		</div>
	</section>

	<!-- ============ СЕТКА БРЕНДОВ ============ -->
	<section class="section">
		<div class="container">
			<p class="lead-text">Все товары в магазине прошли тщательную проверку на качество и подлинность. Мы работаем только с официальным поставщиками стран производителей Германия, Бельгия, Китай/Германия, Франция, Португалия и др. Вся продаваемая нами продукция сертифицирована и отвечает всем санитарным требованиям и полностью безопасна.</p>

			<div class="brands">
				<div class="brand"><img src="/source/img/brands/alpine-floor.webp" alt="Alpine Floor" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/agt.webp" alt="AGT" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/aqua-floor.webp" alt="Aqua Floor" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/kronotex.webp" alt="KRONOTEX" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/my-step.webp" alt="MY Step" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/primavera.webp" alt="PRIMAVERA" width="320" height="180" loading="lazy"></div>
				<div class="brand"><img src="/source/img/brands/eversense.webp" alt="EVERSENSE" width="320" height="180" loading="lazy"></div>
			</div>
		</div>
	</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>