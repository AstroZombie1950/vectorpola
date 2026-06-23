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