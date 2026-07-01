/* ===== Год в футере ===== */
const yearEl = document.getElementById('currentYear');
if (yearEl) yearEl.textContent = new Date().getFullYear();

/* ===== Кнопка «наверх» ===== */
const toTop = document.getElementById('toTop');
if (toTop) {
	window.addEventListener('scroll', () => {
		toTop.classList.toggle('show', window.scrollY > 400);
	});
	toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
}

/* ===== Якорные ссылки со сдвигом на высоту шапки ===== */
document.addEventListener('click', e => {
	const link = e.target.closest('a[href^="#"]');
	if (!link) return;
	const id = link.getAttribute('href').slice(1);
	if (!id) return;
	const target = document.getElementById(id);
	if (!target) return;
	e.preventDefault();
	const header = document.querySelector('.header');
	const offset = header ? header.offsetHeight + 4 : 72;
	const top = target.getBoundingClientRect().top + window.scrollY - offset;
	window.scrollTo({ top, behavior: 'smooth' });
});

/* ===== FAQ: плавное открытие и закрытие ===== */
document.querySelectorAll('.faq details').forEach(details => {
	const summary = details.querySelector('summary');
	const ans     = details.querySelector('.ans');
	const ansIn   = details.querySelector('.ans-in');
	if (!summary || !ans || !ansIn) return;

	let animating = false; // защита от кликов во время анимации

	summary.addEventListener('click', e => {
		e.preventDefault();          // открытием/закрытием управляем сами
		if (animating) return;
		animating = true;

		if (details.open) {
			/* Закрытие: от текущей высоты к нулю, потом снимаем open */
			ans.style.height = ans.offsetHeight + 'px';
			requestAnimationFrame(() => { ans.style.height = '0'; });
			ans.addEventListener('transitionend', function done() {
				details.open = false;
				ans.style.height = '';
				animating = false;
			}, { once: true });
		} else {
			/* Открытие: open сразу, затем анимируем высоту от 0 к контенту */
			details.open = true;
			ans.style.height = '0';
			requestAnimationFrame(() => { ans.style.height = ansIn.offsetHeight + 'px'; });
			ans.addEventListener('transitionend', function done() {
				ans.style.height = 'auto'; // чтобы контент не обрезался при ресайзе
				animating = false;
			}, { once: true });
		}
	});
});

/* ===== Мобильное меню ===== */
const burger = document.getElementById('mobileBurger');
const menu   = document.getElementById('mobileMenu');
const close  = menu?.querySelector('.mm-close');

/* Видимые фокусируемые элементы внутри меню */
function getFocusable() {
	if (!menu) return [];
	const sel = 'a[href], button, input, select, textarea, [tabindex]:not([tabindex="-1"])';
	return [...menu.querySelectorAll(sel)].filter(el => !el.disabled && el.getClientRects().length);
}

function openMenu() {
	menu.classList.add('open');
	menu.setAttribute('aria-hidden', 'false');
	if (burger) burger.setAttribute('aria-expanded', 'true');
	document.body.classList.add('menu-open');
	(close || getFocusable()[0])?.focus(); // переносим фокус внутрь меню
}
function closeMenu() {
	if (!menu.classList.contains('open')) return; // уже закрыто
	menu.classList.remove('open');
	menu.setAttribute('aria-hidden', 'true');
	if (burger) burger.setAttribute('aria-expanded', 'false');
	document.body.classList.remove('menu-open');
	if (burger) burger.focus(); // возвращаем фокус на бургер
}

if (burger) burger.addEventListener('click', openMenu);
if (close)  close.addEventListener('click', closeMenu);

/* Закрытие по клику на ссылку внутри меню */
menu?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));

/* Escape + запирание фокуса (Tab) внутри открытого меню */
document.addEventListener('keydown', e => {
	if (!menu || !menu.classList.contains('open')) return;

	if (e.key === 'Escape') { closeMenu(); return; }

	if (e.key === 'Tab') {
		const items = getFocusable();
		if (!items.length) return;
		const first = items[0];
		const last  = items[items.length - 1];

		/* Зацикливаем фокус: с последнего → на первый и наоборот */
		if (e.shiftKey && document.activeElement === first) {
			e.preventDefault(); last.focus();
		} else if (!e.shiftKey && document.activeElement === last) {
			e.preventDefault(); first.focus();
		}
	}
});

/* Закрытие при расширении окна выше мобильной точки */
window.addEventListener('resize', () => { if (window.innerWidth > 900) closeMenu(); });

/* ===== Телефонная маска ===== */
function applyPhoneMask(input) {
	input.addEventListener('input', function () {
		let val = this.value.replace(/\D/g, '');
		if (val.startsWith('8')) val = '7' + val.slice(1);
		if (!val.startsWith('7') && val.length > 0) val = '7' + val;
		val = val.slice(0, 11);

		let out = '';
		if (val.length > 0)  out = '+7';
		if (val.length > 1)  out += ' (' + val.slice(1, 4);
		if (val.length >= 4) out += ') ' + val.slice(4, 7);
		if (val.length >= 7) out += '-' + val.slice(7, 9);
		if (val.length >= 9) out += '-' + val.slice(9, 11);
		this.value = out;
	});
	input.addEventListener('focus', function () {
		if (this.value === '') this.value = '+7 ';
	});
	input.addEventListener('blur', function () {
		if (this.value.trim() === '+7') this.value = '';
	});
}

/* ===== Валидация одного поля ===== */
function validateField(input) {
	const errorEl = input.parentElement.querySelector('.field-error');
	let msg = '';

	const val = input.value.trim();

	if (input.dataset.required !== undefined && val === '') {
		msg = input.dataset.errorEmpty || 'Заполните поле';
	} else if (input.type === 'tel') {
		const digits = input.value.replace(/\D/g, '');
		if (digits.length < 11) msg = 'Введите полный номер телефона';
	} else if (input.type === 'email' && val !== '') {
		/* Простая проверка формата email */
		if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) msg = 'Введите корректный e-mail';
	} else if (input.dataset.minlen) {
		if (val.length < +input.dataset.minlen) {
			msg = input.dataset.errorShort || 'Слишком коротко';
		}
	}

	input.classList.toggle('error', msg !== '');
	if (errorEl) errorEl.textContent = msg;
	return msg === '';
}

/* ===== Инициализация CTA-формы ===== */
function initCtaForm(formEl) {
	if (!formEl) return;

	/* Эндпоинт и текст успеха можно переопределить data-атрибутами
	   (форма подписки шлёт на subscribe.php со своим сообщением). */
	const endpoint = formEl.dataset.endpoint || '/source/php/send.php';
	const okMsg    = formEl.dataset.success  || 'Заявка отправлена! Мы перезвоним вам.';

	const phoneInput = formEl.querySelector('input[type="tel"]');
	if (phoneInput) applyPhoneMask(phoneInput);

	/* Валидация по blur (скрытые поля не трогаем) */
	formEl.querySelectorAll('input:not([type=hidden]), textarea').forEach(field => {
		field.addEventListener('blur', () => validateField(field));
	});

	const btn    = formEl.querySelector('button[type="button"]');
	const status = formEl.querySelector('.form-status');

	btn.addEventListener('click', async () => {
		const fields = [...formEl.querySelectorAll('input:not([type=hidden]), textarea')];
		const valid  = fields.map(validateField).every(Boolean);
		if (!valid) return;

		btn.disabled = true;
		const origText = btn.textContent;
		btn.textContent = 'Отправляем…';
		if (status) { status.textContent = ''; status.className = 'form-status'; }

		try {
			const res  = await fetch(endpoint, { method: 'POST', body: new FormData(formEl) });
			const json = await res.json();
			if (json.ok) {
				if (status) { status.textContent = okMsg; status.classList.add('ok'); }
				formEl.reset();
			} else {
				const msg = json.errors ? json.errors.join(', ') : 'Ошибка. Попробуйте ещё раз.';
				if (status) { status.textContent = msg; status.classList.add('err'); }
			}
		} catch {
			if (status) { status.textContent = 'Ошибка соединения. Попробуйте ещё раз.'; status.classList.add('err'); }
		} finally {
			btn.disabled = false;
			btn.textContent = origText;
		}
	});
}

document.querySelectorAll('.cta-form, .subscribe-form').forEach(initCtaForm);

/* ===== Каталог категории: фильтры (моб. шторка) + сортировка ===== */
(function () {
	const panel   = document.getElementById('filtersPanel');
	const toggle  = document.getElementById('filtersToggle');
	const overlay = document.getElementById('filtersOverlay');
	const closeBtn = panel ? panel.querySelector('.filters__close') : null;
	if (!panel) return; // не на странице категории

	function openFilters() {
		panel.classList.add('is-open');
		if (overlay) overlay.hidden = false;
		document.body.style.overflow = 'hidden';
	}
	function closeFilters() {
		panel.classList.remove('is-open');
		if (overlay) overlay.hidden = true;
		document.body.style.overflow = '';
	}

	toggle?.addEventListener('click', openFilters);
	closeBtn?.addEventListener('click', closeFilters);
	overlay?.addEventListener('click', closeFilters);
	document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFilters(); });
	// при возврате на десктоп — сбрасываем мобильное состояние
	window.addEventListener('resize', () => { if (window.innerWidth >= 1024) closeFilters(); });

	// Сортировка — отправляем форму сразу при выборе
	const sortSelect = document.getElementById('sortSelect');
	const form = document.getElementById('catalogFilters');
	sortSelect?.addEventListener('change', () => form?.submit());

	// Авто-сабмит фасетов/наличия: на десктопе — сразу, на моб. шторке — ждём «Показать»
	const isDesktop = () => window.innerWidth >= 1024;
	panel.querySelectorAll('input[type="checkbox"]').forEach(cb => {
		cb.addEventListener('change', () => { if (isDesktop()) form?.submit(); });
	});
	// Цена — сабмит по Enter в любом режиме
	panel.querySelectorAll('input[name="price_min"], input[name="price_max"]').forEach(inp => {
		inp.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); form?.submit(); } });
	});
})();


/* ===== Бейдж корзины (на всех страницах; полная логика — в cart.js) ===== */
(function cartBadge() {
	let n = 0;
	try { n = (JSON.parse(localStorage.getItem('vp_cart')) || []).length; } catch {}
	document.querySelectorAll('.cart .count, .mob-icon .count').forEach(el => { el.textContent = n; });
	document.querySelectorAll('.js-cart-link').forEach(el => { el.textContent = 'Корзина (' + n + ')'; });
})();

/* ===== Карусель «Популярные продукты» (горизонтальный скролл стрелками) ===== */
(function () {
	document.querySelectorAll('.products').forEach(track => {
		const arrows = track.closest('.section')?.querySelector('.arrows');
		if (!arrows) return;
		const btns = arrows.querySelectorAll('button');
		const prev = btns[0], next = btns[1];

		// шаг прокрутки — почти видимая ширина (≈ один "экран" карточек)
		const step = () => Math.max(track.clientWidth * 0.9, 220);
		prev?.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
		next?.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));

		// гасим стрелки на краях / когда листать нечего
		const update = () => {
			const max = track.scrollWidth - track.clientWidth - 2;
			if (prev) prev.disabled = track.scrollLeft <= 0;
			if (next) next.disabled = max <= 0 || track.scrollLeft >= max;
		};
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();
	});
})();