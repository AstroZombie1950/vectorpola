<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';

/* Считаем товары по категориям (быстрый GROUP BY из sqlite) */
$counts = vp_category_counts();

function vp_plural(int $n, string $one, string $few, string $many): string {
	$n = abs($n) % 100; $n1 = $n % 10;
	if ($n > 10 && $n < 20) return $many;
	if ($n1 > 1 && $n1 < 5)  return $few;
	if ($n1 === 1)           return $one;
	return $many;
}

$pageTitle = 'Каталог напольных покрытий — Вектор пола';
$pageDesc  = 'Каталог напольных покрытий: ламинат, кварцвинил и SPC, инженерная и паркетная доска, плинтусы и сопутствующие материалы. Подбор по образцам, доставка и укладка.';
$canonical = 'https://vectorpola.ru/catalog/';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<title><?= htmlspecialchars($pageTitle) ?></title>
	<meta name="description" content="<?= htmlspecialchars($pageDesc) ?>">
	<link rel="canonical" href="<?= $canonical ?>">

	<meta property="og:type" content="website">
	<meta property="og:url" content="<?= $canonical ?>">
	<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:site_name" content="Вектор пола">

	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	<link rel="stylesheet" href="/source/css/catalog.css?v=2">

	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "BreadcrumbList",
		"itemListElement": [
			{ "@type": "ListItem", "position": 1, "name": "Главная", "item": "https://vectorpola.ru/" },
			{ "@type": "ListItem", "position": 2, "name": "Каталог", "item": "https://vectorpola.ru/catalog/" }
		]
	}
	</script>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<section class="page-hero">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<span>Каталог</span>
			</nav>
			<h1>Каталог напольных покрытий</h1>
			<p class="page-hero__sub">Выберите раздел — внутри удобные фильтры по цене, бренду и характеристикам.</p>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="cat-index-grid">
				<?php foreach (VP_CATEGORIES as $slug => $label):
					$cnt = $counts[$slug] ?? 0; ?>
				<a class="cat-index-tile" href="<?= vp_category_url($slug) ?>">
					<span class="cat-index-tile__ic">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="4"/><rect x="3" y="10" width="18" height="4"/><rect x="3" y="16" width="18" height="4"/></svg>
					</span>
					<span class="cat-index-tile__body">
						<b><?= htmlspecialchars($label) ?></b>
						<i><?= $cnt > 0 ? $cnt . ' ' . vp_plural($cnt, 'товар', 'товара', 'товаров') : 'скоро в продаже' ?></i>
					</span>
					<svg class="cat-index-tile__arr" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=3"></script>
</body>
</html>