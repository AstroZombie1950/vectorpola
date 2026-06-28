<?php
/* ===================================================
   Динамический sitemap.xml.
   Отдаётся по /sitemap.xml через rewrite в .htaccess.
   Источник данных — catalog.php (активные товары, категории).
   =================================================== */

require_once __DIR__ . '/source/php/catalog.php';

const VP_HOST = 'https://vectorpola.ru';   // без www — синхронно с .htaccess

header('Content-Type: application/xml; charset=utf-8');

/* Экранируем URL для XML */
function vp_xml_loc(string $path): string {
	return htmlspecialchars(VP_HOST . $path, ENT_XML1, 'UTF-8');
}

/* Один <url> с опциональным lastmod */
function vp_url_node(string $path, string $lastmod = ''): string {
	$out = "\t<url>\n\t\t<loc>" . vp_xml_loc($path) . "</loc>\n";
	if ($lastmod !== '') {
		$out .= "\t\t<lastmod>" . htmlspecialchars($lastmod, ENT_XML1, 'UTF-8') . "</lastmod>\n";
	}
	$out .= "\t</url>\n";
	return $out;
}

$products = vp_load_products();   // уже только активные

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

/* Статика */
foreach (['/', '/catalog/', '/about/', '/contacts/', '/delivery/', '/returns/', '/brands/'] as $p) {
	echo vp_url_node($p);
}

/* Категории — только непустые (есть хотя бы один активный товар) */
$catSeen = [];
foreach ($products as $p) {
	$cat = $p['category'] ?? '';
	if ($cat !== '' && isset(VP_CATEGORIES[$cat])) {
		$catSeen[$cat] = true;
	}
}
foreach (array_keys($catSeen) as $cat) {
	echo vp_url_node(vp_category_url($cat));
}

/* Товары — все активные, lastmod из updated_at (формат YYYY-MM-DD) */
foreach ($products as $p) {
	if (empty($p['slug']) || empty($p['category'])) continue;
	$lastmod = (string)($p['updated_at'] ?? '');
	echo vp_url_node(vp_product_url($p), $lastmod);
}

echo '</urlset>';