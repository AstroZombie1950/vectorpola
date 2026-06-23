/* Кнопка «наверх» */
const toTop = document.getElementById('toTop');
window.addEventListener('scroll', () => {
	toTop.classList.toggle('show', window.scrollY > 400);
});
toTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));

/* Динамический год в футере */
const yearEl = document.getElementById('currentYear');
if (yearEl) yearEl.textContent = new Date().getFullYear();

/* FAQ: плавная анимация высоты */
document.querySelectorAll('.faq details').forEach(d => {
	const summary = d.querySelector('summary');
	const ans = d.querySelector('.ans');
	summary.addEventListener('click', e => {
		e.preventDefault();
		if (d.open) {
			/* закрытие: из текущей высоты в 0 */
			ans.style.height = ans.scrollHeight + 'px';
			requestAnimationFrame(() => { ans.style.height = '0'; });
			ans.addEventListener('transitionend', function end() {
				d.open = false; ans.style.height = ''; ans.removeEventListener('transitionend', end);
			}, { once: true });
		} else {
			/* открытие: из 0 в реальную высоту, затем снимаем inline */
			d.open = true;
			ans.style.height = '0';
			requestAnimationFrame(() => { ans.style.height = ans.scrollHeight + 'px'; });
			ans.addEventListener('transitionend', function end() {
				ans.style.height = ''; ans.removeEventListener('transitionend', end);
			}, { once: true });
		}
	});
});

/* Мобильное меню: открытие/закрытие, скролл-лок, Escape */
const burger = document.getElementById('mobileBurger');
const mmenu = document.getElementById('mobileMenu');
const mmClose = mmenu.querySelector('.mm-close');
const openMenu = () => {
	/* Сохраняем текущий скролл и фиксируем страницу */
	const scrollY = window.scrollY;
	document.body.style.top = `-${scrollY}px`;
	document.body.classList.add('menu-open');
	mmenu.classList.add('open');
	mmenu.setAttribute('aria-hidden', 'false');
};
const closeMenu = () => {
	/* Снимаем фиксацию и восстанавливаем позицию скролла */
	const scrollY = parseInt(document.body.style.top || '0') * -1;
	document.body.classList.remove('menu-open');
	document.body.style.top = '';
	window.scrollTo(0, scrollY);
	mmenu.classList.remove('open');
	mmenu.setAttribute('aria-hidden', 'true');
};
burger.addEventListener('click', openMenu);
mmClose.addEventListener('click', closeMenu);
/* закрытие по клику на любую ссылку меню */
mmenu.querySelectorAll('a').forEach(a => a.addEventListener('click', closeMenu));
/* закрытие по Escape */
document.addEventListener('keydown', e => { if (e.key === 'Escape' && mmenu.classList.contains('open')) closeMenu(); });
/* авто-закрытие при переходе на десктоп */
window.addEventListener('resize', () => { if (window.innerWidth > 900 && mmenu.classList.contains('open')) closeMenu(); });
/* ===== Телефонная маска ===== */
function applyPhoneMask(input) {
	input.addEventListener('input', function () {
		let val = this.value.replace(/\D/g, '');
		/* нормализуем: 8 → 7, добавляем 7 если нет */
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
	/* при фокусе ставим +7 если пусто */
	input.addEventListener('focus', function () {
		if (this.value === '') this.value = '+7 ';
	});
	/* если только +7 — очищаем при уходе */
	input.addEventListener('blur', function () {
		if (this.value.trim() === '+7') this.value = '';
	});
}

/* ===== Валидация одного поля ===== */
function validateField(input) {
	const errorEl = input.parentElement.querySelector('.field-error');
	let msg = '';

	if (input.dataset.required !== undefined && input.value.trim() === '') {
		msg = input.dataset.errorEmpty || 'Заполните поле';
	} else if (input.type === 'tel') {
		const digits = input.value.replace(/\D/g, '');
		if (digits.length < 11) msg = 'Введите полный номер телефона';
	} else if (input.dataset.minlen) {
		if (input.value.trim().length < +input.dataset.minlen) {
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

	/* Валидация по blur для каждого поля */
	formEl.querySelectorAll('input, textarea').forEach(field => {
		field.addEventListener('blur', () => validateField(field));
	});

	/* Отправка */
	const btn = formEl.querySelector('button[type="button"]');
	const status = formEl.querySelector('.form-status');

	btn.addEventListener('click', async () => {
		/* Валидируем все поля */
		const fields = [...formEl.querySelectorAll('input, textarea')];
		const valid = fields.map(validateField).every(Boolean);
		if (!valid) return;

		btn.disabled = true;
		const origText = btn.textContent;
		btn.textContent = 'Отправляем…';
		if (status) { status.textContent = ''; status.className = 'form-status'; }

		const data = new FormData(formEl);

		try {
			const res = await fetch('/source/php/send.php', { method: 'POST', body: data });
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

/* Инициализируем все формы на странице */
document.querySelectorAll('.cta-form').forEach(initCtaForm);
