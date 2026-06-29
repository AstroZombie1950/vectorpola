<?php
$pageTitle = 'Вектор пола — напольные покрытия с подбором под ваш интерьер';
$pageDesc  = 'Магазин напольных покрытий в Москве и Красногорске. Ламинат, кварцвинил, SPC, инженерная и паркетная доска. Подбор по образцам, расчёт материалов, доставка и укладка.';
$canonical = 'https://vectorpola.ru/';

require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';
if (!function_exists('vp_money')) {
	function vp_money($n): string { return number_format((float)$n, 0, '.', ' ') . ' ₽'; }
}
$popular = vp_popular_products(8);   // помеченные галкой в админке
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- ===== SEO ===== -->
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta name="keywords" content="напольные покрытия, ламинат, кварцвинил, SPC, инженерная доска, паркетная доска, Москва, Красногорск">
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

	<!-- ===== Twitter Card ===== -->
	<meta name="twitter:card" content="summary_large_image">
	<meta name="twitter:title" content="Вектор пола — напольные покрытия">
	<meta name="twitter:description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta name="twitter:image" content="https://vectorpola.ru/og-image.jpg">

	<!-- ===== Фавикон ===== -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<!-- Предзагрузка LCP-картинки первого экрана -->
	<link rel="preload" as="image" href="/source/img/hero.webp" fetchpriority="high">

	<link rel="stylesheet" href="/source/css/main.css?v=10">

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ ПЕРВЫЙ ЭКРАН ============ -->
	<section class="hero" aria-label="Главный баннер">
		<div class="container">
			<div class="hero-layout">
				<!-- Каталог слева (десктоп) -->
				<aside class="hero-catalog" aria-label="Разделы каталога">
					<div class="hc-head"><span class="burger"><i></i><i></i><i></i></span> Каталог</div>
					<ul>
						<li><a href="/catalog/laminat/">Ламинат <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/kvarcvinil/">Кварцвинил / SPC <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/vinil/">Виниловые полы <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/inzhenernaya-doska/">Инженерная доска <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/parketnaya-doska/">Паркетная доска <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/massivnaya-doska/">Массивная доска <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/probka/">Пробковые покрытия <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/plintus-podlozhka/">Плинтусы и подложка <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li><a href="/catalog/soputstvuyushchie/">Сопутствующие товары <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></a></li>
						<li class="more"><a href="/catalog/">Перейти в каталог</a></li>
					</ul>
				</aside>

				<!-- Промо-баннер -->
				<div class="hero-promo">
					<div>
						<h1>Напольные покрытия <span class="accent">с подбором</span> под ваш интерьер</h1>
						<p class="sub">Ламинат, кварцвинил, SPC, инженерная и паркетная доска. Поможем подобрать покрытие под бюджет, стиль, нагрузку и способ укладки.</p>
						<div class="actions">
							<a href="#picker" class="btn btn--accent">Подобрать покрытие</a>
							<a href="#final" class="btn btn--outline">Получить расчёт</a>
							<a href="/contacts/" class="btn btn--outline">Проложить маршрут в салон</a>
						</div>
						<div class="perks">
							<span>Подбор по образцам</span>
							<span>Расчёт материалов</span>
							<span>Доставка</span>
							<span>Укладка</span>
							<span>Два салона</span>
						</div>
					</div>
					<div class="hp-visual"><img src="/source/img/hero.webp" alt="Интерьер с напольным покрытием" width="1200" height="900" loading="eager" decoding="async" fetchpriority="high"></div>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ КАТЕГОРИИ КАТАЛОГА ============ -->
	<section class="section" id="catalog">
		<div class="container">
			<div class="section-head"><h2>Каталог покрытий</h2></div>

			<form class="cat-search" role="search" method="get" action="/search/">
				<input type="search" name="q" placeholder="Найти покрытие в каталоге" aria-label="Поиск в каталоге">
				<button type="submit" class="btn btn--accent">Найти</button>
			</form>

			<div class="cat-grid">
				<a href="/catalog/laminat/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Ламинат</b></a>
				<a href="/catalog/kvarcvinil/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Кварцвинил / SPC</b></a>
				<a href="/catalog/vinil/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Виниловые полы</b></a>
				<a href="/catalog/inzhenernaya-doska/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Инженерная доска</b></a>
				<a href="/catalog/parketnaya-doska/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Паркетная доска</b></a>
				<a href="/catalog/massivnaya-doska/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg></span><b>Массивная доска</b></a>
				<a href="/catalog/probka/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3"/></svg></span><b>Пробковые покрытия</b></a>
				<a href="/catalog/plintus-podlozhka/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V8l8-4 8 4v12"/><path d="M4 14h16"/></svg></span><b>Плинтусы и подложка</b></a>
				<a href="/catalog/soputstvuyushchie/" class="cat-tile"><span class="ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 002 1.6h9.7a2 2 0 002-1.6L23 6H6"/></svg></span><b>Сопутствующие товары</b></a>
			</div>
		</div>
	</section>

	<!-- ============ ПОПУЛЯРНЫЕ ПРОДУКТЫ ============ -->
	<?php if ($popular): ?>
	<section class="section section--soft">
		<div class="container">
			<div class="head-row">
				<div class="section-head"><h2>Популярные продукты</h2></div>
				<div class="arrows">
					<button type="button" aria-label="Назад"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
					<button type="button" aria-label="Вперёд"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
				</div>
			</div>
			<div class="products">
				<?php foreach ($popular as $p):
					$pImg   = !empty($p['images'][0]) ? $p['images'][0] : '/source/img/no-image.webp';
					$pPrice = (float)($p['price'] ?? 0);
				?>
				<a class="product" href="<?= htmlspecialchars(vp_product_url($p)) ?>">
					<div class="img"><img src="<?= htmlspecialchars($pImg) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="800" height="800" loading="lazy" onerror="this.onerror=null;this.src='/source/img/no-image.webp'"></div>
					<div class="body">
						<div class="name"><?= htmlspecialchars($p['name']) ?></div>
						<div class="price"><?= vp_money($pPrice) ?> <small>/ <?= htmlspecialchars($p['unit'] ?? 'м²') ?></small></div>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<!-- ============ ПОДБОР ПОКРЫТИЯ (сценарии) ============ -->
	<section class="section" id="picker">
		<div class="container">
			<div class="section-head"><h2>Поможем выбрать покрытие под вашу задачу</h2></div>
			<div class="scenarios">
				<div class="scenario">Для кухни</div>
				<div class="scenario">Для спальни</div>
				<div class="scenario">Для квартиры с детьми</div>
				<div class="scenario">Для квартиры с животными</div>
				<div class="scenario">Под тёплый пол</div>
				<div class="scenario">Водостойкое покрытие</div>
				<div class="scenario">Покрытие под ёлочку</div>
				<div class="scenario">Бюджетное решение</div>
				<div class="scenario">Премиальное покрытие</div>
			</div>
			<a href="#final" class="btn btn--accent">Получить консультацию</a>
		</div>
	</section>
	<?php endif; ?>

	<!-- ============ ВЫЕЗДНОЙ ШОУРУМ ============ -->
	<section class="section section--soft" id="showroom">
		<div class="container">
			<div class="showroom">
				<div class="grid">
					<div>
						<h2>Выездной шоурум</h2>
						<p>Если вам неудобно приезжать в салон, мы можем привезти образцы напольных покрытий к вам домой, в офис или на объект.</p>
						<p>Специалист покажет подходящие варианты, поможет сравнить оттенки, фактуры и материалы, а также подберёт покрытие под интерьер, бюджет и условия эксплуатации.</p>
						<a href="#final" class="btn btn--accent">Заказать выездной шоурум</a>
					</div>
					<div class="incl">
						<b>Что входит:</b>
						<ul>
							<li>Выезд специалиста с образцами</li>
							<li>Консультация на объекте</li>
							<li>Подбор покрытия под помещение</li>
							<li>Сравнение цветов и фактур при вашем освещении</li>
							<li>Расчёт нужного количества материала</li>
							<li>Рекомендации по укладке и сопутствующим товарам</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ ПОЧЕМУ МЫ ============ -->
	<section class="section" id="why">
		<div class="container">
			<div class="section-head"><h2>Почему мы?</h2></div>
			<div class="features">
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></span>
					<p>Подбор покрытия по образцам</p>
				</div>
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h2M14 10h2M8 14h2M14 14h2M8 18h2M14 18h2"/></svg></span>
					<p>Помощь с расчётом количества материала</p>
				</div>
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="6" width="14" height="11"/><path d="M15 9h4l3 3v5h-7"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg></span>
					<p>Доставка и самовывоз</p>
				</div>
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 7l3 3-9 9-3 .5.5-3z"/><path d="M3 21h18"/></svg></span>
					<p>Возможность укладки</p>
				</div>
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 20a6 6 0 0112 0M14 20a5 5 0 017 0"/></svg></span>
					<p>Работа с дизайнерами и поставщиками</p>
				</div>
				<div class="feature">
					<span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 21s-7-6.3-7-11a7 7 0 0114 0c0 4.7-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
					<p>Два салона: Москва и Красногорск</p>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ БРЕНДЫ ============ -->
	<section class="section section--soft" id="brands">
		<div class="container">
			<div class="section-head">
				<h2>Бренды</h2>
				<p class="lead">Работаем с проверенными производителями напольных покрытий.</p>
			</div>
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

	<!-- ============ О КОМПАНИИ ============ -->
	<section class="section about" id="about">
		<div class="container">
			<div class="box">
				<div class="section-head"><h2>О компании</h2></div>
				<p>«Вектор пола» — магазин напольных покрытий, где помогают выбрать пол под реальные задачи: интерьер, бюджет, нагрузку, влажность помещения и способ укладки. Мы работаем с ламинатом, кварцвинилом, SPC, инженерной и паркетной доской, плинтусами и сопутствующими материалами. В салоне можно посмотреть образцы, сравнить оттенки и фактуры, получить консультацию специалиста и рассчитать нужный объём покрытия.</p>
				<div class="actions">
					<a href="/about/" class="btn btn--accent">Подробнее о компании</a>
					<a href="/contacts/" class="btn btn--outline">Приехать в салон</a>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ НОВОСТИ ============ -->
	<section class="section section--soft" id="news">
		<div class="container">
			<div class="head-row">
				<div class="section-head"><h2>Новости</h2></div>
				<div class="arrows">
					<button type="button" aria-label="Назад"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg></button>
					<button type="button" aria-label="Вперёд"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg></button>
				</div>
			</div>
			<div class="news">
				<div class="news-card"><div class="img"><img src="/source/img/news.webp" alt="Новость" width="800" height="600" loading="lazy"></div><div class="body"><div class="title">Заголовок новости — пример карточки</div><div class="date">00.00.2025</div></div></div>
				<div class="news-card"><div class="img"><img src="/source/img/news.webp" alt="Новость" width="800" height="600" loading="lazy"></div><div class="body"><div class="title">Заголовок новости — пример карточки</div><div class="date">00.00.2025</div></div></div>
				<div class="news-card"><div class="img"><img src="/source/img/news.webp" alt="Новость" width="800" height="600" loading="lazy"></div><div class="body"><div class="title">Заголовок новости — пример карточки</div><div class="date">00.00.2025</div></div></div>
				<div class="news-card"><div class="img"><img src="/source/img/news.webp" alt="Новость" width="800" height="600" loading="lazy"></div><div class="body"><div class="title">Заголовок новости — пример карточки</div><div class="date">00.00.2025</div></div></div>
			</div>
		</div>
	</section>

	<!-- ============ FAQ ============ -->
	<section class="section faq" id="faq">
		<div class="container">
			<div class="section-head center"><h2>Остались вопросы?</h2><p class="lead">Собрали ответы на частые вопросы.</p></div>
			<details>
				<summary>Где можно посмотреть образцы покрытий?<span class="chev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span></summary>
				<div class="ans"><div class="ans-in">У нас два салона — в Москве (Волоколамское шоссе 71/13) и Красногорске (Ильинское шоссе 1А). Также можем привезти образцы на выездном шоуруме к вам домой, в офис или на объект.</div></div>
			</details>
			<details>
				<summary>Доставляете ли вы за МКАД?<span class="chev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span></summary>
				<div class="ans"><div class="ans-in">Да, доставляем по Москве и Московской области. За МКАД — 1 500 ₽ + 30 ₽/км. Точную стоимость менеджер рассчитает по адресу и объёму заказа.</div></div>
			</details>
			<details>
				<summary>Можно ли заказать укладку?<span class="chev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span></summary>
				<div class="ans"><div class="ans-in">Да, укладку можно согласовать отдельно — удобно, если нужно комплексное решение: подбор материала, доставка и монтаж.</div></div>
			</details>
			<details>
				<summary>Поможете рассчитать количество материала?<span class="chev"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg></span></summary>
				<div class="ans"><div class="ans-in">Да, поможем рассчитать нужный объём покрытия под вашу площадь с учётом подрезки и запаса.</div></div>
			</details>
		</div>
	</section>

	<!-- ============ ФИНАЛЬНЫЙ CTA + ФОРМА ============ -->
	<section class="section cta-section" id="final">
		<div class="container cta-section__grid">
			<div>
				<h2>Не знаете, какое покрытие выбрать?</h2>
				<p class="lead">Оставьте заявку — специалист поможет подобрать пол под помещение, бюджет и стиль интерьера.</p>
			</div>
			<form class="cta-form" autocomplete="off">
				<input type="hidden" name="source" value="Главная страница">
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
					<textarea name="comment" placeholder="Комментарий" aria-label="Комментарий"></textarea>
					<span class="field-error"></span>
				</div>
				<button type="button" class="btn btn--accent">Получить консультацию</button>
				<div class="form-status"></div>
			</form>
		</div>
	</section>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=4"></script>
</body>
</html>