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

$rows   = vp_sitemap_rows();        // активные: slug, category, updated_at
$counts = vp_category_counts();     // непустые категории

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

/* Статика */
foreach (['/', '/catalog/', '/about/', '/contacts/', '/delivery/', '/returns/', '/brands/'] as $path) {
	echo vp_url_node($path);
}

/* Категории — только непустые */
foreach (array_keys($counts) as $cat) {
	if (isset(VP_CATEGORIES[$cat])) echo vp_url_node(vp_category_url($cat));
}

/* Товары — все активные, lastmod из updated_at */
foreach ($rows as $r) {
	if (empty($r['slug']) || empty($r['category'])) continue;
	$lastmod = substr((string)($r['updated_at'] ?? ''), 0, 10); // YYYY-MM-DD
	echo vp_url_node(vp_product_url($r), $lastmod);
}

echo '</urlset>';