<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/source/php/catalog.php';

/* ===== Роутинг: cat + slug из .htaccess ===== */
$cat  = preg_replace('/[^a-z0-9-]/', '', $_GET['cat']  ?? '');
$slug = preg_replace('/[^a-z0-9-]/', '', $_GET['slug'] ?? '');

$product = $slug ? vp_find_product($slug) : null;

/* 404, если товара нет или категория в URL не совпадает с категорией товара */
if (!$product || ($product['category'] ?? '') !== $cat) {
	http_response_code(404);
	$pageTitle = 'Товар не найден — Вектор пола';
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
				<h1>Товар не найден</h1>
				<p class="lead-text" style="margin-top:12px;">Возможно, ссылка устарела или товар снят с продажи.</p>
				<a href="/" class="btn btn--accent" style="margin-top:8px;">На главную</a>
			</div>
		</section>
		<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>
		<script src="/source/js/main.js"></script>
		<script src="/source/js/product.js"></script>
	</body>
	</html>
	<?php
	exit;
}

/* ===== Данные товара ===== */
$catLabel  = vp_category_label($product['category']);
$canonical = 'https://vectorpola.ru' . vp_product_url($product);

$price     = (float)($product['price'] ?? 0);
$oldPrice  = isset($product['old_price']) ? (float)$product['old_price'] : 0;
$packArea  = (float)($product['pack_area'] ?? 0);
$unit      = $product['unit'] ?? 'м²';
$inStock   = !empty($product['in_stock']);
$images    = !empty($product['images']) ? $product['images'] : ['/source/img/popular.webp'];
$specs     = $product['specs'] ?? [];

/* Форматирование рублей: «2 475 ₽» */
function vp_money($n): string {
	return number_format((float)$n, 0, '.', ' ') . ' ₽';
}

/* Калькулятор: расчёт для исходного количества = 1 м² (без JS работает) */
$pricePerPack = $packArea > 0 ? $price * $packArea : $price;
$startArea    = 1;
$startPacks   = $packArea > 0 ? (int)ceil($startArea / $packArea) : 1;
$startTotalA  = $startPacks * $packArea;
$startTotal   = $startPacks * $pricePerPack;

/* SEO */
$pageTitle = $product['seo_title'] ?: ($product['name'] . ' — купить в Москве и Красногорске | Вектор пола');
$descSrc   = $product['seo_description'] ?: ($product['description'] ?? $product['name']);
$pageDesc  = mb_substr(trim($descSrc), 0, 200);
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
	<meta property="og:type" content="product">
	<meta property="og:url" content="<?= $canonical ?>">
	<meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
	<meta property="og:description" content="<?= htmlspecialchars($pageDesc) ?>">
	<meta property="og:image" content="https://vectorpola.ru<?= htmlspecialchars($images[0]) ?>">
	<meta property="og:locale" content="ru_RU">
	<meta property="og:site_name" content="Вектор пола">

	<!-- ===== Фавикон ===== -->
	<link rel="icon" type="image/svg+xml" href="/favicon.svg">
	<link rel="alternate icon" href="/favicon.ico">
	<meta name="theme-color" content="#2B2F38">

	<link rel="stylesheet" href="/source/css/main.css">
	<link rel="stylesheet" href="/source/css/inner-pages.css?v=2">
	<link rel="stylesheet" href="/source/css/product.css?v=4">

	<!-- ===== Микроразметка (Product + хлебные крошки) ===== -->
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "Product",
		"name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>,
		"image": <?= json_encode($canonical ? ('https://vectorpola.ru' . $images[0]) : '', JSON_UNESCAPED_UNICODE) ?>,
		"description": <?= json_encode($pageDesc, JSON_UNESCAPED_UNICODE) ?>,
		"brand": { "@type": "Brand", "name": <?= json_encode($product['brand'] ?? '', JSON_UNESCAPED_UNICODE) ?> },
		"offers": {
			"@type": "Offer",
			"url": <?= json_encode($canonical, JSON_UNESCAPED_UNICODE) ?>,
			"priceCurrency": "RUB",
			"price": "<?= $price ?>",
			"priceValidUntil": "<?= date('Y-12-31', strtotime('+1 year')) ?>",
			"itemCondition": "https://schema.org/NewCondition",
			"availability": "<?= $inStock ? 'https://schema.org/InStock' : 'https://schema.org/PreOrder' ?>"
		}
	}
	</script>
	<script type="application/ld+json">
	{
		"@context": "https://schema.org",
		"@type": "BreadcrumbList",
		"itemListElement": [
			{ "@type": "ListItem", "position": 1, "name": "Главная", "item": "https://vectorpola.ru/" },
			{ "@type": "ListItem", "position": 2, "name": "Каталог", "item": "https://vectorpola.ru/catalog/" },
			{ "@type": "ListItem", "position": 3, "name": <?= json_encode($catLabel, JSON_UNESCAPED_UNICODE) ?>, "item": "https://vectorpola.ru/catalog/<?= $product['category'] ?>/" },
			{ "@type": "ListItem", "position": 4, "name": <?= json_encode($product['name'], JSON_UNESCAPED_UNICODE) ?>, "item": <?= json_encode($canonical, JSON_UNESCAPED_UNICODE) ?> }
		]
	}
	</script>

	<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/metrika.html'; ?>
</head>
<body>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/header.php'; ?>

	<!-- ============ ШАПКА КАРТОЧКИ ============ -->
	<section class="page-hero page-hero--product">
		<div class="container">
			<nav class="breadcrumb" aria-label="Хлебные крошки">
				<a href="/">Главная</a>
				<a href="/catalog/">Каталог</a>
				<a href="/catalog/<?= $product['category'] ?>/"><?= htmlspecialchars($catLabel) ?></a>
				<span><?= htmlspecialchars($product['name']) ?></span>
			</nav>
			<h1><?= htmlspecialchars($product['name']) ?></h1>
		</div>
	</section>

	<!-- ============ КАРТОЧКА ============ -->
	<section class="section">
		<div class="container product">
			<div class="product-grid">

				<!-- Галерея -->
				<div class="product-gallery">
					<div class="pg-main">
						<img id="pgMain" src="<?= htmlspecialchars($images[0]) ?>" alt="<?= htmlspecialchars($product['name']) ?>" width="800" height="800">
					</div>
					<?php if (count($images) > 1): ?>
					<div class="pg-thumbs">
						<?php foreach ($images as $i => $img): ?>
						<button type="button" class="pg-thumb <?= $i === 0 ? 'is-active' : '' ?>" data-img="<?= htmlspecialchars($img) ?>" aria-label="Фото <?= $i + 1 ?>">
							<img src="<?= htmlspecialchars($img) ?>" alt="" width="120" height="120" loading="lazy">
						</button>
						<?php endforeach; ?>
					</div>
					<?php endif; ?>
				</div>

				<!-- Калькулятор + заявка -->
				<aside class="product-calc"
					data-name="<?= htmlspecialchars($product['name']) ?>"
					data-slug="<?= htmlspecialchars($product['slug']) ?>"
					data-url="<?= htmlspecialchars(vp_product_url($product)) ?>"
					data-image="<?= htmlspecialchars($images[0]) ?>"
					data-unit="<?= htmlspecialchars($unit) ?>"
					data-price="<?= $price ?>"
					data-pack-area="<?= $packArea ?>"
					data-price-per-pack="<?= $pricePerPack ?>">

					<div class="calc-head">
						<span class="calc-ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="10" x2="10" y2="10"/><line x1="12" y1="10" x2="16" y2="10"/><line x1="8" y1="14" x2="10" y2="14"/><line x1="12" y1="14" x2="16" y2="14"/><line x1="8" y1="18" x2="10" y2="18"/><line x1="12" y1="18" x2="16" y2="18"/></svg></span>
						<b>Калькулятор стоимости</b>
					</div>

					<!-- Степпер: количество м² -->
					<div class="calc-stepper">
						<button type="button" class="calc-minus" aria-label="Меньше">−</button>
						<div class="calc-qty"><span id="calcArea"><?= $startArea ?></span> м²</div>
						<button type="button" class="calc-plus" aria-label="Больше">+</button>
					</div>

					<!-- Разбивка -->
					<div class="calc-rows">
						<div class="calc-row">
							<div class="calc-val">
								<?php if ($oldPrice > $price): ?><s><?= vp_money($oldPrice) ?></s> <?php endif; ?><b><?= vp_money($price) ?></b>
							</div>
							<div class="calc-label">Цена за квадратный метр</div>
						</div>
						<div class="calc-row">
							<div class="calc-val"><b><?= rtrim(rtrim(number_format($packArea, 2, '.', ''), '0'), '.') ?> м²</b></div>
							<div class="calc-label">Кв. метров в упаковке</div>
						</div>
						<div class="calc-row calc-row--split">
							<div>
								<div class="calc-val"><b><?= vp_money($pricePerPack) ?></b></div>
								<div class="calc-label">Цена за пачку</div>
							</div>
							<div>
								<div class="calc-val"><b id="calcPacks"><?= $startPacks ?></b></div>
								<div class="calc-label">Количество пачек</div>
							</div>
						</div>
						<div class="calc-row">
							<div class="calc-val"><b id="calcTotalArea"><?= rtrim(rtrim(number_format($startTotalA, 3, '.', ''), '0'), '.') ?></b> м²</div>
							<div class="calc-label">Количество кв.м.</div>
						</div>
					</div>

					<div class="calc-stock <?= $inStock ? 'is-in' : 'is-pre' ?>">
						<?= $inStock ? '✓ На складе' : 'Под заказ' ?>
					</div>

					<div class="calc-total">
						<span>Итого к оплате</span>
						<b id="calcTotal"><?= vp_money($startTotal) ?></b>
					</div>

					<!-- Действия: корзина + покупка в один клик -->
					<div class="calc-actions">
						<button type="button" class="btn btn--accent calc-add">В корзину</button>
						<button type="button" class="btn btn--outline calc-quick">Купить в один клик</button>
					</div>
					<div class="calc-added" hidden>Товар добавлен в корзину. <a href="/cart/">Перейти в корзину →</a></div>
					<p class="calc-note">Нажимая кнопку, вы оставляете заявку — менеджер свяжется с вами, уточнит наличие и поможет с расчётом.</p>
				</aside>
			</div>

			<!-- ============ ОПИСАНИЕ + ХАРАКТЕРИСТИКИ ============ -->
			<div class="product-info">
				<?php if (!empty($product['description'])): ?>
				<div class="product-block">
					<h2>Описание</h2>
					<p><?= htmlspecialchars($product['description']) ?></p>
				</div>
				<?php endif; ?>

				<?php if ($specs): ?>
				<div class="product-block">
					<h2>Характеристики</h2>
					<table class="specs">
						<tbody>
							<?php foreach ($specs as $k => $v): ?>
							<tr>
								<th><?= htmlspecialchars($k) ?></th>
								<td><?= htmlspecialchars($v) ?></td>
							</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
				<?php endif; ?>

				<!-- Поделиться (компактно, без левого рельса) -->
				<div class="product-share">
					<span>Поделиться:</span>
					<a class="sh-btn" href="https://api.whatsapp.com/send?text=<?= rawurlencode($canonical) ?>" target="_blank" rel="noopener" aria-label="Поделиться в WhatsApp">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19.05 4.91A9.82 9.82 0 0012.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 004.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.02zm-7.01 15.16a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 012.41 5.82c0 4.54-3.7 8.23-8.23 8.23z"/></svg>
					</a>
					<a class="sh-btn" href="https://t.me/share/url?url=<?= rawurlencode($canonical) ?>&text=<?= rawurlencode($product['name']) ?>" target="_blank" rel="noopener" aria-label="Поделиться в Telegram">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M21.94 4.6l-3.3 15.56c-.25 1.1-.9 1.37-1.83.85l-5.04-3.72-2.43 2.34c-.27.27-.5.5-1.01.5l.36-5.13L18.02 6.1c.4-.36-.09-.56-.62-.2L5.92 13.2.83 11.6c-1.1-.34-1.12-1.1.23-1.63L20.5 2.4c.92-.34 1.72.22 1.44 2.2z"/></svg>
					</a>
					<button type="button" class="sh-btn sh-copy" data-url="<?= htmlspecialchars($canonical) ?>" aria-label="Скопировать ссылку">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 007 0l3-3a5 5 0 00-7-7l-1 1"/><path d="M14 11a5 5 0 00-7 0l-3 3a5 5 0 007 7l1-1"/></svg>
					</button>
					<span class="sh-copied" id="shCopied" hidden>Ссылка скопирована</span>
				</div>
			</div>
		</div>
	</section>

	<!-- ============ ПОПАП «КУПИТЬ В ОДИН КЛИК» ============ -->
	<div class="modal" id="quickBuyModal" hidden>
		<div class="modal__overlay" data-close></div>
		<div class="modal__box" role="dialog" aria-modal="true" aria-labelledby="qbTitle">
			<button type="button" class="modal__close" data-close aria-label="Закрыть">×</button>
			<h3 id="qbTitle">Купить в один клик</h3>
			<div class="qb-summary">
				<div class="qb-prod"><?= htmlspecialchars($product['name']) ?></div>
				<div class="qb-line"><span>Количество</span><b id="qbQty">—</b></div>
				<div class="qb-line"><span>Итого</span><b id="qbTotal">—</b></div>
			</div>
			<div class="qb-form">
				<input type="text" name="name" placeholder="Имя" aria-label="Ваше имя" autocomplete="name">
				<input type="tel" name="phone" placeholder="+7 (___) ___-__-__" aria-label="Телефон" autocomplete="tel">
				<textarea name="comment" rows="2" placeholder="Комментарий (необязательно)" aria-label="Комментарий"></textarea>
				<button type="button" class="btn btn--accent">Отправить заявку</button>
				<div class="form-status"></div>
				<p class="qb-note">Оплата не требуется. Менеджер свяжется для подтверждения.</p>
			</div>
		</div>
	</div>

<?php include $_SERVER['DOCUMENT_ROOT'] . '/source/include/footer.php'; ?>

	<script src="/source/js/main.js?v=3"></script>
	<script src="/source/js/cart.js?v=3"></script>
	<script src="/source/js/product.js?v=2"></script>
</body>
</html>