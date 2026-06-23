<?php
$pageTitle = 'О компании — Вектор пола';
$pageDesc  = 'Магазин напольных покрытий для квартир, домов, офисов и коммерческих помещений. Помогаем подобрать покрытие под условия эксплуатации, бюджет и стиль интерьера.';
$canonical = 'https://vectorpola.ru/about/';
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
				<span>О компании</span>
			</nav>
			<h1>О компании</h1>
		</div>
	</section>

	<!-- ============ ОСНОВНОЙ ТЕКСТ ============ -->
	<section class="section">
		<div class="container inner-content">

			<p class="lead-text">«Вектор пола» — магазин напольных покрытий для квартир, домов, офисов и коммерческих помещений. Мы помогаем подобрать покрытие не только по цвету и цене, но и по условиям эксплуатации: влажность, нагрузка, наличие теплого пола, домашние животные, требования к износостойкости и способу укладки.</p>

			<p>В ассортименте представлены ламинат, кварцвинил, SPC, инженерная доска, паркетная доска, массивная доска, пробковые покрытия, плинтусы, подложки и сопутствующие материалы.</p>

			<p>Специалисты помогают сравнить коллекции, подобрать оттенок под интерьер, рассчитать нужный объем и предложить оптимальное решение под бюджет.</p>

			<div class="info-grid">

				<div class="info-block">
					<h2>График работы</h2>
					<p>Мы работаем ежедневно с 10:00 до 20:00. В салон можно приехать как в будни, так и в выходные. При необходимости заранее согласуем удобное время консультации.</p>
				</div>

				<div class="info-block">
					<h2>Ассортимент</h2>
					<p>В салоне представлены образцы напольных покрытий разных типов, оттенков и фактур. Вы сможете увидеть материал вживую, сравнить варианты и понять, как покрытие будет смотреться в интерьере.</p>
					<p>Если вам удобнее выбрать пол на объекте, специалист может приехать с образцами к вам домой, в офис или на объект. Так вы сможете оценить цвет, фактуру и сочетание покрытия с интерьером при реальном освещении.</p>
				</div>

				<div class="info-block">
					<h2>Команда специалистов</h2>
					<p>Наши консультанты помогают подобрать покрытие под задачу: помещение, бюджет, стиль интерьера, нагрузку и способ укладки. Мы объясняем различия между материалами простым языком и предлагаем подходящие варианты.</p>
				</div>

				<div class="info-block">
					<h2>Условия покупки</h2>
					<p>Мы помогаем оформить заказ, рассчитать нужное количество материала, согласовать доставку и при необходимости подобрать сопутствующие товары: подложку, плинтус и аксессуары.</p>
				</div>

				<div class="info-block">
					<h2>Способы оплаты</h2>
					<p>Оплатить заказ можно удобным способом: наличными, банковской картой, переводом или по счету. Условия оплаты согласовываются с менеджером при оформлении заказа.</p>
				</div>

				<div class="info-block">
					<h2>Индивидуальный подбор</h2>
					<p>Если среди представленных вариантов вы не нашли подходящее покрытие, специалист поможет подобрать альтернативы по цвету, фактуре, формату, стоимости и характеристикам.</p>
				</div>

				<div class="info-block">
					<h2>Сервис и сопровождение</h2>
					<p>Мы сопровождаем клиента на всех этапах: от выбора покрытия до оформления заказа, доставки и консультации по укладке.</p>
				</div>

				<div class="info-block">
					<h2>Укладка напольных покрытий</h2>
					<p>При необходимости можно согласовать укладку покрытия. Это удобно, если вы хотите получить комплексное решение: подбор материала, доставку и монтаж.</p>
				</div>

			</div>
		</div>
	</section>

	<!-- ============ ДИЗАЙНЕРАМ И ПОСТАВЩИКАМ ============ -->
	<section class="section section--soft">
		<div class="container">
			<div class="section-head"><h2>Дизайнерам и поставщикам</h2></div>

			<div class="collab-block">
				<div>
					<!-- Кому подходит -->
					<div class="collab-audience">
						<span>Дизайнерам</span>
						<span>Подрядчикам</span>
						<span>Поставщикам</span>
					</div>

					<!-- Что предлагаем -->
					<ul class="check-list" style="margin-top: 24px;">
						<li>Помощь с подбором покрытий под проект</li>
						<li>Образцы для демонстрации клиентам</li>
						<li>Расчёт материалов под объект</li>
						<li>Сопровождение клиента на всех этапах</li>
						<li>Индивидуальные условия сотрудничества</li>
					</ul>

					<!-- Прямые контакты -->
					<div class="collab-contacts">
						<a href="tel:+79258211744" class="btn btn--accent">Позвонить</a>
						<a href="mailto:zakaz@vectorpola.ru" class="btn btn--outline">zakaz@vectorpola.ru</a>
					</div>
				</div>

				<!-- Форма -->
				<form class="cta-form" autocomplete="off">
					<input type="hidden" name="source" value="О компании — Дизайнерам">
					<p class="cta-form__title">Обсудить сотрудничество</p>
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
						<input type="email" name="email" placeholder="E-mail" aria-label="E-mail"
							data-required data-error-empty="Введите e-mail">
						<span class="field-error"></span>
					</div>
					<div>
						<textarea name="comment" placeholder="Расскажите о проекте" aria-label="Комментарий"></textarea>
						<span class="field-error"></span>
					</div>
					<button type="button" class="btn btn--accent">Отправить заявку</button>
					<div class="form-status"></div>
				</form>
			</div>
		</div>
	</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js"></script>
</body>
</html>