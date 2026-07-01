# Вектор пола

Интернет-магазин напольных покрытий (`vectorpola.ru`). Витрина на **vanilla PHP/HTML/CSS/JS** без фреймворков и сборщиков, работает на shared-хостинге Beget. Каталог ~7800 товаров, источник истины — плоский JSON, витрина читает быстрый SQLite-кеш.

---

## Содержание

- [Стек и принципы](#стек-и-принципы)
- [Архитектура данных](#архитектура-данных)
- [Каталог](#каталог)
- [Структура проекта](#структура-проекта)
- [Роутинг](#роутинг)
- [Функционал витрины](#функционал-витрины)
- [Админка](#админка)
- [Формы и заявки](#формы-и-заявки)
- [Пайплайн картинок](#пайплайн-картинок)
- [Дизайн-система](#дизайн-система)
- [Локальный запуск](#локальный-запуск)

---

## Стек и принципы

- **PHP** (8.x) на shared-хостинге Beget. Расширения: `pdo_sqlite`, `mbstring`, `zip` (последнее — для XLSX-экспорта).
- **Vanilla** HTML/CSS/JS. Никаких фреймворков, npm, Composer, бандлеров, Cloudflare — сознательно.
- **Данные:** канонический `data/products.json` + производный `data/catalog.sqlite` (read-кеш).
- **Заявки:** Telegram-бот. **Подписки:** плоский JSON + выгрузка.
- Cache-busting ассетов вручную через `?v=N`.

Почему так: проект небольшой, хостинг дешёвый и без прав на демонов/крон-тюнинг, а отсутствие сборки означает «залил файл — работает». Скорость витрины вытянута архитектурой хранилища, а не инфраструктурой.

---

## Архитектура данных

Ключевая идея: **`products.json` — источник истины, `catalog.sqlite` — производный read-кеш**.

```
                    пишет                        читает (быстро)
   Админка  ─────────────────►  products.json  ◄─────────────  (пересборка)
                                      │                              │
                                      │  vp_rebuild_sqlite()         │
                                      ▼                              ▼
                                catalog.sqlite  ─────────────►  Витрина
                                 (21+1 колонка,                 (категория, карточка,
                                  фасеты, индексы)               поиск, sitemap, популярные, акции)
```

- **Витрина читает только SQLite** через PDO. Раньше на каждый запрос декодился JSON на 17.5 МБ — убрали.
- **Админка пишет в JSON**, после чего SQLite пересобирается **один раз за запрос** через `register_shutdown_function` (важно для импорта в цикле — иначе пересборка на каждую строку).
- **Атомарная пересборка:** пишем в `catalog.sqlite.tmp`, затем `rename`. При ошибке — `tmp` удаляется, старая база остаётся живой.
- **Устойчивость к рассинхрону схемы:** запросы к новым колонкам (`vp_promo_products`, `productsListPaged`) обёрнуты в try/catch. Если PHP залит раньше пересборки базы — витрина/админка не падают в 500, а деградируют мягко (пустой блок / json-фолбэк) до пересборки.
- Ручная пересборка: `/admin/rebuild-db.php` (за логином).

### Таблицы SQLite

- **`products`** (22 колонки). Флаги-булеаны: `active`, `in_stock`, `popular`, `promo`. `search_text` = `lower(name + brand)` для регистронезависимого поиска по кириллице. Индексы: `slug`, `(category, active)`, `price`, `(popular, active)`, `(promo, active)`.
- **`product_facets`** (`product_id, category, active, facet_key, facet_value`) — бренд + сконфигурированные спеки. Индексы по `(category, facet_key, active)`, `product_id`, `(facet_key, facet_value)`.

### Схема товара (`products.json`)

```json
{
  "id": "p_xxx", "slug": "...", "name": "", "sku": "",
  "category": "laminat", "brand": "",
  "price": 0, "old_price": null, "unit": "м²", "pack_area": 0,
  "in_stock": true, "active": true, "popular": false, "promo": false,
  "images": ["/uploads/products/..webp"], "specs": {},
  "seo_title": "", "seo_description": "", "description": "", "updated_at": "..."
}
```

### Ключевые функции (`source/php/catalog.php`)

`vp_db()`, `vp_hydrate()`, `vp_find_product()`, `vp_category_query()`, `vp_category_facets()`, `vp_category_price_bounds()`, `vp_category_count(s)()`, `vp_popular_products()`, `vp_promo_products()`, `vp_search_products()`, `vp_sitemap_rows()`, `vp_rebuild_sqlite()`, `vp_slugify()`.
URL-хелперы: `vp_product_url($p)` → `/catalog/{category}/{slug}/`, `vp_category_url($cat)` → `/catalog/{cat}/`.

---

## Каталог

~**7800 товаров** (порядка 7760 активных). Данные отнормализованы: нет `0 ₽`, нет дублей slug, бренды проставлены, ключи спеков канонизированы. Цифры плавают по мере наполнения — актуальные всегда в БД.

**9 категорий** (`VP_CATEGORIES`), из них 4 наполнены, 5 ведут на «Раздел наполняется»:

| Слаг | Название | Статус |
| --- | --- | --- |
| `laminat` | Ламинат | наполнена |
| `kvarcvinil` | Кварцвинил / SPC | наполнена |
| `parketnaya-doska` | Паркетная доска | наполнена |
| `inzhenernaya-doska` | Инженерная доска | наполнена |
| `vinil` | Виниловые полы | пусто |
| `massivnaya-doska` | Массивная доска | пусто |
| `probka` | Пробковые покрытия | пусто |
| `plintus-podlozhka` | Плинтусы и подложка | пусто |
| `soputstvuyushchie` | Сопутствующие товары | пусто |

**Фильтры (`VP_FILTERS`)** — сверены с реальными спеками:

- **laminat:** Бренд · Класс истираемости · Толщина
- **kvarcvinil:** Бренд · Класс истираемости · Толщина · Тип укладки
- **parketnaya-doska:** Бренд · Порода · Покрытие · Толщина
- **inzhenernaya-doska:** Бренд · Тип укладки · Покрытие · Толщина · Порода

Картинки товаров — `/uploads/products/*.webp`, приведены к единому виду **800×800**.

---

## Структура проекта

```
/
├── index.php              # Главная: популярные-карусель, акции-карусель, форма подписки, поиск, бренды
├── category.php           # Страница категории (SQLite, фасеты, пагинация, авто-сабмит фильтров)
├── product.php            # Карточка товара (SQLite, Offer-schema, галерея, калькулятор площади)
├── catalog-index.php      # /catalog/ — плитки категорий со счётчиками
├── search.php             # /search/?q= — поиск (noindex,follow)
├── cart.php               # /cart/ — корзина
├── sitemap.php            # Динамический sitemap из SQLite
├── og-image.jpg           # OG-превью (временное — долг)
├── .htaccess              # HTTPS(закомм. на тесте)+www, deny /data/, роутинг, gzip, expires
├── robots.txt             # Disallow /admin/ /source/php/ /data/ /search/ /cart/
│
├── about/ contacts/ delivery/ returns/ brands/   # внутренние страницы
│
├── data/
│   ├── products.json      # Источник истины (~17.5 МБ)
│   ├── catalog.sqlite     # Read-кеш (генерится, деплоить раньше PHP)
│   └── subscribers.json   # Подписки (создаётся при первой заявке)
│
├── source/
│   ├── css/     main.css · inner-pages.css · catalog.css · product.css · cart.css
│   ├── js/      main.js · cart.js · product.js
│   ├── include/ header.php · footer.php · metrika.html
│   ├── php/     catalog.php · config.php · send.php · subscribe.php
│   └── img/     hero · popular · no-image · logo · brands/*
│
├── uploads/products/      # ~7840 webp 800×800 (в .gitignore, заливаются rsync-ом)
│
└── admin/
    ├── products.php        # CRUD + пагинация/поиск + вкладки «Популярные» / «Акционные» / импорт / экспорт
    ├── subscribers.php     # Подписки: список, удаление, выгрузка CSV/XLSX
    ├── rebuild-db.php      # Ручная пересборка SQLite
    ├── dashboard.php · settings.php · help.php
    └── php/                auth · product-save · products-data · upload · import · export ·
                            xlsx-reader · xlsx-writer · layout-top · layout-bottom · admin-config
```

---

## Роутинг

Через `.htaccess` (`mod_rewrite`, все правила внутри `<IfModule mod_rewrite.c>`):

| URL | Файл |
| --- | --- |
| `/catalog/` | `catalog-index.php` |
| `/catalog/{cat}/` | `category.php?cat=` |
| `/catalog/{cat}/{slug}/` | `product.php?cat=&slug=` |
| `/cart/` | `cart.php` |
| `/search/` | `search.php` |
| `/sitemap.xml` | `sitemap.php` |

Внутренние страницы — по своим папкам (`/about/`, `/contacts/` и т.д.).

---

## Функционал витрины

- **Категория:** фасеты, диапазон цены, «в наличии», сортировка, пагинация. Авто-сабмит фильтров (десктоп — сразу, мобайл — по кнопке, цена — по Enter). Пагинация индексируема (self-canonical + rel prev/next), noindex на комбинациях фильтров.
- **Карточка:** галерея, калькулятор площади (типизируемый инпут с пересчётом), «в корзину», «купить в один клик», Offer-schema (`priceValidUntil` + `itemCondition`).
- **Поиск** (`/search/`): по названию + бренду, мульти-слово, пустой → подсказка, мусор → «не найдено», пагинация, `noindex,follow`.
- **Популярные:** галка в админке → колонка `popular` → карусель на главной (scroll-snap, стрелки гаснут на краях). Пусто → секция скрыта.
- **Акционные:** галка «Акционный» в админке → колонка `promo` → карусель на главной с перечёркнутой `old_price`. Пусто → секция скрыта.
- **Подписка на акции:** форма на главной (имя/email/телефон с маской и валидацией) → `subscribe.php` → `data/subscribers.json`.
- **Заглушка no-image:** два слоя — пустой `images` → фолбэк, и `onerror` на 404.

Карусели универсальны: JS вешается на любой `.products` внутри `.section` с `.arrows`.

---

## Админка

За логином (`admin/php/auth.php` → `requireLogin()`).

- **Каталог:** CRUD товаров, серверная пагинация/поиск, вкладки:
  - **Список товаров** — таблица с фильтром по категории.
  - **Популярные** / **Акционные** — товары с соответствующей галкой.
  - **Добавить / Редактировать** — форма товара с тумблерами: «Показывать на сайте» (`active`), «В наличии» (`in_stock`), «Популярный» (`popular`), «Акционный» (`promo`).
  - **Импорт / Экспорт** — XLSX (свой `xlsx-reader.php` / `xlsx-writer.php`, без внешних либ) + шаблон.
- **Подписки:** список подписчиков (новые сверху), удаление по email (POST + confirm + PRG-редирект), выгрузка **CSV** (BOM + `;`) и **XLSX**.
- **Настройки:** смена логина/пароля, email восстановления.
- **Пересборка базы:** `rebuild-db.php`.

После сохранения/импорта SQLite пересобирается автоматически (`register_shutdown_function`).

---

## Формы и заявки

- **`source/php/send.php`** — заявки (консультация, один клик, корзина, доставка) → **Telegram**. Раздельные connect/read таймауты (5 / 15 с), `error_log()` + фолбэк `data/leads-failed.log`, чтобы лид не потерялся при сбое API. Колбэк успеха изолирован от `try/catch`.
- **`source/php/subscribe.php`** — подписка на акции → `data/subscribers.json`. Валидация всех трёх полей, дедуп по email (тот же адрес не плодит — обновляет имя/тел/дату), запись под `flock`. Telegram не дёргает.
- **Клиент** (`main.js`): единая инициализация форм читает `data-endpoint` и `data-success`; маска телефона и валидация (`required`, `tel` на 11 цифр, формат `email`, `minlen`) переиспользуются.

Папка `/data/` закрыта в `.htaccess` и `robots.txt` — JSON с подписками наружу не светится.

---

## Пайплайн картинок

Локальные скрипты (Python/PIL, у владельца на машине, **не в репозитории**):

- **Товарные:** детект белых полей по bbox → обрезка → cover-заполнение **800×800 webp**. Параметр `MAX_UP` ограничивает апскейл (на текстуре дерева мыло незаметно).
- **Логотипы брендов:** SVG через `cairosvg` в высоком разрешении, PNG/JPG через PIL с обрезкой полей → **320×180 webp, белый фон, логотип вписан с воздухом**.
- Заливка на хост — rsync (без `-z`, без `--delete`).

Бренды (8, захардкожены в `index.php` и `brands/index.php` — при правках менять оба): Alpine Floor, AGT, Aqua Floor, KRONOTEX, MY Step, PRIMAVERA, EVERSENSE, Geometrika.

---

## Дизайн-система

- Токены в `:root` (`main.css`). Акцент **«медовый янтарь» `--accent: #F2A516`** (+ dark/soft/on-accent), тёмный `--dark: #2B2F38`, `--ink/--muted/--line/--bg/--bg-soft/--bg-cream`, радиусы `--radius: 10px` / `--radius-lg: 16px`, `--maxw: 1280px`.
- Брейкпоинты mobile-first: **600 / 768 / 900 (бургер) / 1024 / 1100 / 1280**.
- Кнопки фронта: `.btn` + `.btn--accent` / `.btn--outline`. В админке — `.btn-primary` / `.btn-secondary` / `.btn-danger`.
- **Правило гридов:** любой новый грид — `repeat(N, minmax(0, 1fr))`, не голый `1fr` (иначе горизонтальный скролл в Safari/iOS — колонка не ужимается ниже min-content содержимого).

---

## Локальный запуск

Нужен PHP с `pdo_sqlite`, `mbstring`, `zip`.

```bash
# из корня проекта
php -S localhost:8000
```

Открыть `http://localhost:8000/`. Для «красивых» URL каталога нужен Apache с `mod_rewrite` (встроенный сервер PHP правила `.htaccess` не применяет — обращаться к `category.php?cat=...` напрямую или поднять Apache).

Перед первым запуском убедиться, что `data/catalog.sqlite` актуальна схеме кода; при сомнении — пересобрать через `/admin/rebuild-db.php` или вызвать `vp_rebuild_sqlite()`.

### Конфигурация

`source/php/config.php` — доступы бота и константы. Значения не коммитить в паблик; заполнить своими:

```php
<?php
const TG_TOKEN   = 'YOUR_BOT_TOKEN';   // @BotFather
const TG_CHAT_ID = 'YOUR_CHAT_ID';
```

Доступы админки — `admin/php/admin-config.php`.

---

## Деплой

Shared-хостинг Beget, заливка по SSH/rsync (порт 22). Реальные логины/пути — вне репозитория.

---

*Проект коммерческий, ведётся соло*
