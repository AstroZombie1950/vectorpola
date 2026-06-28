<?php
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/products-data.php';
requireLogin();

$pageTitle  = 'Каталог';
$activePage = 'products';

$categories = productCategories();
$units      = productUnits();

// Список: поиск + фильтр по категории + пагинация (быстрое чтение из sqlite)
$listQ     = trim($_GET['q'] ?? '');
$listCat   = preg_replace('/[^a-z0-9-]/', '', $_GET['cat'] ?? '');
$listPage  = max(1, (int)($_GET['p'] ?? 1));
$listData  = productsListPaged($listQ, $listCat, $listPage, 50);
$products  = $listData['items'];
$listTotal = $listData['total'];
$listPages = $listData['pages'];
$listPage  = $listData['page'];
$listActive = ($listQ !== '' || $listCat !== '');   // активен ли поиск/фильтр

// Редактирование товара
$editProduct = null;
if (isset($_GET['edit'])) {
	$editProduct = productGet($_GET['edit']);
}

// Начальная вкладка
$defaultTab = isset($_GET['edit']) ? 'add' : ($_GET['tab'] ?? 'list');

include __DIR__ . '/php/layout-top.php';
?>

<div class="page-header">
	<h1>Каталог товаров</h1>
	<p class="page-subtitle">Управление ассортиментом магазина</p>
</div>

<!-- Спойлер-подсказка -->
<div class="spoiler">
	<button class="spoiler-toggle" aria-expanded="false">
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
		Как работать с каталогом
		<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto"><path d="M6 9l6 6 6-6"/></svg>
	</button>
	<div class="spoiler-body">
		<ol>
			<li><strong>Список товаров</strong> — просмотр, редактирование и удаление позиций.</li>
			<li><strong>Добавить товар</strong> — форма для добавления одного товара вручную.</li>
			<li><strong>Импорт</strong> — загрузка товаров из Excel (XLSX) или CSV. При совпадении артикула — товар обновится.</li>
			<li><strong>Экспорт / шаблон</strong> — скачать текущий каталог или пустой шаблон для заполнения.</li>
		</ol>
		<p style="margin-top:10px">Подробная инструкция — в разделе <a href="/admin/help.php" style="color:var(--accent-dark)">Инструкция</a>.</p>
	</div>
</div>

<!-- Табы -->
<div class="tabs">
	<button class="tab-btn <?= $defaultTab === 'list'   ? 'tab-btn--active' : '' ?>" data-tab="tab-list">Список товаров <span class="tab-count"><?= $listTotal ?></span></button>
	<button class="tab-btn <?= $defaultTab === 'add'    ? 'tab-btn--active' : '' ?>" data-tab="tab-add"><?= $editProduct ? 'Редактировать товар' : 'Добавить товар' ?></button>
	<button class="tab-btn <?= $defaultTab === 'import' ? 'tab-btn--active' : '' ?>" data-tab="tab-import">Импорт</button>
	<button class="tab-btn <?= $defaultTab === 'export' ? 'tab-btn--active' : '' ?>" data-tab="tab-export">Экспорт / шаблон</button>
</div>

<!-- ============ СПИСОК ============ -->
<div class="tab-pane <?= $defaultTab === 'list' ? 'tab-pane--active' : '' ?>" id="tab-list">

	<!-- Поиск и фильтр по категории -->
	<form method="get" action="/admin/products.php" class="products-search" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:16px">
		<input type="hidden" name="tab" value="list">
		<input type="text" name="q" value="<?= htmlspecialchars($listQ) ?>" placeholder="Поиск по названию или артикулу" style="flex:1;min-width:220px;padding:9px 12px;border:1px solid var(--line,#ddd);border-radius:8px">
		<select name="cat" style="padding:9px 12px;border:1px solid var(--line,#ddd);border-radius:8px">
			<option value="">Все категории</option>
			<?php foreach ($categories as $k => $v): ?>
			<option value="<?= htmlspecialchars($k) ?>" <?= $listCat === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
			<?php endforeach; ?>
		</select>
		<button type="submit" class="btn btn--accent">Найти</button>
		<?php if ($listActive): ?><a href="/admin/products.php?tab=list" class="btn btn--outline">Сбросить</a><?php endif; ?>
	</form>

	<?php if ($listTotal === 0): ?>
		<div class="empty-state">
			<p><?= $listActive ? 'По запросу ничего не найдено.' : 'Товаров пока нет. Добавьте первый товар вручную или загрузите через импорт.' ?></p>
		</div>
	<?php else: ?>
		<p class="text-muted" style="margin-bottom:10px">Найдено: <?= $listTotal ?> · страница <?= $listPage ?> из <?= $listPages ?></p>
		<div class="products-table-wrap">
			<table class="products-table" id="productsTable">
				<thead>
					<tr>
						<th style="width:56px"></th>
						<th>Название</th>
						<th>Артикул</th>
						<th>Категория</th>
						<th>Цена</th>
						<th>Статус</th>
						<th style="width:130px"></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $p): ?>
					<tr data-id="<?= htmlspecialchars($p['id']) ?>">
						<td class="td-img">
							<?php if (!empty($p['images'][0])): ?>
								<img src="<?= htmlspecialchars($p['images'][0]) ?>" alt="">
							<?php else: ?>
								<div class="no-img">нет фото</div>
							<?php endif; ?>
						</td>
						<td><?= htmlspecialchars($p['name']) ?></td>
						<td class="text-muted"><?= htmlspecialchars($p['sku']) ?></td>
						<td class="text-muted"><?= htmlspecialchars($categories[$p['category']] ?? $p['category']) ?></td>
						<td><?= $p['price'] ? number_format($p['price'], 0, '.', ' ') . ' ₽ / ' . htmlspecialchars($p['unit']) : '—' ?></td>
						<td>
							<span class="badge <?= $p['active'] ? 'badge--active' : 'badge--hidden' ?>">
								<?= $p['active'] ? 'Активен' : 'Скрыт' ?>
							</span>
						</td>
						<td>
							<div class="row-actions">
								<a href="/admin/products.php?edit=<?= $p['id'] ?>" class="btn-icon" title="Редактировать">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
								</a>
								<button class="btn-icon btn-icon--danger js-delete" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" title="Удалить">
									<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4h6v2"/></svg>
								</button>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ($listPages > 1):
			$qsBase = http_build_query(array_filter(['tab' => 'list', 'q' => $listQ, 'cat' => $listCat], fn($v) => $v !== '' && $v !== null));
		?>
		<nav class="admin-pagination" style="display:flex;gap:6px;flex-wrap:wrap;margin-top:16px;align-items:center">
			<?php if ($listPage > 1): ?>
				<a class="btn btn--outline" href="?<?= $qsBase ?>&p=<?= $listPage - 1 ?>">← Назад</a>
			<?php endif; ?>
			<?php for ($i = 1; $i <= $listPages; $i++):
				if ($i === 1 || $i === $listPages || abs($i - $listPage) <= 2): ?>
					<?php if ($i === $listPage): ?>
						<span class="btn btn--accent" style="pointer-events:none"><?= $i ?></span>
					<?php else: ?>
						<a class="btn btn--outline" href="?<?= $qsBase ?>&p=<?= $i ?>"><?= $i ?></a>
					<?php endif; ?>
				<?php elseif ($i === 2 || $i === $listPages - 1): ?>
					<span style="padding:0 4px">…</span>
				<?php endif; ?>
			<?php endfor; ?>
			<?php if ($listPage < $listPages): ?>
				<a class="btn btn--outline" href="?<?= $qsBase ?>&p=<?= $listPage + 1 ?>">Вперёд →</a>
			<?php endif; ?>
		</nav>
		<?php endif; ?>

	<?php endif; ?>
</div>

<!-- ============ ДОБАВИТЬ / РЕДАКТИРОВАТЬ ============ -->
<div class="tab-pane <?= $defaultTab === 'add' ? 'tab-pane--active' : '' ?>" id="tab-add">
	<div id="productFormAlert"></div>

	<form id="productForm" enctype="multipart/form-data">
		<input type="hidden" name="action" value="save">
		<input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? '') ?>">
		<input type="hidden" name="images" id="imagesInput" value="<?= htmlspecialchars(json_encode($editProduct['images'] ?? [])) ?>">
		<input type="hidden" name="specs"  id="specsInput"  value="<?= htmlspecialchars(json_encode($editProduct['specs']  ?? [])) ?>">

		<div class="form-cols">
			<!-- Левая колонка -->
			<div class="form-col">
				<div class="card">
					<h2 class="card-title">Основное</h2>

					<div class="field">
						<label>Название <span class="req">*</span></label>
						<input type="text" name="name" value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" required>
					</div>
					<div class="field">
						<label>URL-адрес (slug)</label>
						<input type="text" name="slug" value="<?= htmlspecialchars($editProduct['slug'] ?? '') ?>" placeholder="генерируется из названия">
						<p class="text-muted text-small" style="margin-top:4px">Латиница и дефисы. Пусто — создастся автоматически из названия. Участвует в ссылке товара.</p>
					</div>
					<div class="field">
						<label>Артикул</label>
						<input type="text" name="sku" value="<?= htmlspecialchars($editProduct['sku'] ?? '') ?>">
					</div>
					<div class="field">
						<label>Категория</label>
						<select name="category">
							<option value="">— не выбрана —</option>
							<?php foreach ($categories as $k => $v): ?>
								<option value="<?= $k ?>" <?= ($editProduct['category'] ?? '') === $k ? 'selected' : '' ?>>
									<?= htmlspecialchars($v) ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="field">
						<label>Бренд</label>
						<input type="text" name="brand" value="<?= htmlspecialchars($editProduct['brand'] ?? '') ?>">
					</div>
					<div class="field-row">
						<div class="field">
							<label>Цена, ₽ <span class="req">*</span></label>
							<input type="number" name="price" min="0" step="0.01" value="<?= htmlspecialchars($editProduct['price'] ?? '') ?>" required>
						</div>
						<div class="field">
							<label>Старая цена, ₽</label>
							<input type="number" name="old_price" min="0" step="0.01" value="<?= htmlspecialchars($editProduct['old_price'] ?? '') ?>" placeholder="если есть скидка">
						</div>
					</div>
					<div class="field-row">
						<div class="field">
							<label>м² в упаковке</label>
							<input type="number" name="pack_area" min="0" step="0.001" value="<?= htmlspecialchars($editProduct['pack_area'] ?? '') ?>" placeholder="напр. 2.22">
						</div>
						<div class="field" style="max-width:110px">
							<label>Ед. изм.</label>
							<select name="unit">
								<?php foreach ($units as $k => $v): ?>
									<option value="<?= $k ?>" <?= ($editProduct['unit'] ?? 'м²') === $k ? 'selected' : '' ?>><?= htmlspecialchars($v) ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="field">
						<label>Описание</label>
						<textarea name="description" rows="4"><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
					</div>

					<!-- Активен / в наличии -->
					<label class="toggle-label">
						<input type="checkbox" name="active" id="activeCheck" <?= !empty($editProduct['active']) ? 'checked' : '' ?>>
						<span class="toggle"></span>
						Показывать на сайте
					</label>
					<label class="toggle-label" style="margin-top:10px">
						<input type="checkbox" name="in_stock" id="inStockCheck" <?= !empty($editProduct['in_stock']) ? 'checked' : '' ?>>
						<span class="toggle"></span>
						В наличии на складе
					</label>
				</div>

				<!-- SEO -->
				<div class="card mt-16">
					<h2 class="card-title">SEO (необязательно)</h2>
					<p class="text-muted text-small" style="margin-bottom:12px">Пусто — соберётся автоматически из названия и описания.</p>
					<div class="field">
						<label>SEO title</label>
						<input type="text" name="seo_title" value="<?= htmlspecialchars($editProduct['seo_title'] ?? '') ?>" placeholder="Заголовок вкладки/выдачи">
					</div>
					<div class="field">
						<label>SEO description</label>
						<textarea name="seo_description" rows="3" placeholder="Краткое описание для поисковой выдачи"><?= htmlspecialchars($editProduct['seo_description'] ?? '') ?></textarea>
					</div>
				</div>
			</div>

			<!-- Правая колонка -->
			<div class="form-col">
				<!-- Изображения -->
				<div class="card">
					<h2 class="card-title">Изображения</h2>
					<div id="imagesList" class="images-list">
						<?php foreach ($editProduct['images'] ?? [] as $img): ?>
							<div class="image-item" data-url="<?= htmlspecialchars($img) ?>">
								<img src="<?= htmlspecialchars($img) ?>" alt="">
								<button type="button" class="image-remove" title="Удалить">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<div class="field mt-8">
						<label>Загрузить с компьютера</label>
						<input type="file" name="new_image" id="newImageFile" accept="image/*">
					</div>
					<p class="text-muted mt-4">Форматы: JPG, PNG, WebP. Фото сожмётся автоматически.</p>
				</div>

				<!-- Доп. характеристики -->
				<div class="card mt-16">
					<h2 class="card-title">Дополнительные характеристики</h2>
					<p class="text-muted text-small" style="margin-bottom:12px">Любые параметры: толщина, класс, цвет и т.д.</p>
					<div id="specsList">
						<?php foreach ($editProduct['specs'] ?? [] as $k => $v): ?>
							<div class="spec-row">
								<input type="text" class="spec-key"   value="<?= htmlspecialchars($k) ?>" placeholder="Название">
								<input type="text" class="spec-value" value="<?= htmlspecialchars($v) ?>" placeholder="Значение">
								<button type="button" class="spec-remove" title="Удалить">×</button>
							</div>
						<?php endforeach; ?>
					</div>
					<button type="button" class="btn-secondary mt-8" id="addSpec">+ Добавить характеристику</button>
				</div>
			</div>
		</div>

		<div class="form-footer">
			<button type="submit" class="btn-primary" id="saveBtn">
				<?= $editProduct ? 'Сохранить изменения' : 'Добавить товар' ?>
			</button>
			<?php if ($editProduct): ?>
				<a href="/admin/products.php" class="btn-secondary">Отмена</a>
			<?php endif; ?>
		</div>
	</form>
</div>

<!-- ============ ИМПОРТ ============ -->
<div class="tab-pane <?= $defaultTab === 'import' ? 'tab-pane--active' : '' ?>" id="tab-import">
	<div id="importAlert"></div>

	<div class="spoiler">
		<button class="spoiler-toggle" aria-expanded="false">
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
			Как загрузить файл
			<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-left:auto"><path d="M6 9l6 6 6-6"/></svg>
		</button>
		<div class="spoiler-body">
			<ol>
				<li>Скачайте шаблон на вкладке «Экспорт / шаблон».</li>
				<li>Заполните шаблон в Excel или Google Таблицах, начиная со второй строки.</li>
				<li>Не удаляйте и не переименовывайте первую строку с заголовками.</li>
				<li>Чтобы добавить новую характеристику — добавьте столбец с её названием правее последнего.</li>
				<li>В столбце «Изображение» укажите прямую ссылку https://... — фото скачается автоматически.</li>
				<li>Если товар с таким артикулом уже есть — он обновится. Нового артикула — создастся новый товар.</li>
				<li>Выберите файл ниже и нажмите «Загрузить».</li>
			</ol>
		</div>
	</div>

	<div class="format-grid mt-16">
		<!-- XLSX -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--xlsx">XLSX</span>
				<strong>Excel-файл</strong>
			</div>
			<p>Рекомендуемый формат. Открывается в Excel, Google Таблицах и LibreOffice. Поддерживает форматирование, цветовые подсказки и фиксированную шапку.</p>
			<form id="importFormXlsx">
				<input type="hidden" name="format" value="xlsx">
				<div class="field">
					<label>Выбрать XLSX-файл</label>
					<label class="file-drop" id="dropXlsx">
						<input type="file" name="file" accept=".xlsx" required id="fileXlsx">
						<span class="file-drop-icon">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
						</span>
						<span class="file-drop-text" id="textXlsx">Нажмите или перетащите файл</span>
					</label>
				</div>
				<button type="submit" class="btn-primary">Загрузить XLSX</button>
			</form>
		</div>

		<!-- CSV -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--csv">CSV</span>
				<strong>CSV-файл</strong>
			</div>
			<p>Простой текстовый формат. Разделитель — точка с запятой (;). При открытии в Excel укажите кодировку UTF-8. Подходит для простых выгрузок без форматирования.</p>
			<form id="importFormCsv">
				<input type="hidden" name="format" value="csv">
				<div class="field">
					<label>Выбрать CSV-файл</label>
					<label class="file-drop" id="dropCsv">
						<input type="file" name="file" accept=".csv,.txt" required id="fileCsv">
						<span class="file-drop-icon">
							<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
						</span>
						<span class="file-drop-text" id="textCsv">Нажмите или перетащите файл</span>
					</label>
				</div>
				<button type="submit" class="btn-primary">Загрузить CSV</button>
			</form>
		</div>
	</div>
</div>

<!-- ============ ЭКСПОРТ ============ -->
<div class="tab-pane <?= $defaultTab === 'export' ? 'tab-pane--active' : '' ?>" id="tab-export">
	<div class="format-grid">
		<!-- Шаблон XLSX -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--xlsx">XLSX</span>
				<strong>Пустой шаблон</strong>
			</div>
			<p>Скачайте шаблон с заголовками и одной строкой-примером. Заполните и загрузите через Импорт. Фиксированные столбцы выделены бежевым, дополнительные характеристики — зелёным.</p>
			<a href="/admin/php/export.php?format=xlsx&mode=template" class="btn-primary">Скачать шаблон XLSX</a>
		</div>

		<!-- Шаблон CSV -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--csv">CSV</span>
				<strong>Пустой шаблон</strong>
			</div>
			<p>То же самое в формате CSV. Разделитель — точка с запятой. Подходит, если нет Excel.</p>
			<a href="/admin/php/export.php?format=csv&mode=template" class="btn-primary">Скачать шаблон CSV</a>
		</div>

		<?php if (!empty($products)): ?>
		<!-- Экспорт XLSX -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--xlsx">XLSX</span>
				<strong>Текущий каталог</strong>
			</div>
			<p>Выгрузить все <?= count($products) ?> товаров в Excel для редактирования. Отредактируйте и загрузите обратно через Импорт.</p>
			<a href="/admin/php/export.php?format=xlsx&mode=export" class="btn-primary">Скачать каталог XLSX</a>
		</div>

		<!-- Экспорт CSV -->
		<div class="format-card">
			<div class="format-card-head">
				<span class="format-badge format-badge--csv">CSV</span>
				<strong>Текущий каталог</strong>
			</div>
			<p>Выгрузить все <?= count($products) ?> товаров в CSV.</p>
			<a href="/admin/php/export.php?format=csv&mode=export" class="btn-primary">Скачать каталог CSV</a>
		</div>
		<?php endif; ?>
	</div>
</div>

<!-- ============ Стили страницы ============ -->
<style>
/* Счётчик на табе */
.tab-count { background: var(--bg-cream); color: var(--muted); font-size: 11px; padding: 1px 6px; border-radius: 10px; margin-left: 4px; }

/* Действия в строке таблицы */
.row-actions { display: flex; gap: 4px; }
.btn-icon {
	width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;
	border-radius: 6px; border: 1.5px solid var(--line); background: var(--bg);
	cursor: pointer; color: var(--muted); transition: .15s;
}
.btn-icon:hover { border-color: var(--accent); color: var(--accent-dark); }
.btn-icon--danger:hover { border-color: var(--danger); color: var(--danger); }

/* Форма товара */
.form-cols { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
.form-footer { margin-top: 20px; display: flex; gap: 12px; }
.field-row { display: flex; gap: 12px; }
.field-row .field { flex: 1; }
.req { color: var(--danger); }

/* Тогл */
.toggle-label { display: flex; align-items: center; gap: 10px; cursor: pointer; user-select: none; font-size: 14px; }
.toggle-label input { display: none; }
.toggle { width: 40px; height: 22px; border-radius: 11px; background: var(--line); position: relative; transition: background .2s; flex-shrink: 0; }
.toggle::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; transition: left .2s; }
.toggle-label input:checked + .toggle { background: var(--accent); }
.toggle-label input:checked + .toggle::after { left: 21px; }

/* Изображения */
.images-list { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
.image-item { position: relative; width: 80px; height: 80px; }
.image-item img { width: 100%; height: 100%; object-fit: cover; border-radius: 6px; border: 1px solid var(--line); }
.image-remove {
	position: absolute; top: -6px; right: -6px;
	width: 20px; height: 20px; border-radius: 50%;
	background: var(--danger); color: #fff;
	border: none; cursor: pointer; font-size: 14px;
	display: flex; align-items: center; justify-content: center; line-height: 1;
}

/* Характеристики */
.spec-row { display: flex; gap: 8px; margin-bottom: 8px; align-items: center; }
.spec-row input { flex: 1; padding: 8px 10px; border: 1.5px solid var(--line); border-radius: var(--radius); }
.spec-row input:focus { outline: none; border-color: var(--accent); }
.spec-remove { background: none; border: none; font-size: 20px; cursor: pointer; color: var(--muted); padding: 0 4px; }
.spec-remove:hover { color: var(--danger); }

/* Пустое состояние */
.empty-state { padding: 40px; text-align: center; color: var(--muted); background: var(--bg); border-radius: var(--radius-lg); border: 1.5px dashed var(--line); }

@media (max-width: 768px) {
	.form-cols { grid-template-columns: 1fr; }
}
</style>

<!-- ============ JS страницы ============ -->
<script>
(function () {

/* ===== Удаление товара ===== */
document.querySelectorAll('.js-delete').forEach(btn => {
	btn.addEventListener('click', async () => {
		const id   = btn.dataset.id;
		const name = btn.dataset.name;
		if (!confirm(`Удалить товар «${name}»? Действие необратимо.`)) return;

		const fd = new FormData();
		fd.append('action', 'delete');
		fd.append('id', id);

		const r = await fetch('/admin/php/product-save.php', { method: 'POST', body: fd });
		const d = await r.json();
		if (d.ok) {
			btn.closest('tr').remove();
		} else {
			alert('Ошибка: ' + d.error);
		}
	});
});

/* ===== Характеристики ===== */
function syncSpecs() {
	const rows  = document.querySelectorAll('.spec-row');
	const specs = {};
	rows.forEach(row => {
		const k = row.querySelector('.spec-key').value.trim();
		const v = row.querySelector('.spec-value').value.trim();
		if (k) specs[k] = v;
	});
	document.getElementById('specsInput').value = JSON.stringify(specs);
}

function addSpecRow(key = '', value = '') {
	const row = document.createElement('div');
	row.className = 'spec-row';
	row.innerHTML = `
		<input type="text" class="spec-key"   value="${escHtml(key)}"   placeholder="Название" oninput="syncSpecs()">
		<input type="text" class="spec-value" value="${escHtml(value)}" placeholder="Значение" oninput="syncSpecs()">
		<button type="button" class="spec-remove" title="Удалить">×</button>
	`;
	row.querySelector('.spec-remove').addEventListener('click', () => { row.remove(); syncSpecs(); });
	document.getElementById('specsList').appendChild(row);
}

// Навешиваем удаление на существующие строки
document.querySelectorAll('.spec-row .spec-remove').forEach(btn => {
	btn.addEventListener('click', () => { btn.closest('.spec-row').remove(); syncSpecs(); });
});
document.querySelectorAll('.spec-row input').forEach(inp => inp.addEventListener('input', syncSpecs));

document.getElementById('addSpec')?.addEventListener('click', () => addSpecRow());

/* ===== Изображения ===== */
function getImages() {
	return JSON.parse(document.getElementById('imagesInput').value || '[]');
}
function setImages(arr) {
	document.getElementById('imagesInput').value = JSON.stringify(arr);
}

function renderImages() {
	const list = document.getElementById('imagesList');
	list.innerHTML = '';
	getImages().forEach(url => {
		const div = document.createElement('div');
		div.className = 'image-item';
		div.dataset.url = url;
		div.innerHTML = `<img src="${escHtml(url)}" alt=""><button type="button" class="image-remove" title="Удалить">×</button>`;
		div.querySelector('.image-remove').addEventListener('click', () => removeImage(url));
		list.appendChild(div);
	});
}

function removeImage(url) {
	setImages(getImages().filter(u => u !== url));
	renderImages();
}

// Предзагрузка файла (preview без отправки — отправляется вместе с формой)
document.getElementById('newImageFile')?.addEventListener('change', function () {
	if (!this.files[0]) return;
	const reader = new FileReader();
	reader.onload = e => {
		// Временный превью
		const list = document.getElementById('imagesList');
		const preview = document.createElement('div');
		preview.className = 'image-item';
		preview.id = 'newImagePreview';
		preview.innerHTML = `<img src="${e.target.result}" alt=""><span style="position:absolute;bottom:2px;left:2px;font-size:10px;background:rgba(0,0,0,.5);color:#fff;padding:1px 4px;border-radius:3px">новое</span>`;
		const old = document.getElementById('newImagePreview');
		if (old) old.remove();
		list.appendChild(preview);
	};
	reader.readAsDataURL(this.files[0]);
});

/* ===== Сохранение формы товара ===== */
document.getElementById('productForm')?.addEventListener('submit', async function (e) {
	e.preventDefault();
	syncSpecs();

	const btn     = document.getElementById('saveBtn');
	const alertEl = document.getElementById('productFormAlert');
	btn.disabled  = true;
	btn.textContent = 'Сохранение...';

	const fd = new FormData(this);
	const r  = await fetch('/admin/php/product-save.php', { method: 'POST', body: fd });
	const d  = await r.json();

	btn.disabled = false;
	btn.textContent = this.querySelector('[name="id"]').value ? 'Сохранить изменения' : 'Добавить товар';

	if (d.ok) {
		alertEl.innerHTML = '<div class="alert alert--success">Товар сохранён</div>';
		if (!this.querySelector('[name="id"]').value) {
			this.reset();
			document.getElementById('imagesList').innerHTML = '';
			document.getElementById('specsList').innerHTML = '';
			document.getElementById('imagesInput').value = '[]';
			document.getElementById('specsInput').value = '{}';
		} else {
			window.location.href = '/admin/products.php';
		}
	} else {
		alertEl.innerHTML = `<div class="alert alert--error">${d.error || 'Ошибка сохранения'}</div>`;
	}

	alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
});

/* ===== Импорт XLSX ===== */
['importFormXlsx', 'importFormCsv'].forEach(formId => {
	document.getElementById(formId)?.addEventListener('submit', async function (e) {
		e.preventDefault();
		const btn     = this.querySelector('button[type="submit"]');
		const alertEl = document.getElementById('importAlert');
		btn.disabled  = true;
		btn.textContent = 'Загрузка...';

		const fd = new FormData(this);
		const r  = await fetch('/admin/php/import.php', { method: 'POST', body: fd });
		const d  = await r.json();

		btn.disabled = false;
		btn.textContent = formId.includes('Xlsx') ? 'Загрузить XLSX' : 'Загрузить CSV';

		if (d.ok) {
			let msg = `Готово: добавлено ${d.created}, обновлено ${d.updated}.`;
			if (d.errors.length) msg += ' Предупреждения: ' + d.errors.join('; ');
			alertEl.innerHTML = `<div class="alert alert--success">${msg}</div>`;
		} else {
			alertEl.innerHTML = `<div class="alert alert--error">${d.error || 'Ошибка импорта'}</div>`;
		}
		alertEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
	});
});

/* ===== Утилиты ===== */
function escHtml(s) {
	return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// File drop — обновляем вид при выборе файла
function initFileDrop(inputId, dropId, textId) {
	const input = document.getElementById(inputId);
	const drop  = document.getElementById(dropId);
	const text  = document.getElementById(textId);
	if (!input) return;

	input.addEventListener('change', () => {
		const file = input.files && input.files[0];
		if (file) {
			drop.classList.add('has-file');
			text.textContent = file.name;
		} else {
			drop.classList.remove('has-file');
			text.textContent = 'Нажмите или перетащите файл';
		}
	});
}

initFileDrop('fileXlsx', 'dropXlsx', 'textXlsx');
initFileDrop('fileCsv',  'dropCsv',  'textCsv');

})();
</script>

<?php include __DIR__ . '/php/layout-bottom.php'; ?>