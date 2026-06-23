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
	const offset = header ? header.offsetHeight + 12 : 80;
	const top = target.getBoundingClientRect().top + window.scrollY - offset;
	window.scrollTo({ top, behavior: 'smooth' });
});

/* ===== FAQ: плавное открытие ===== */
document.querySelectorAll('.faq details').forEach(details => {
	const ans = details.querySelector('.ans');
	const ansIn = details.querySelector('.ans-in');
	if (!ans || !ansIn) return;

	details.addEventListener('toggle', () => {
		if (details.open) {
			ans.style.height = ansIn.offsetHeight + 'px';
		} else {
			ans.style.height = ans.scrollHeight + 'px';
			requestAnimationFrame(() => { ans.style.height = '0'; });
		}
	});
});

/* ===== Мобильное меню ===== */
const burger = document.getElementById('mobileBurger');
const menu   = document.getElementById('mobileMenu');
const close  = menu?.querySelector('.mm-close');

function openMenu() {
	menu.classList.add('open');
	menu.setAttribute('aria-hidden', 'false');
	document.body.classList.add('menu-open');
}
function closeMenu() {
	menu.classList.remove('open');
	menu.setAttribute('aria-hidden', 'true');
	document.body.classList.remove('menu-open');
}

if (burger) burger.addEventListener('click', openMenu);
if (close)  close.addEventListener('click', closeMenu);

/* Закрытие по клику на ссылку внутри меню */
menu?.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));

/* Закрытие по Escape */
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });

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

	const phoneInput = formEl.querySelector('input[type="tel"]');
	if (phoneInput) applyPhoneMask(phoneInput);

	/* Валидация по blur */
	formEl.querySelectorAll('input, textarea').forEach(field => {
		field.addEventListener('blur', () => validateField(field));
	});

	const btn    = formEl.querySelector('button[type="button"]');
	const status = formEl.querySelector('.form-status');

	btn.addEventListener('click', async () => {
		const fields = [...formEl.querySelectorAll('input, textarea')];
		const valid  = fields.map(validateField).every(Boolean);
		if (!valid) return;

		btn.disabled = true;
		const origText = btn.textContent;
		btn.textContent = 'Отправляем…';
		if (status) { status.textContent = ''; status.className = 'form-status'; }

		try {
			const res  = await fetch('/source/php/send.php', { method: 'POST', body: new FormData(formEl) });
			const json = await res.json();
			if (json.ok) {
				if (status) { status.textContent = 'Заявка отправлена! Мы перезвоним вам.'; status.classList.add('ok'); }
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

document.querySelectorAll('.cta-form').forEach(initCtaForm);