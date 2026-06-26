<?php
/* ===================================================
   Каталог: категории, фильтры, доступ к товарам.
   Единый источник правды по категориям и фасетам.
   Подключать через require_once.
   =================================================== */

/* Слаг категории → отображаемое название.
   Слаги участвуют в URL — не менять без 301-редиректа.
   ЭТО ЕДИНЫЙ СПИСОК: на него же ссылается админка. */
const VP_CATEGORIES = [
	'laminat'            => 'Ламинат',
	'kvarcvinil'         => 'Кварцвинил / SPC',
	'vinil'              => 'Виниловые полы',
	'inzhenernaya-doska' => 'Инженерная доска',
	'parketnaya-doska'   => 'Паркетная доска',
	'massivnaya-doska'   => 'Массивная доска',
	'probka'             => 'Пробковые покрытия',
	'plintus-podlozhka'  => 'Плинтусы и подложка',
	'soputstvuyushchie'  => 'Сопутствующие товары',
];

/* Конфиг фильтров по категориям.
   key   — поле фасета: 'brand' (берётся из product.brand) или имя ключа в specs.
   param — короткое имя GET-параметра (латиница, без пробелов).
   type  — checkbox (мультивыбор) | range (диапазон, только цена — она отдельно).
   Набор ключей specs финализируется после интеграции спарсенных данных. */
const VP_FILTERS = [
	'laminat' => [
		'brand'                     => ['label' => 'Бренд',       'param' => 'brand',   'type' => 'checkbox'],
		'Класс истираемости'        => ['label' => 'Класс',       'param' => 'class',   'type' => 'checkbox'],
		'Тип рисунка'               => ['label' => 'Тип рисунка', 'param' => 'pattern', 'type' => 'checkbox'],
		'Фаска'                     => ['label' => 'Фаска',       'param' => 'bevel',   'type' => 'checkbox'],
		'Тёплый пол, совместимость' => ['label' => 'Тёплый пол',  'param' => 'warm',    'type' => 'checkbox'],
	],
	'kvarcvinil' => [
		'brand'                     => ['label' => 'Бренд',          'param' => 'brand', 'type' => 'checkbox'],
		'Класс истираемости'        => ['label' => 'Класс',          'param' => 'class', 'type' => 'checkbox'],
		'Тип соединения'            => ['label' => 'Тип соединения', 'param' => 'joint', 'type' => 'checkbox'],
		'Тёплый пол, совместимость' => ['label' => 'Тёплый пол',     'param' => 'warm',  'type' => 'checkbox'],
		'Водостойкость'             => ['label' => 'Водостойкость',  'param' => 'water', 'type' => 'checkbox'],
	],
	'parketnaya-doska' => [
		'brand'                => ['label' => 'Бренд',         'param' => 'brand',   'type' => 'checkbox'],
		'Порода дерева'        => ['label' => 'Порода',        'param' => 'wood',    'type' => 'checkbox'],
		'Тип покрытия'         => ['label' => 'Покрытие',      'param' => 'finish',  'type' => 'checkbox'],
		'Количество полос'     => ['label' => 'Полосность',    'param' => 'strips',  'type' => 'checkbox'],
		'Селекция'             => ['label' => 'Селекция',      'param' => 'select',  'type' => 'checkbox'],
	],
	'inzhenernaya-doska' => [
		'brand'         => ['label' => 'Бренд',    'param' => 'brand',  'type' => 'checkbox'],
		'Порода дерева' => ['label' => 'Порода',   'param' => 'wood',   'type' => 'checkbox'],
		'Тип покрытия'  => ['label' => 'Покрытие', 'param' => 'finish', 'type' => 'checkbox'],
		'Селекция'      => ['label' => 'Селекция', 'param' => 'select', 'type' => 'checkbox'],
	],
];

/* Кол-во товаров на странице категории */
const VP_PER_PAGE = 24;

/* Путь к хранилищу товаров */
function vp_products_path(): string {
	return $_SERVER['DOCUMENT_ROOT'] . '/data/products.json';
}

/* Все активные товары (массив). Формат файла: {"products":[...]} */
function vp_load_products(): array {
	$path = vp_products_path();
	if (!is_file($path)) return [];
	$raw  = file_get_contents($path);
	$data = json_decode($raw, true);
	$list = $data['products'] ?? [];
	return array_values(array_filter($list, fn($p) => !empty($p['active'])));
}

/* Найти товар по слагу (или null) */
function vp_find_product(string $slug): ?array {
	foreach (vp_load_products() as $p) {
		if (($p['slug'] ?? '') === $slug) return $p;
	}
	return null;
}

/* Название категории по слагу */
function vp_category_label(string $slug): string {
	return VP_CATEGORIES[$slug] ?? $slug;
}

/* Канонический URL карточки товара */
function vp_product_url(array $p): string {
	return '/catalog/' . $p['category'] . '/' . $p['slug'] . '/';
}

/* URL страницы категории */
function vp_category_url(string $cat): string {
	return '/catalog/' . $cat . '/';
}

/* Активные товары одной категории */
function vp_products_by_category(string $cat): array {
	return array_values(array_filter(vp_load_products(), fn($p) => ($p['category'] ?? '') === $cat));
}

/* Конфиг фильтров категории (или дефолт — только бренд) */
function vp_filter_config(string $cat): array {
	return VP_FILTERS[$cat] ?? [
		'brand' => ['label' => 'Бренд', 'param' => 'brand', 'type' => 'checkbox'],
	];
}

/* Значение фасета у товара: brand — из поля, остальное — из specs */
function vp_facet_value(array $p, string $key): string {
	if ($key === 'brand') return trim($p['brand'] ?? '');
	return trim($p['specs'][$key] ?? '');
}

/* Собрать опции фасетов: для каждого ключа — список {значение → количество}.
   Считаем по всем товарам категории, чтобы пользователь видел доступные варианты. */
function vp_collect_facets(array $products, array $config): array {
	$facets = [];
	foreach ($config as $key => $cfg) {
		$counts = [];
		foreach ($products as $p) {
			$v = vp_facet_value($p, $key);
			if ($v === '') continue;
			$counts[$v] = ($counts[$v] ?? 0) + 1;
		}
		// сортируем значения по алфавиту/числу
		uksort($counts, fn($a, $b) => strnatcasecmp($a, $b));
		if ($counts) $facets[$key] = ['cfg' => $cfg, 'options' => $counts];
	}
	return $facets;
}

/* Применить фильтры из GET к списку товаров категории */
function vp_apply_filters(array $products, array $config, array $get): array {
	// Диапазон цены
	$pmin = isset($get['price_min']) && $get['price_min'] !== '' ? (float)$get['price_min'] : null;
	$pmax = isset($get['price_max']) && $get['price_max'] !== '' ? (float)$get['price_max'] : null;
	// Только в наличии
	$onlyStock = !empty($get['in_stock']);

	return array_values(array_filter($products, function ($p) use ($config, $get, $pmin, $pmax, $onlyStock) {
		$price = (float)($p['price'] ?? 0);
		if ($pmin !== null && $price < $pmin) return false;
		if ($pmax !== null && $price > $pmax) return false;
		if ($onlyStock && empty($p['in_stock'])) return false;

		// Фасеты-чекбоксы (мультивыбор → ИЛИ внутри, И между фасетами)
		foreach ($config as $key => $cfg) {
			$param = $cfg['param'];
			$sel   = $get[$param] ?? null;
			if (empty($sel)) continue;
			$sel   = (array)$sel;
			$v     = vp_facet_value($p, $key);
			if (!in_array($v, $sel, true)) return false;
		}
		return true;
	}));
}

/* Сортировка: price_asc | price_desc | default (в наличии → цена → имя) */
function vp_sort_products(array $products, string $sort): array {
	usort($products, function ($a, $b) use ($sort) {
		$pa = (float)($a['price'] ?? 0);
		$pb = (float)($b['price'] ?? 0);
		if ($sort === 'price_asc')  return $pa <=> $pb;
		if ($sort === 'price_desc') return $pb <=> $pa;
		// default: сначала в наличии, потом дешевле, потом по имени
		$sa = !empty($a['in_stock']) ? 0 : 1;
		$sb = !empty($b['in_stock']) ? 0 : 1;
		if ($sa !== $sb) return $sa <=> $sb;
		if ($pa !== $pb) return $pa <=> $pb;
		return strnatcasecmp($a['name'] ?? '', $b['name'] ?? '');
	});
	return $products;
}

/* Границы цены по списку (для подсказок в полях диапазона) */
function vp_price_bounds(array $products): array {
	$prices = array_map(fn($p) => (float)($p['price'] ?? 0), $products);
	$prices = array_filter($prices, fn($v) => $v > 0);
	if (!$prices) return [0, 0];
	return [(int)floor(min($prices)), (int)ceil(max($prices))];
}

/* Транслитерация в slug: кириллица → латиница, нижний регистр, дефисы */
function vp_slugify(string $s): string {
	$map = [
		'а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'zh',
		'з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o',
		'п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c',
		'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'','ы'=>'y','ь'=>'','э'=>'e','ю'=>'yu','я'=>'ya',
	];
	$s = mb_strtolower(trim($s), 'UTF-8');
	$s = strtr($s, $map);
	$s = preg_replace('/[^a-z0-9]+/u', '-', $s); // всё лишнее → дефис
	$s = trim($s, '-');
	return $s !== '' ? $s : 'tovar';
}