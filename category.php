<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';

/* ===== Роутинг: cat из .htaccess ===== */
$cat = preg_replace('/[^a-z0-9-]/', '', $_GET['cat'] ?? '');

/* 404, если категории нет в списке */
if (!$cat || !isset(VP_CATEGORIES[$cat])) {
	http_response_code(404);
	$pageTitle = 'Категория не найдена — Вектор пола';
	?>
	<!DOCTYPE html>
	<html lang="ru">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title><?= htmlspecialchars($pageTitle) ?></title>
		<meta name="robots" content="noindex">
		<link rel="icon" type="image/svg+xml" href="/favicon.svg">
		<link rel="stylesheet" href="/source/css/main.css">
		<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	</head>
	<body>
		<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>
		<section class="section">
			<div class="container inner-content" style="text-align:center;">
				<h1>Категория не найдена</h1>
				<p class="lead-text" style="margin-top:12px;">Возможно, ссылка устарела. Загляните в общий каталог.</p>
				<a href="/catalog/" class="btn btn--accent" style="margin-top:8px;">В каталог</a>
			</div>
		</section>
		<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>
		<script src="/source/js/main.js?v=2"></script>
	</body>
	</html>
	<?php
	exit;
}

/* ===== Данные категории ===== */
$catLabel = vp_category_label($cat);
$catUrl   = vp_category_url($cat);
$baseUrl  = 'https://vectorpola.ru' . $catUrl;

$allProducts = vp_products_by_category($cat);  // все активные товары категории
$config      = vp_filter_config($cat);
$facets      = vp_collect_facets($allProducts, $config);
[$priceMin, $priceMax] = vp_price_bounds($allProducts);

/* ===== GET: фильтры, сортировка, страница ===== */
$sort = in_array($_GET['sort'] ?? '', ['price_asc', 'price_desc'], true) ? $_GET['sort'] : 'default';
$page = max(1, (int)($_GET['page'] ?? 1));

$filtered = vp_apply_filters($allProducts, $config, $_GET);
$filtered = vp_sort_products($filtered, $sort);

$total      = count($filtered);
$totalPages = max(1, (int)ceil($total / VP_PER_PAGE));
if ($page > $totalPages) $page = $totalPages;
$offset     = ($page - 1) * VP_PER_PAGE;
$pageItems  = array_slice($filtered, $offset, VP_PER_PAGE);

/* Активны ли фильтры/сортировка/страница (для noindex и кнопки сброса) */
$hasFilters = false;
foreach ($config as $cfg) {
	if (!empty($_GET[$cfg['param']])) { $hasFilters = true; break; }
}
if (!$hasFilters && (($_GET['price_min'] ?? '') !== '' || ($_GET['price_max'] ?? '') !== '' || !empty($_GET['in_stock']))) {
	$hasFilters = true;
}
$isFiltered = $hasFilters || $sort !== 'default' || $page > 1;

/* ===== Хелперы ===== */
/* Текущие GET без cat + переопределения → query string */
function vp_qs(array $overrides = []): string {
	$base = $_GET;
	unset($base['cat']);
	$q = array_merge($base, $overrides);
	$q = array_filter($q, fn($v) => $v !== '' && $v !== null && $v !== []);
	return $q ? '?' . http_build_query($q) : '';
}

/* Форматирование рублей */
function vp_money($n): string { return number_format((float)$n, 0, '.', ' ') . ' ₽'; }

/* ===== SEO ===== */
$pageTitle = $catLabel . ' — купить в Москве и Красногорске | Вектор пола';
$pageDesc  = 'Каталог: ' . mb_strtolower($catLabel) . '. Подбор по образцам, расчёт материалов, доставка и укладка. Цены, наличие, характеристики.';
$canonical = $baseUrl; // фильтрованные/постраничные версии канонизируем на чистый URL
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- ===== SEO ===== -->
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
	<link rel="canonical" href="<?= $canonical ?>">
	<?php if ($isFiltered): ?><meta name="robots" content="noindex,follow"><?php endif; ?>

	<!-- ===== Open Graph ===== -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?= $canonical ?>">
	<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:site_name" content="Вектор пола">

	<!-- ===== Фавикон ===== -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	<link rel="stylesheet" href="/source/css/catalog.css?v=2">

	<!-- ===== Микроразметка (хлебные крошки) ===== -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "BreadcrumbList",
		"itemListElement": [
			{ "@type": "ListItem", "position": 1, "name": "Главная", "item": "https://vectorpola.ru/" },
			{ "@type": "ListItem", "position": 2, "name": "Каталог", "item": "https://vectorpola.ru/catalog/" },
			{ "@type": "ListItem", "position": 3, "name": <?= json_encode($catLabel, JSON_UNESCAPED_UNICODE) ?>, "item": <?= json_encode($baseUrl, JSON_UNESCAPED_UNICODE) ?> }
		]
	}
	</script>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ ШАПКА КАТЕГОРИИ ============ -->
	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<a href="/catalog/">Каталог</a>
				<span><?= htmlspecialchars($catLabel) ?></span>
			</nav>
			<h1><?= htmlspecialchars($catLabel) ?></h1>
			<p class="page-hero__sub"><?= $total ?> <?= vp_plural($total, 'товар', 'товара', 'товаров') ?> в наличии и под заказ</p>
		</div>
	</section>

	<!-- ============ КАТАЛОГ КАТЕГОРИИ ============ -->
	<section class="section">
		<div class="container">

			<?php if (empty($allProducts)): ?>
				<!-- Категория пока пуста -->
				<div class="catalog-empty">
					<h2>Раздел наполняется</h2>
					<p>Скоро здесь появятся товары этой категории. А пока — посмотрите другие разделы каталога или оставьте заявку, и мы подберём покрытие под вашу задачу.</p>
					<div class="catalog-empty__actions">
						<a href="/catalog/" class="btn btn--accent">Весь каталог</a>
						<a href="/#final" class="btn btn--outline">Оставить заявку</a>
					</div>
				</div>
			<?php else: ?>

				<form class="catalog-layout" method="get" action="<?= $catUrl ?>" id="catalogFilters">

					<!-- ===== Фильтры ===== -->
					<aside class="filters" id="filtersPanel">
						<div class="filters__head">
							<b>Фильтры</b>
							<button type="button" class="filters__close" aria-label="Закрыть фильтры">×</button>
						</div>

						<!-- Цена -->
						<div class="filter-group">
							<div class="filter-group__title">Цена, ₽</div>
							<div class="filter-price">
								<input type="number" name="price_min" inputmode="numeric" min="0"
									value="<?= htmlspecialchars($_GET['price_min'] ?? '') ?>" placeholder="<?= $priceMin ?>" aria-label="Цена от">
								<span>—</span>
								<input type="number" name="price_max" inputmode="numeric" min="0"
									value="<?= htmlspecialchars($_GET['price_max'] ?? '') ?>" placeholder="<?= $priceMax ?>" aria-label="Цена до">
							</div>
						</div>

						<!-- В наличии -->
						<div class="filter-group">
							<label class="filter-check">
								<input type="checkbox" name="in_stock" value="1" <?= !empty($_GET['in_stock']) ? 'checked' : '' ?>>
								<span>Только в наличии</span>
							</label>
						</div>

						<!-- Фасеты -->
						<?php foreach ($facets as $key => $f):
							$param = $f['cfg']['param'];
							$sel   = (array)($_GET[$param] ?? []);
						?>
						<div class="filter-group">
							<div class="filter-group__title"><?= htmlspecialchars($f['cfg']['label']) ?></div>
							<div class="filter-options">
								<?php foreach ($f['options'] as $val => $cnt): ?>
								<label class="filter-check">
									<input type="checkbox" name="<?= $param ?>[]" value="<?= htmlspecialchars($val) ?>" <?= in_array((string)$val, array_map('strval', $sel), true) ? 'checked' : '' ?>>
									<span><?= htmlspecialchars($val) ?> <i><?= $cnt ?></i></span>
								</label>
								<?php endforeach; ?>
							</div>
						</div>
						<?php endforeach; ?>

						<div class="filters__actions">
							<button type="submit" class="btn btn--accent">Показать</button>
							<?php if ($isFiltered): ?>
								<a href="<?= $catUrl ?>" class="filters__reset">Сбросить</a>
							<?php endif; ?>
						</div>
					</aside>

					<!-- ===== Результаты ===== -->
					<div class="catalog-main">

						<!-- Тулбар -->
						<div class="catalog-toolbar">
							<button type="button" class="filters-toggle" id="filtersToggle">
								<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" y1="6" x2="20" y2="6"/><line x1="7" y1="12" x2="17" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
								Фильтры
							</button>
							<div class="catalog-count"><?= $total ?> <?= vp_plural($total, 'товар', 'товара', 'товаров') ?></div>
							<label class="catalog-sort">
								<select name="sort" id="sortSelect" aria-label="Сортировка">
									<option value="default"    <?= $sort === 'default'    ? 'selected' : '' ?>>по умолчанию</option>
									<option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>сначала дешевле</option>
									<option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>сначала дороже</option>
								</select>
							</label>
						</div>

						<?php if (empty($pageItems)): ?>
							<!-- Ничего не найдено под фильтр -->
							<div class="catalog-noresult">
								<p>По выбранным фильтрам ничего не нашлось.</p>
								<a href="<?= $catUrl ?>" class="btn btn--outline">Сбросить фильтры</a>
							</div>
						<?php else: ?>

							<!-- Сетка карточек -->
							<div class="catalog-grid">
								<?php foreach ($pageItems as $p):
									$pImg   = !empty($p['images'][0]) ? $p['images'][0] : '/source/img/popular.webp';
									$pPrice = (float)($p['price'] ?? 0);
									$pOld   = isset($p['old_price']) ? (float)$p['old_price'] : 0;
									$pPack  = (float)($p['pack_area'] ?? 0);
									$perPack = $pPack > 0 ? $pPrice * $pPack : 0;
								?>
								<a class="cat-card" href="<?= htmlspecialchars(vp_product_url($p)) ?>">
									<div class="cat-card__img">
										<img src="<?= htmlspecialchars($pImg) ?>" alt="<?= htmlspecialchars($p['name']) ?>" width="800" height="800" loading="lazy">
										<?php if ($pOld > $pPrice && $pOld > 0): ?><span class="cat-card__badge">−<?= round(($pOld - $pPrice) / $pOld * 100) ?>%</span><?php endif; ?>
										<?php if (empty($p['in_stock'])): ?><span class="cat-card__stock">под заказ</span><?php endif; ?>
									</div>
									<div class="cat-card__body">
										<?php if (!empty($p['brand'])): ?><div class="cat-card__brand"><?= htmlspecialchars($p['brand']) ?></div><?php endif; ?>
										<div class="cat-card__name"><?= htmlspecialchars($p['name']) ?></div>
										<div class="cat-card__price">
											<b><?= vp_money($pPrice) ?></b> <small>/ <?= htmlspecialchars($p['unit'] ?? 'м²') ?></small>
											<?php if ($pOld > $pPrice && $pOld > 0): ?><s><?= vp_money($pOld) ?></s><?php endif; ?>
										</div>
										<?php if ($perPack > 0): ?><div class="cat-card__pack">от <?= vp_money($perPack) ?> / уп.</div><?php endif; ?>
									</div>
								</a>
								<?php endforeach; ?>
							</div>

							<!-- Пагинация -->
							<?php if ($totalPages > 1): ?>
							<nav class="pagination" aria-label="Постраничная навигация">
								<?php if ($page > 1): ?>
									<a class="pagination__btn" href="<?= $catUrl . vp_qs(['page' => $page - 1 === 1 ? null : $page - 1]) ?>" aria-label="Назад">←</a>
								<?php endif; ?>

								<?php
								// окно номеров: первая, текущая±2, последняя
								$nums = [];
								for ($i = 1; $i <= $totalPages; $i++) {
									if ($i === 1 || $i === $totalPages || abs($i - $page) <= 2) $nums[] = $i;
								}
								$prev = 0;
								foreach ($nums as $i):
									if ($prev && $i - $prev > 1) echo '<span class="pagination__dots">…</span>';
									$prev = $i;
								?>
									<?php if ($i === $page): ?>
										<span class="pagination__btn is-active"><?= $i ?></span>
									<?php else: ?>
										<a class="pagination__btn" href="<?= $catUrl . vp_qs(['page' => $i === 1 ? null : $i]) ?>"><?= $i ?></a>
									<?php endif; ?>
								<?php endforeach; ?>

								<?php if ($page < $totalPages): ?>
									<a class="pagination__btn" href="<?= $catUrl . vp_qs(['page' => $page + 1]) ?>" aria-label="Вперёд">→</a>
								<?php endif; ?>
							</nav>
							<?php endif; ?>

						<?php endif; ?>
					</div>
				</form>

			<?php endif; ?>
		</div>
	</section>

	<!-- Затемнение под мобильную панель фильтров -->
	<div class="filters-overlay" id="filtersOverlay" hidden></div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=2"></script>
</body>
</html>
<?php
/* Склонение существительного: 1 товар / 2 товара / 5 товаров */
function vp_plural(int $n, string $one, string $few, string $many): string {
	$n = abs($n) % 100;
	$n1 = $n % 10;
	if ($n > 10 && $n < 20) return $many;
	if ($n1 > 1 && $n1 < 5)  return $few;
	if ($n1 === 1)           return $one;
	return $many;
}