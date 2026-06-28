<?php
/* ===================================================
   Каталог: категории, фильтры, доступ к товарам.
   Единый источник правды по категориям и фасетам.
   Данные читаются из data/catalog.sqlite (быстрый read-кеш).
   Источник истины — data/products.json; sqlite пересобирается
   из него при любой правке в админке (vp_rebuild_sqlite).
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
   key   — поле фасета: 'brand' (из product.brand) или имя ключа в specs.
   param — короткое имя GET-параметра (латиница).
   type  — checkbox (мультивыбор). Цена и наличие — отдельно.
   Порядок ключей = порядок отрисовки фасетов. */
const VP_FILTERS = [
	'laminat' => [
		'brand'              => ['label' => 'Бренд',   'param' => 'brand',     'type' => 'checkbox'],
		'Класс истираемости' => ['label' => 'Класс',   'param' => 'class',     'type' => 'checkbox'],
		'Толщина, мм'        => ['label' => 'Толщина', 'param' => 'thickness', 'type' => 'checkbox'],
	],
	'kvarcvinil' => [
		'brand'              => ['label' => 'Бренд',       'param' => 'brand',     'type' => 'checkbox'],
		'Класс истираемости' => ['label' => 'Класс',       'param' => 'class',     'type' => 'checkbox'],
		'Толщина, мм'        => ['label' => 'Толщина',     'param' => 'thickness', 'type' => 'checkbox'],
		'Тип укладки'        => ['label' => 'Тип укладки', 'param' => 'lay',       'type' => 'checkbox'],
	],
	'parketnaya-doska' => [
		'brand'       => ['label' => 'Бренд',    'param' => 'brand',     'type' => 'checkbox'],
		'Порода'      => ['label' => 'Порода',   'param' => 'wood',      'type' => 'checkbox'],
		'Покрытие'    => ['label' => 'Покрытие', 'param' => 'finish',    'type' => 'checkbox'],
		'Толщина, мм' => ['label' => 'Толщина',  'param' => 'thickness', 'type' => 'checkbox'],
	],
	'inzhenernaya-doska' => [
		'brand'       => ['label' => 'Бренд',       'param' => 'brand',     'type' => 'checkbox'],
		'Тип укладки' => ['label' => 'Тип укладки', 'param' => 'lay',       'type' => 'checkbox'],
		'Покрытие'    => ['label' => 'Покрытие',    'param' => 'finish',    'type' => 'checkbox'],
		'Толщина, мм' => ['label' => 'Толщина',     'param' => 'thickness', 'type' => 'checkbox'],
		'Порода'      => ['label' => 'Порода',      'param' => 'wood',      'type' => 'checkbox'],
	],
];

/* Кол-во товаров на странице категории */
const VP_PER_PAGE = 24;

/* ===================================================
   Доступ к данным (SQLite)
   =================================================== */

/* Путь к файлам хранилища */
function vp_sqlite_path(): string   { return $_SERVER['DOCUMENT_ROOT'] . '/data/catalog.sqlite'; }
function vp_products_path(): string { return $_SERVER['DOCUMENT_ROOT'] . '/data/products.json'; }

/* PDO-соединение с базой каталога (синглтон на запрос) */
function vp_db(): ?PDO {
	static $db = false;
	if ($db === false) {
		$path = vp_sqlite_path();
		if (!is_file($path)) { $db = null; return null; }
		try {
			$db = new PDO('sqlite:' . $path, null, null, [
				PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
				PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			]);
		} catch (Throwable $e) {
			$db = null;
		}
	}
	return $db;
}

/* Привести строку БД к форме, которую ждут шаблоны (как в products.json) */
function vp_hydrate(array $r): array {
	$r['images']    = !empty($r['images']) ? (json_decode($r['images'], true) ?: []) : [];
	$r['specs']     = !empty($r['specs'])  ? (json_decode($r['specs'], true)  ?: []) : [];
	$r['in_stock']  = (int)($r['in_stock'] ?? 0) === 1;
	$r['active']    = (int)($r['active'] ?? 0) === 1;
	$r['price']     = (float)($r['price'] ?? 0);
	$r['pack_area'] = (float)($r['pack_area'] ?? 0);
	$r['old_price'] = ($r['old_price'] ?? null) !== null ? (float)$r['old_price'] : null;
	$r['popular']   = (int)($r['popular'] ?? 0) === 1;
	return $r;
}

/* Найти активный товар по слагу (или null) */
function vp_find_product(string $slug): ?array {
	$db = vp_db();
	if (!$db) return null;
	$st = $db->prepare('SELECT * FROM products WHERE slug = ? AND active = 1 LIMIT 1');
	$st->execute([$slug]);
	$row = $st->fetch();
	return $row ? vp_hydrate($row) : null;
}

/* Кол-во активных товаров в категории (без фильтров) */
function vp_category_count(string $cat): int {
	$db = vp_db();
	if (!$db) return 0;
	$st = $db->prepare('SELECT COUNT(*) FROM products WHERE category = ? AND active = 1');
	$st->execute([$cat]);
	return (int)$st->fetchColumn();
}

/* Кол-во активных товаров по всем категориям: [slug => count] */
function vp_category_counts(): array {
	$db = vp_db();
	if (!$db) return [];
	$out = [];
	foreach ($db->query('SELECT category, COUNT(*) c FROM products WHERE active = 1 GROUP BY category') as $r) {
		$out[$r['category']] = (int)$r['c'];
	}
	return $out;
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

/* Конфиг фильтров категории (или дефолт — только бренд) */
function vp_filter_config(string $cat): array {
	return VP_FILTERS[$cat] ?? [
		'brand' => ['label' => 'Бренд', 'param' => 'brand', 'type' => 'checkbox'],
	];
}

/* Опции фасетов категории: [key => ['cfg'=>..., 'options'=>[значение => количество]]].
   Считаем по всем активным товарам категории — чтобы видеть доступные варианты. */
function vp_category_facets(string $cat): array {
	$db = vp_db();
	if (!$db) return [];
	$config = vp_filter_config($cat);
	$keys   = array_keys($config);
	if (!$keys) return [];

	$ph = implode(',', array_fill(0, count($keys), '?'));
	$st = $db->prepare(
		"SELECT facet_key, facet_value, COUNT(*) c
		 FROM product_facets
		 WHERE category = ? AND active = 1 AND facet_key IN ($ph)
		 GROUP BY facet_key, facet_value"
	);
	$st->execute(array_merge([$cat], $keys));

	$byKey = [];
	foreach ($st as $r) {
		$byKey[$r['facet_key']][(string)$r['facet_value']] = (int)$r['c'];
	}

	// Собираем в порядке конфига, значения — натуральной сортировкой
	$facets = [];
	foreach ($config as $key => $cfg) {
		if (empty($byKey[$key])) continue;
		$opts = $byKey[$key];
		uksort($opts, fn($a, $b) => strnatcasecmp($a, $b));
		$facets[$key] = ['cfg' => $cfg, 'options' => $opts];
	}
	return $facets;
}

/* Границы цены активных товаров категории (для плейсхолдеров диапазона) */
function vp_category_price_bounds(string $cat): array {
	$db = vp_db();
	if (!$db) return [0, 0];
	$st = $db->prepare('SELECT MIN(price) mn, MAX(price) mx FROM products WHERE category = ? AND active = 1 AND price > 0');
	$st->execute([$cat]);
	$r = $st->fetch();
	if (!$r || $r['mn'] === null) return [0, 0];
	return [(int)floor($r['mn']), (int)ceil($r['mx'])];
}

/* Выборка товаров категории с фильтрами/сортировкой/страницей.
   Возвращает ['items' => [...], 'total' => N]. */
function vp_category_query(string $cat, array $get, string $sort, int $page): array {
	$db = vp_db();
	if (!$db) return ['items' => [], 'total' => 0];

	$config = vp_filter_config($cat);
	$where  = ['category = ?', 'active = 1'];
	$args   = [$cat];

	// Диапазон цены
	$pmin = isset($get['price_min']) && $get['price_min'] !== '' ? (float)$get['price_min'] : null;
	$pmax = isset($get['price_max']) && $get['price_max'] !== '' ? (float)$get['price_max'] : null;
	if ($pmin !== null) { $where[] = 'price >= ?'; $args[] = $pmin; }
	if ($pmax !== null) { $where[] = 'price <= ?'; $args[] = $pmax; }

	// Только в наличии
	if (!empty($get['in_stock'])) { $where[] = 'in_stock = 1'; }

	// Фасеты-чекбоксы (ИЛИ внутри фасета, И между фасетами)
	foreach ($config as $key => $cfg) {
		$sel = $get[$cfg['param']] ?? null;
		if (empty($sel)) continue;
		$sel = array_values((array)$sel);
		$ph  = implode(',', array_fill(0, count($sel), '?'));
		$where[] = "id IN (SELECT product_id FROM product_facets WHERE facet_key = ? AND facet_value IN ($ph))";
		$args[]  = $key;
		foreach ($sel as $v) $args[] = (string)$v;
	}

	$whereSql = implode(' AND ', $where);

	// Всего под фильтр
	$st = $db->prepare("SELECT COUNT(*) FROM products WHERE $whereSql");
	$st->execute($args);
	$total = (int)$st->fetchColumn();

	// Сортировка (имя — запасной ключ, без натуральной — некритично)
	if ($sort === 'price_asc') {
		$order = 'price ASC, name COLLATE NOCASE ASC';
	} elseif ($sort === 'price_desc') {
		$order = 'price DESC, name COLLATE NOCASE ASC';
	} else {
		$order = 'in_stock DESC, price ASC, name COLLATE NOCASE ASC';
	}

	$offset = max(0, ($page - 1) * VP_PER_PAGE);

	// Страница: where-параметры + LIMIT/OFFSET (целые — bindValue с типом)
	$st = $db->prepare("SELECT * FROM products WHERE $whereSql ORDER BY $order LIMIT ? OFFSET ?");
	$i = 1;
	foreach ($args as $a) $st->bindValue($i++, $a);
	$st->bindValue($i++, VP_PER_PAGE, PDO::PARAM_INT);
	$st->bindValue($i++, $offset, PDO::PARAM_INT);
	$st->execute();

	$items = array_map('vp_hydrate', $st->fetchAll());
	return ['items' => $items, 'total' => $total];
}

/* Популярные товары для главной (помечены галкой в админке) */
function vp_popular_products(int $limit = 8): array {
	$db = vp_db();
	if (!$db) return [];
	$st = $db->prepare('SELECT * FROM products WHERE active = 1 AND popular = 1 ORDER BY updated_at DESC LIMIT ?');
	$st->bindValue(1, $limit, PDO::PARAM_INT);
	$st->execute();
	return array_map('vp_hydrate', $st->fetchAll());
}

/* Поиск по каталогу: каждое слово запроса должно встретиться в названии или бренде.
   Ищем по нормализованному search_text (lowercase) — корректно для кириллицы. */
function vp_search_products(string $q, int $page, int $perPage = 24): array {
	$db = vp_db();
	if (!$db) return ['items' => [], 'total' => 0];

	$qLower = mb_strtolower(trim($q), 'UTF-8');
	$words  = preg_split('/\s+/u', $qLower, -1, PREG_SPLIT_NO_EMPTY);
	if (!$words) return ['items' => [], 'total' => 0];

	$where = ['active = 1'];
	$args  = [];
	foreach ($words as $w) {
		$where[] = 'search_text LIKE ?';
		$args[]  = '%' . $w . '%';
	}
	$wsql = implode(' AND ', $where);

	$st = $db->prepare("SELECT COUNT(*) FROM products WHERE $wsql");
	$st->execute($args);
	$total = (int)$st->fetchColumn();

	$offset = max(0, ($page - 1) * $perPage);
	$st = $db->prepare("SELECT * FROM products WHERE $wsql ORDER BY in_stock DESC, name COLLATE NOCASE ASC LIMIT ? OFFSET ?");
	$i = 1;
	foreach ($args as $a) $st->bindValue($i++, $a);
	$st->bindValue($i++, $perPage, PDO::PARAM_INT);
	$st->bindValue($i++, $offset, PDO::PARAM_INT);
	$st->execute();

	return ['items' => array_map('vp_hydrate', $st->fetchAll()), 'total' => $total];
}

/* Поток активных товаров для sitemap (slug/category/updated_at) */
function vp_sitemap_rows(): array {
	$db = vp_db();
	if (!$db) return [];
	return $db->query(
		"SELECT slug, category, updated_at FROM products
		 WHERE active = 1 AND slug <> '' AND category <> ''"
	)->fetchAll();
}

/* ===================================================
   Пересборка catalog.sqlite из products.json.
   Вызывается из админки после любой правки каталога.
   Пишем во временный файл и атомарно подменяем — чтобы
   не ловить полузаписанную базу на живых запросах.
   =================================================== */
function vp_rebuild_sqlite(): bool {
	$jsonPath = vp_products_path();
	$dbPath   = vp_sqlite_path();
	$tmpPath  = $dbPath . '.tmp';
	if (!is_file($jsonPath)) return false;

	$data = json_decode(file_get_contents($jsonPath), true);
	$list = $data['products'] ?? [];

	if (is_file($tmpPath)) @unlink($tmpPath);

	try {
		$db = new PDO('sqlite:' . $tmpPath, null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
		$db->exec('PRAGMA journal_mode = MEMORY');
		$db->exec('PRAGMA synchronous = OFF');
		$db->exec('
			CREATE TABLE products(
				id TEXT PRIMARY KEY, slug TEXT, name TEXT, category TEXT, brand TEXT, sku TEXT,
				price REAL, old_price REAL, unit TEXT, pack_area REAL,
				in_stock INTEGER, active INTEGER, image TEXT, images TEXT,
				description TEXT, specs TEXT, seo_title TEXT, seo_description TEXT, updated_at TEXT,
				popular INTEGER, search_text TEXT
			);
			CREATE INDEX idx_prod_slug  ON products(slug);
			CREATE INDEX idx_prod_cat   ON products(category, active);
			CREATE INDEX idx_prod_price ON products(price);
			CREATE INDEX idx_prod_popular ON products(popular, active);

			CREATE TABLE product_facets(
				product_id TEXT, category TEXT, active INTEGER, facet_key TEXT, facet_value TEXT
			);
			CREATE INDEX idx_facet_cat ON product_facets(category, facet_key, active);
			CREATE INDEX idx_facet_pid ON product_facets(product_id);
			CREATE INDEX idx_facet_val ON product_facets(facet_key, facet_value);
		');

		$insP = $db->prepare('INSERT OR REPLACE INTO products VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
		$insF = $db->prepare('INSERT INTO product_facets VALUES (?,?,?,?,?)');

		$db->beginTransaction();
		foreach ($list as $p) {
			$images = $p['images'] ?? [];
			$img    = $images[0] ?? '';
			$op     = $p['old_price'] ?? null;
			$op     = ($op !== null && $op !== '' && (float)$op > 0) ? (float)$op : null;
			$cat    = $p['category'] ?? '';
			$pid    = $p['id'] ?? '';
			$active = !empty($p['active']) ? 1 : 0;

			$insP->execute([
				$pid, $p['slug'] ?? '', $p['name'] ?? '', $cat, $p['brand'] ?? '', $p['sku'] ?? '',
				(float)($p['price'] ?? 0), $op, $p['unit'] ?? 'м²', (float)($p['pack_area'] ?? 0),
				!empty($p['in_stock']) ? 1 : 0, $active, $img,
				json_encode($images, JSON_UNESCAPED_UNICODE),
				$p['description'] ?? '', json_encode($p['specs'] ?? [], JSON_UNESCAPED_UNICODE),
				$p['seo_title'] ?? '', $p['seo_description'] ?? '', $p['updated_at'] ?? '',
				!empty($p['popular']) ? 1 : 0,
				mb_strtolower(trim(($p['name'] ?? '') . ' ' . ($p['brand'] ?? '')), 'UTF-8'),
			]);

			// Фасеты: бренд (для всех) + сконфигурированные spec-ключи категории
			$brand = trim($p['brand'] ?? '');
			if ($brand !== '') $insF->execute([$pid, $cat, $active, 'brand', $brand]);
			foreach (VP_FILTERS[$cat] ?? [] as $key => $cfg) {
				if ($key === 'brand') continue; // уже записан
				$v = trim((string)($p['specs'][$key] ?? ''));
				if ($v !== '') $insF->execute([$pid, $cat, $active, $key, $v]);
			}
		}
		$db->commit();
		$db = null;
	} catch (Throwable $e) {
		if (isset($db)) $db = null;
		@unlink($tmpPath);
		return false;
	}

	return @rename($tmpPath, $dbPath);
}

/* ===================================================
   Транслитерация в slug: кириллица → латиница
   =================================================== */
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
