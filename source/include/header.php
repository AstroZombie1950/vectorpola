<!-- ============ ШАПКА: верхняя строка ============ -->
<div class="topbar">
	<div class="container">
		<nav aria-label="Дополнительное меню">
			<ul>
				<li><a href="/brands/">Бренды</a></li>
				<li><a href="/delivery/">Доставка и оплата</a></li>
				<li><a href="/returns/">Возврат и гарантия</a></li>
				<li><a href="/about/">О компании</a></li>
				<li><a href="/contacts/">Контакты</a></li>
			</ul>
		</nav>
		<div class="addr">
			<span>Москва, Волоколамское шоссе 71/13</span>
			<span>Красногорск, Ильинское шоссе 1А</span>
		</div>
	</div>
</div>

<!-- ============ ШАПКА: основной ряд ============ -->
<header class="header">
	<div class="container">
		<!-- Логотип -->
		<a href="/" class="logo logo--badge" aria-label="Вектор пола — главная">
			<img src="/source/img/logo.webp" alt="Вектор пола" width="830" height="440">
		</a>

		<!-- Мобильные иконки: WA + корзина (только ≤900px) -->
		<div class="mob-icons">
			<a href="https://wa.me/79258211744" target="_blank" rel="noopener" class="mob-icon" aria-label="Написать в WhatsApp">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.05 4.91A9.82 9.82 0 0012.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 004.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.02zm-7.01 15.16a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 012.41 5.82c0 4.54-3.7 8.23-8.23 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29z"/></svg>
			</a>
			<a href="#" class="mob-icon" aria-label="Корзина">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.6"/><circle cx="20" cy="21" r="1.6"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
				<span class="count">0</span>
			</a>
		</div>

		<!-- Бургер -->
		<button class="mobile-burger" id="mobileBurger" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobileMenu">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
		</button>

		<!-- Кнопка каталога (десктоп) -->
		<a href="#catalog" class="catalog-btn" aria-label="Каталог покрытий">
			<span class="burger"><i></i><i></i><i></i></span>
			<span>Каталог</span>
		</a>

		<!-- Поиск -->
		<form class="search" role="search" onsubmit="return false">
			<input type="search" placeholder="Поиск напольных покрытий" aria-label="Поиск по сайту">
			<button type="button" aria-label="Найти">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
			</button>
		</form>

		<!-- Телефон -->
		<div class="contacts">
			<a href="tel:+79258211744" class="phone">+7 (925) 821-17-44</a>
			<div class="hours">Ежедневно с 10:00 до 20:00</div>
		</div>

		<!-- WhatsApp + e-mail (десктоп) -->
		<div class="hicons">
			<a href="https://wa.me/79258211744" target="_blank" rel="noopener" class="hicon" aria-label="Написать в WhatsApp">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M19.05 4.91A9.82 9.82 0 0012.04 2C6.58 2 2.13 6.45 2.13 11.91c0 1.75.46 3.45 1.32 4.95L2 22l5.25-1.38a9.9 9.9 0 004.78 1.22h.01c5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.02zm-7.01 15.16a8.2 8.2 0 01-4.18-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.18 8.18 0 01-1.26-4.38c0-4.54 3.7-8.23 8.24-8.23 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 012.41 5.82c0 4.54-3.7 8.23-8.23 8.23zm4.52-6.16c-.25-.12-1.47-.72-1.69-.81-.23-.08-.39-.12-.56.12-.17.25-.64.81-.79.97-.14.17-.29.19-.54.06-.25-.12-1.05-.39-1.99-1.23-.74-.66-1.23-1.47-1.38-1.72-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.12-.14.17-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.56-1.34-.76-1.84-.2-.48-.4-.42-.56-.43h-.48c-.17 0-.43.06-.66.31-.23.25-.86.85-.86 2.07 0 1.22.89 2.4 1.01 2.56.12.17 1.75 2.67 4.23 3.74.59.26 1.05.41 1.41.52.59.19 1.13.16 1.56.1.48-.07 1.47-.6 1.68-1.18.21-.58.21-1.07.14-1.18-.06-.11-.22-.17-.47-.29z"/></svg>
			</a>
			<a href="mailto:zakaz@vectorpola.ru" class="hicon" aria-label="Написать на e-mail">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/></svg>
			</a>
		</div>

		<!-- Кнопка расчёта -->
		<a href="#final" class="btn btn--accent header-cta">Получить расчёт</a>

		<!-- Корзина (десктоп) -->
		<a href="#" class="cart" aria-label="Корзина">
			<span class="ico">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.6"/><circle cx="20" cy="21" r="1.6"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
				<span class="count">0</span>
			</span>
		</a>
	</div>
</header>

<!-- ============ МОБИЛЬНОЕ МЕНЮ ============ -->
<div class="mobile-menu" id="mobileMenu" aria-hidden="true" role="dialog" aria-label="Меню сайта">
	<div class="mm-head">
		<a href="/" class="logo logo--badge" aria-label="Вектор пола — главная">
			<img src="/source/img/logo.webp" alt="Вектор пола" width="830" height="440">
		</a>
		<button class="mm-close" aria-label="Закрыть меню">
			<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 6l12 12M18 6L6 18"/></svg>
		</button>
	</div>

	<div class="mm-body">
		<form class="mm-search" role="search" onsubmit="return false">
			<input type="search" placeholder="Поиск напольных покрытий" aria-label="Поиск по сайту">
			<button type="button" class="btn btn--accent" aria-label="Найти">
				<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
			</button>
		</form>

		<div class="mm-actions">
			<a href="#final" class="btn btn--accent">Получить расчёт</a>
			<a href="#" class="btn btn--outline">Корзина (0)</a>
		</div>

		<div>
			<div class="mm-section">Каталог</div>
			<nav class="mm-nav" aria-label="Каталог">
				<a href="#">Ламинат</a>
				<a href="#">Кварцвинил / SPC</a>
				<a href="#">Виниловые полы</a>
				<a href="#">Инженерная доска</a>
				<a href="#">Паркетная доска</a>
				<a href="#">Массивная доска</a>
				<a href="#">Пробковые покрытия</a>
				<a href="#">Плинтусы и подложка</a>
				<a href="#">Сопутствующие товары</a>
				<a href="#catalog">Перейти в каталог</a>
			</nav>
		</div>

		<div>
			<div class="mm-section">Информация</div>
			<nav class="mm-nav" aria-label="Информация">
				<a href="/brands/">Бренды</a>
				<a href="/delivery/">Доставка и оплата</a>
				<a href="/returns/">Возврат и гарантия</a>
				<a href="/about/">О компании</a>
				<a href="/contacts/">Контакты</a>
			</nav>
		</div>

		<div class="mm-contacts">
			<a href="tel:+79258211744" class="phone">+7 (925) 821-17-44</a>
			<a href="https://wa.me/79258211744" target="_blank" rel="noopener">Написать в WhatsApp</a>
			<a href="mailto:zakaz@vectorpola.ru">zakaz@vectorpola.ru</a>
			<span class="mm-addr">Ежедневно с 10:00 до 20:00</span>
		</div>

		<div>
			<div class="mm-section">Салоны</div>
			<div class="mm-addr">
				Москва, Волоколамское шоссе 71/13, к. 1, пом. 30Н<br>
				Красногорск, Ильинское шоссе, дом 1А, пом. 6
			</div>
		</div>
	</div>
</div>