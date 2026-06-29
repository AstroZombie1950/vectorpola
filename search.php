<?php
/* ===================================================
   Страница результатов поиска: /search/?q=...
   Ищет по названию и бренду (SQLite, search_text).
   noindex,follow — внутренний поиск не индексируем.
   =================================================== */
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';

/* Форматирование цены (локально — vp_money объявлена в карточке/категории) */
if (!function_exists('vp_money')) {
	function vp_money($n): string { return number_format((float)$n, 0, '.', ' ') . ' ₽'; }
}

$q    = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));

$hasQuery = ($q !== '');
$items = [];
$total = 0;
$totalPages = 1;

if ($hasQuery) {
	$res        = vp_search_products($q, $page);
	$total      = $res['total'];
	$totalPages = max(1, (int)ceil($total / 24));
	if ($page > $totalPages) {
		$page = $totalPages;
		$res  = vp_search_products($q, $page);
	}
	$items = $res['items'];
}

/* URL для пагинации с сохранением q */
function vp_search_url(string $q, int $page): string {
	$qs = ['q' => $q];
	if ($page > 1) $qs['page'] = $page;
	return '/search/?' . http_build_query($qs);
}

$pageTitle = $hasQuery
	? ('Поиск: ' . $q . ' — Вектор пола')
	: 'Поиск по каталогу — Вектор пола';
$pageDesc  = 'Поиск напольных покрытий по названию и бренду в каталоге «Вектор пола».';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<!-- ===== SEO: внутренний поиск не индексируем ===== -->
	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta name="robots" content="noindex,follow">
	<link rel="canonical" href="https://vectorpola.ru/search/">

	<!-- ===== Фавикон ===== -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css?v=5">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	<link rel="stylesheet" href="/source/css/catalog.css?v=2">

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ ШАПКА ПОИСКА ============ -->
	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<span>Поиск</span>
			</nav>
			<h1>Поиск по каталогу</h1>

			<!-- Форма поиска (всегда видна, удобно уточнить запрос) -->
			<form class="cat-search" role="search" method="get" action="/search/" style="margin-top:16px">
				<input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Введите название или бренд" aria-label="Поиск по каталогу">
				<button type="submit" class="btn btn--accent">Найти</button>
			</form>

			<?php if ($hasQuery): ?>
				<p class="page-hero__sub" style="margin-top:14px">
					По запросу «<?= htmlspecialchars($q) ?>» — <?= $total ?> <?= vp_plural_s($total) ?>
				</p>
			<?php endif; ?>
		</div>
	</section>

	<!-- ============ РЕЗУЛЬТАТЫ ============ -->
	<section class="section">
		<div class="container">

			<?php if (!$hasQuery): ?>
				<!-- Пустой запрос — мягкая подсказка -->
				<div class="catalog-empty">
					<h2>Что ищем?</h2>
					<p>Введите название покрытия или бренд — например, «дуб», «Tarkett» или «ламинат 33 класс». Или загляните в <a href="/catalog/">каталог</a>.</p>
				</div>

			<?php elseif (empty($items)): ?>
				<!-- Ничего не найдено -->
				<div class="catalog-empty">
					<h2>Ничего не нашлось</h2>
					<p>По запросу «<?= htmlspecialchars($q) ?>» товаров нет. Попробуйте другое название или бренд, либо посмотрите весь <a href="/catalog/">каталог</a>.</p>
					<div class="catalog-empty__actions">
						<a href="/catalog/" class="btn btn--accent">Весь каталог</a>
						<a href="/#final" class="btn btn--outline">Оставить заявку</a>
					</div>
				</div>

			<?php else: ?>
				<!-- Сетка карточек (как в категории) -->
				<div class="catalog-grid">
					<?php foreach ($items as $p):
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
						<a class="pagination__btn" href="<?= htmlspecialchars(vp_search_url($q, $page - 1)) ?>" aria-label="Назад">←</a>
					<?php endif; ?>
					<?php
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
							<a class="pagination__btn" href="<?= htmlspecialchars(vp_search_url($q, $i)) ?>"><?= $i ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
					<?php if ($page < $totalPages): ?>
						<a class="pagination__btn" href="<?= htmlspecialchars(vp_search_url($q, $page + 1)) ?>" aria-label="Вперёд">→</a>
					<?php endif; ?>
				</nav>
				<?php endif; ?>

			<?php endif; ?>
		</div>
	</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=4"></script>
</body>
</html>
<?php
/* Склонение «товар» (локальный хелпер, чтобы не тянуть из категории) */
function vp_plural_s(int $n): string {
	$nn = abs($n) % 100; $n1 = $nn % 10;
	if ($nn > 10 && $nn < 20) return 'товаров';
	if ($n1 > 1 && $n1 < 5)  return 'товара';
	if ($n1 === 1)           return 'товар';
	return 'товаров';
}
