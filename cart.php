<?php
$pageTitle = 'Корзина — Вектор пола';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="robots" content="noindex,follow">

	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css?v=11">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	<link rel="stylesheet" href="/source/css/cart.css?v=2">

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<span>Корзина</span>
			</nav>
			<h1>Корзина</h1>
		</div>
	</section>

	<section class="section">
		<div class="container cart-wrap">

			<!-- Список товаров (наполняется cart.js) -->
			<div id="cartRoot"></div>

			<!-- Оформление заявки -->
			<div id="cartCheckout" class="cart-checkout" hidden>
				<h2>Оформление заявки</h2>
				<p class="cart-checkout__note">Оплата на сайте не требуется. Оставьте контакты — менеджер свяжется, подтвердит наличие и рассчитает доставку.</p>
				<div id="cartForm" class="cart-form">
					<input type="text" name="name" placeholder="Имя" aria-label="Ваше имя" autocomplete="name">
					<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" aria-label="Телефон" autocomplete="tel">
					<textarea name="comment" rows="3" placeholder="Комментарий (адрес, удобное время — необязательно)" aria-label="Комментарий"></textarea>
					<button type="button" class="btn btn--accent">Отправить заявку</button>
					<div class="form-status"></div>
				</div>
			</div>

		</div>
	</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=5"></script>
	<script src="/source/js/cart.js?v=4"></script>
</body>
</html>