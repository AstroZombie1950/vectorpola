/* ===== Sidebar (мобильный) ===== */
(function () {
	const sidebar  = document.getElementById('sidebar');
	const overlay  = document.getElementById('sidebarOverlay');
	const toggle   = document.getElementById('sidebarToggle');
	if (!sidebar) return;

	function open() {
		sidebar.classList.add('sidebar--open');
		overlay.classList.remove('hidden');
		document.body.style.overflow = 'hidden';
	}
	function close() {
		sidebar.classList.remove('sidebar--open');
		overlay.classList.add('hidden');
		document.body.style.overflow = '';
	}

	toggle?.addEventListener('click', open);
	overlay?.addEventListener('click', close);
	document.addEventListener('keydown', e => e.key === 'Escape' && close());
})();

/* ===== Табы ===== */
document.querySelectorAll('.tabs').forEach(tabGroup => {
	tabGroup.querySelectorAll('.tab-btn').forEach(btn => {
		btn.addEventListener('click', () => {
			const target = btn.dataset.tab;

			// Снимаем активное со всех кнопок и панелей в этой группе
			tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('tab-btn--active'));
			document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('tab-pane--active'));

			btn.classList.add('tab-btn--active');
			document.getElementById(target)?.classList.add('tab-pane--active');

			// Пишем таб в URL без перезагрузки
			const url = new URL(location.href);
			url.searchParams.set('tab', target);
			history.replaceState(null, '', url);
		});
	});
});

// Восстанавливаем таб из URL
(function () {
	const params  = new URLSearchParams(location.search);
	const tabName = params.get('tab');
	if (!tabName) return;

	const btn = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
	if (btn) btn.click();
})();

/* ===== Спойлеры ===== */
document.querySelectorAll('.spoiler-toggle').forEach(btn => {
	btn.addEventListener('click', () => {
		const expanded = btn.getAttribute('aria-expanded') === 'true';
		btn.setAttribute('aria-expanded', String(!expanded));
		const body = btn.nextElementSibling;
		if (body) body.classList.toggle('open', !expanded);
	});
});
