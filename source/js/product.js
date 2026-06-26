/* ===== Карточка товара: калькулятор + корзина + «в один клик» ===== */
function initProductCalc(box) {
	if (!box) return;

	const pricePerPack = parseFloat(box.dataset.pricePerPack) || 0;
	const packArea     = parseFloat(box.dataset.packArea) || 0;
	const price        = parseFloat(box.dataset.price) || 0;

	const areaEl   = box.querySelector('#calcArea');
	const packsEl  = box.querySelector('#calcPacks');
	const areaTotEl= box.querySelector('#calcTotalArea');
	const totalEl  = box.querySelector('#calcTotal');

	let area = parseInt(areaEl?.textContent, 10) || 1; // шаг — 1 м²

	const money   = n => Math.round(n).toLocaleString('ru-RU').replace(/\u00A0/g, ' ') + ' ₽';
	const trimNum = n => parseFloat(n.toFixed(3)).toString();

	// Текущее состояние для корзины/заявки
	function state() {
		const packs = packArea > 0 ? Math.ceil(area / packArea) : area;
		const total = packArea > 0 ? packs * pricePerPack : area * price;
		const qty   = packArea > 0 ? (area + ' м² · ' + packs + ' уп.') : (area + ' ' + (box.dataset.unit || 'шт.'));
		return { packs, total, qty };
	}

	function render() {
		const s = state();
		if (packsEl)   packsEl.textContent   = s.packs;
		if (areaTotEl) areaTotEl.textContent = trimNum(packArea > 0 ? s.packs * packArea : area);
		if (totalEl)   totalEl.textContent   = money(s.total);
		syncModal(); // если открыт попап «в один клик»
	}

	box.querySelector('.calc-minus')?.addEventListener('click', () => {
		if (area > 1) { area--; areaEl.textContent = area; render(); }
	});
	box.querySelector('.calc-plus')?.addEventListener('click', () => {
		area++; areaEl.textContent = area; render();
	});

	// Данные товара для корзины
	function cartItem() {
		return {
			slug:     box.dataset.slug,
			name:     box.dataset.name,
			url:      box.dataset.url,
			image:    box.dataset.image,
			price:    price,
			packArea: packArea,
			unit:     box.dataset.unit || 'м²',
			area:     area
		};
	}

	/* ===== Кнопка «В корзину» ===== */
	const addBtn  = box.querySelector('.calc-add');
	const addNote = box.querySelector('.calc-added');
	addBtn?.addEventListener('click', () => {
		if (window.VPCart) window.VPCart.add(cartItem());
		if (addNote) { addNote.hidden = false; clearTimeout(addNote._t); addNote._t = setTimeout(() => addNote.hidden = true, 4000); }
	});

	/* ===== Кнопка «Купить в один клик» → попап ===== */
	const modal   = document.getElementById('quickBuyModal');
	const quickBtn= box.querySelector('.calc-quick');

	function syncModal() {
		if (!modal || modal.hidden) return;
		const s = state();
		const q = modal.querySelector('#qbQty');
		const t = modal.querySelector('#qbTotal');
		if (q) q.textContent = s.qty;
		if (t) t.textContent = money(s.total);
	}
	function openModal() {
		if (!modal) return;
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
		syncModal();
		modal.querySelector('input[name="name"]')?.focus();
	}
	function closeModal() {
		if (!modal) return;
		modal.hidden = true;
		document.body.style.overflow = '';
	}

	quickBtn?.addEventListener('click', openModal);

	if (modal) {
		modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeModal));
		document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

		const form = modal.querySelector('.qb-form');
		if (window.VPCart) window.VPCart.phoneMask(form.querySelector('input[type="tel"]'));

		form?.querySelector('button[type="button"]').addEventListener('click', () => {
			const s = state();
			const status = form.querySelector('.form-status');
			const btn    = form.querySelector('button[type="button"]');
			window.VPCart.submitOrder({
				source:   'Купить в один клик',
				name:     form.querySelector('[name="name"]').value,
				phone:    form.querySelector('[name="phone"]').value,
				comment:  form.querySelector('[name="comment"]').value,
				product:  box.dataset.name,
				quantity: s.qty,
				total:    money(s.total)
			}, status, btn, () => {
				form.reset();
				setTimeout(closeModal, 1600);
			});
		});
	}

	render();
}
document.querySelectorAll('.product-calc').forEach(initProductCalc);

/* ===== Карточка товара: галерея ===== */
(function initGallery() {
	const main = document.getElementById('pgMain');
	if (!main) return;
	document.querySelectorAll('.pg-thumb').forEach(thumb => {
		thumb.addEventListener('click', () => {
			main.src = thumb.dataset.img;
			document.querySelectorAll('.pg-thumb').forEach(t => t.classList.remove('is-active'));
			thumb.classList.add('is-active');
		});
	});
})();

/* ===== Карточка товара: копировать ссылку ===== */
(function initShareCopy() {
	const btn = document.querySelector('.sh-copy');
	if (!btn) return;
	const copied = document.getElementById('shCopied');
	btn.addEventListener('click', async () => {
		try {
			await navigator.clipboard.writeText(btn.dataset.url || location.href);
			if (copied) { copied.hidden = false; setTimeout(() => copied.hidden = true, 2000); }
		} catch {}
	});
})();