<?php
$pageTitle = 'Доставка и оплата — Вектор пола';
$pageDesc  = 'Доставка напольных покрытий по Москве от 1 500 ₽, за МКАД — 1 500 ₽ + 30 ₽/км. Самовывоз, разгрузка, оплата наличными, картой или по счёту.';
$canonical = 'https://vectorpola.ru/delivery/';
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

	<link rel="stylesheet" href="/source/css/main.css?v=8">
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
				<span>Доставка и оплата</span>
			</nav>
			<h1>Доставка и оплата</h1>
			<p class="page-hero__sub">Доставляем напольные покрытия по Москве и Московской области. Возможен самовывоз, доставка до объекта и разгрузка по предварительному согласованию.</p>
			<div class="page-hero__actions">
				<a href="#delivery-form" class="btn btn--accent">Рассчитать доставку</a>
				<a href="tel:+79258211744" class="btn btn--outline">Позвонить</a>
				<a href="https://wa.me/79258211744" target="_blank" rel="noopener" class="btn btn--outline">Написать в WhatsApp</a>
			</div>
		</div>
	</section>

	<!-- ============ УСЛОВИЯ ДОСТАВКИ ============ -->
	<section class="section">
		<div class="container inner-content">

			<div class="section-head"><h2>Условия доставки</h2></div>

			<table class="info-table">
				<tbody>
					<tr>
						<td>Доставка по Москве</td>
						<td><b>От 1 500 ₽</b></td>
					</tr>
					<tr>
						<td>Доставка за МКАД</td>
						<td><b>1 500 ₽ + 30 ₽/км</b></td>
					</tr>
					<tr>
						<td>Куда доставляем</td>
						<td>До подъезда или до ближайшего возможного места разгрузки</td>
					</tr>
					<tr>
						<td>Согласование</td>
						<td>Дата, адрес, состав заказа и итоговая стоимость согласуются с менеджером заранее</td>
					</tr>
				</tbody>
			</table>

			<!-- Самовывоз -->
			<div class="info-block">
				<h2>Самовывоз</h2>
				<p>Вы можете забрать заказ самостоятельно после подтверждения готовности товара. Менеджер заранее сообщит, когда заказ будет доступен к выдаче, и согласует удобное время самовывоза.</p>
				<div class="notice">Самовывоз возможен только после подтверждения заказа менеджером.</div>
			</div>

			<!-- Разгрузка и подъём -->
			<div class="info-block">
				<h2>Разгрузка и подъём</h2>
				<p>Разгрузку и подъём материала можно согласовать отдельно. Стоимость зависит от объёма, веса заказа, этажа, наличия лифта и условий подъезда к объекту.</p>
				<ul class="check-list">
					<li>Разгрузка силами клиента</li>
					<li>Разгрузка с помощью специалистов</li>
					<li>Подъём на этаж</li>
					<li>Индивидуальный расчёт при сложных условиях разгрузки</li>
				</ul>
			</div>

			<!-- Проверка товара -->
			<div class="info-block">
				<h2>Проверка товара при получении</h2>
				<p>При получении заказа необходимо проверить количество упаковок, артикулы, декор, цвет и целостность товара.</p>
				<div class="notice">Если упаковка повреждена или есть расхождения по количеству, нужно сообщить об этом до подписания документов о получении.</div>
			</div>

		</div>
	</section>

	<!-- ============ ОПЛАТА ============ -->
	<section class="section section--soft">
		<div class="container inner-content">

			<div class="section-head"><h2>Оплата</h2></div>

			<ul class="check-list">
				<li>Наличными</li>
				<li>Банковской картой</li>
				<li>Переводом</li>
				<li>По счёту для юридических лиц</li>
				<li>Иным способом по согласованию с менеджером</li>
			</ul>

			<!-- Юрлица -->
			<div class="info-block">
				<h2>Для юридических лиц</h2>
				<p>Для юридических лиц возможна оплата по счёту. При необходимости предоставляются закрывающие документы.</p>

				<table class="info-table info-table--compact">
					<tbody>
						<tr>
							<td>ИП</td>
							<td>Катанова Виктория Евгеньевна</td>
						</tr>
						<tr>
							<td>ИНН</td>
							<td>213013757388</td>
						</tr>
						<tr>
							<td>ОГРНИП</td>
							<td>326210000051661</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>
	</section>

	<!-- ============ CTA-ФОРМА ============ -->
	<section class="section cta-section" id="delivery-form">
		<div class="container cta-section__grid">
			<div>
				<h2>Нужно рассчитать доставку?</h2>
				<p class="lead">Оставьте заявку — менеджер уточнит адрес, объём заказа и рассчитает стоимость доставки и разгрузки.</p>
			</div>
			<form class="cta-form" autocomplete="off">
				<input type="hidden" name="source" value="Доставка и оплата">
				<div>
					<input type="text" name="name" placeholder="Имя" aria-label="Ваше имя"
						data-required data-minlen="2" data-error-empty="Введите имя" data-error-short="Имя слишком короткое">
					<span class="field-error"></span>
				</div>
				<div>
					<input type="tel" name="phone" placeholder="Телефон" aria-label="Телефон для связи"
						data-required data-error-empty="Введите телефон">
					<span class="field-error"></span>
				</div>
				<div>
					<input type="text" name="address" placeholder="Адрес доставки" aria-label="Адрес доставки"
						data-required data-error-empty="Укажите адрес доставки">
					<span class="field-error"></span>
				</div>
				<div>
					<textarea name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>
					<span class="field-error"></span>
				</div>
				<button type="button" class="btn btn--accent">Рассчитать доставку</button>
				<div class="form-status"></div>
			</form>
		</div>
	</section>

    <?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>