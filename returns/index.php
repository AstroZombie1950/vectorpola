<?php
$pageTitle = 'Возврат и гарантия — Вектор пола';
$pageDesc  = 'Условия возврата и гарантии на напольные покрытия. Гарантия производителя на производственные дефекты при соблюдении условий хранения, укладки и эксплуатации.';
$canonical = 'https://vectorpola.ru/returns/';
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
				<span>Возврат и гарантия</span>
			</nav>
			<h1>Возврат и гарантия</h1>
		</div>
	</section>

	<!-- ============ ТЕКСТ ============ -->
	<section class="section">
		<div class="container inner-content">

			<p class="lead-text">Мы заботимся о том, чтобы вы получили качественное напольное покрытие и остались довольны покупкой.</p>

			<div class="info-block">
				<h2>Возврат</h2>
				<p>Возврат возможен, если товар не был в использовании, сохранены заводская упаковка, внешний вид, комплектация и документы о покупке. Перед возвратом необходимо связаться с менеджером и согласовать детали: номер заказа, причину возврата и удобный способ передачи товара.</p>
			</div>

			<div class="info-block">
				<h2>Гарантия</h2>
				<p>На напольные покрытия действует гарантия производителя. Срок и условия гарантии зависят от бренда, коллекции и типа покрытия. Гарантия распространяется на производственные дефекты при соблюдении правил хранения, укладки и эксплуатации материала.</p>
			</div>

			<div class="info-block">
				<h2>Если обнаружен дефект</h2>
				<p>Если вы заметили повреждение, несоответствие товара или возможный производственный дефект, свяжитесь с нами до начала укладки. Для рассмотрения обращения понадобятся номер заказа, фото товара и упаковки, описание проблемы.</p>
			</div>

			<div class="info-block">
				<h2>Важно перед укладкой</h2>
				<p>Перед укладкой обязательно проверьте артикул, цвет, количество упаковок и целостность материала. После начала монтажа товар считается принятым по внешнему виду.</p>
				<div class="notice">После начала укладки претензии по внешнему виду не принимаются.</div>
			</div>

		</div>
	</section>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>