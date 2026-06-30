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

	let area = parseInt(areaEl?.value, 10) || 1; // целые м², шаг 1

	const money   = n => Math.round(n).toLocaleString('ru-RU').replace(/\u00A0/g, ' ') + ' ₽';
	const trimNum = n => parseFloat(n.toFixed(3)).toString();

	// Зафиксировать минимум 1 (после ручного ввода/blur)
	function commitArea() {
		if (area < 1) area = 1;
		if (areaEl) areaEl.value = area;
		render();
	}

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
		if (area > 1) { area--; if (areaEl) areaEl.value = area; render(); }
	});
	box.querySelector('.calc-plus')?.addEventListener('click', () => {
		area++; if (areaEl) areaEl.value = area; render();
	});

	// Ручной ввод: только цифры, живой пересчёт (пусто → 0 временно)
	areaEl?.addEventListener('input', () => {
		const digits = areaEl.value.replace(/\D/g, '');
		areaEl.value = digits;
		area = parseInt(digits, 10) || 0;
		render();
	});
	// blur/Enter — фиксируем минимум 1
	areaEl?.addEventListener('blur', commitArea);
	areaEl?.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); areaEl.blur(); } });

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
		commitArea(); // зафиксировать площадь (минимум 1) перед добавлением
		if (window.VPCart) window.VPCart.add(cartItem());
		if (addNote) { addNote.hidden = false; clearTimeout(addNote._t); addNote._t = setTimeout(() => addNote.hidden = true, 4000); }
	});

	/* ===== Кнопка «Купить в один клик» → попап ===== */
	const modal    = document.getElementById('quickBuyModal');
	const quickBtn = box.querySelector('.calc-quick');
	const qbForm   = modal?.querySelector('.qb-form');
	const qbBtn    = qbForm?.querySelector('button[type="button"]');
	const qbStatus = qbForm?.querySelector('.form-status');
	const qbLabel  = qbBtn ? qbBtn.textContent : 'Отправить заявку';

	function syncModal() {
		if (!modal || modal.hidden) return;
		const s = state();
		const q = modal.querySelector('#qbQty');
		const t = modal.querySelector('#qbTotal');
		if (q) q.textContent = s.qty;
		if (t) t.textContent = money(s.total);
	}
	// Возврат формы в чистое исходное состояние (.qb-form — это div, не form)
	function resetQbForm() {
		if (!qbForm) return;
		qbForm.querySelectorAll('input, textarea').forEach(el => el.value = '');
		if (qbBtn)    { qbBtn.disabled = false; qbBtn.textContent = qbLabel; }
		if (qbStatus) { qbStatus.textContent = ''; qbStatus.className = 'form-status'; }
	}
	function openModal() {
		if (!modal) return;
		commitArea();   // не открываем попап с пустой/нулевой площадью
		resetQbForm();  // всегда открываем чистым
		modal.hidden = false;
		document.body.style.overflow = 'hidden';
		syncModal();
		qbForm?.querySelector('input[name="name"]')?.focus();
	}
	function closeModal() {
		if (!modal) return;
		modal.hidden = true;
		document.body.style.overflow = '';
		resetQbForm();  // чистим форму после закрытия
	}

	quickBtn?.addEventListener('click', openModal);

	if (modal) {
		modal.querySelectorAll('[data-close]').forEach(el => el.addEventListener('click', closeModal));
		document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

		if (window.VPCart) window.VPCart.phoneMask(qbForm.querySelector('input[type="tel"]'));

		qbBtn?.addEventListener('click', () => {
			const s = state();
			window.VPCart.submitOrder({
				source:   'Купить в один клик',
				name:     qbForm.querySelector('[name="name"]').value,
				phone:    qbForm.querySelector('[name="phone"]').value,
				comment:  qbForm.querySelector('[name="comment"]').value,
				product:  box.dataset.name,
				quantity: s.qty,
				total:    money(s.total)
			}, qbStatus, qbBtn, () => {
				// Успех: блокируем кнопку (повторно не отправить), чистим поля.
				// Текст «менеджер свяжется» уже стоит в статусе → потом закрываем.
				qbBtn.disabled = true;
				qbBtn.textContent = 'Заявка отправлена';
				qbForm.querySelectorAll('input, textarea').forEach(el => el.value = '');
				setTimeout(closeModal, 2500);
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