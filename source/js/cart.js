/* ===================================================
   Корзина «Вектор пола» — клиентская, на localStorage.
   Позиция: { slug, name, url, image, price, packArea, unit, area }
   price    — цена за единицу (м²/шт)
   packArea — м² в упаковке (0 — продаётся поштучно)
   area     — выбранное количество (шаг 1)
   =================================================== */
window.VPCart = (function () {
	const KEY = 'vp_cart';

	/* ===== Хранилище ===== */
	function read() {
		try { return JSON.parse(localStorage.getItem(KEY)) || []; }
		catch { return []; }
	}
	function write(items) {
		localStorage.setItem(KEY, JSON.stringify(items));
		updateBadge();
	}

	/* ===== Деньги: «2 475 ₽» ===== */
	function money(n) {
		return Math.round(n).toLocaleString('ru-RU').replace(/\u00A0/g, ' ') + ' ₽';
	}
	function trimNum(n) { return parseFloat(Number(n).toFixed(3)).toString(); }

	/* ===== Расчёт строки ===== */
	function calcLine(item) {
		const price    = parseFloat(item.price) || 0;
		const packArea = parseFloat(item.packArea) || 0;
		const area     = parseInt(item.area, 10) || 1;
		let packs, totalArea, total, qtyText;
		if (packArea > 0) {
			packs     = Math.ceil(area / packArea);
			totalArea = packs * packArea;
			total     = packs * (price * packArea);
			qtyText   = area + ' м² · ' + packs + ' уп.';
		} else {
			packs     = area;
			totalArea = area;
			total     = area * price;
			qtyText   = area + ' ' + (item.unit || 'шт.');
		}
		return { price, packArea, area, packs, totalArea, total, qtyText };
	}

	/* ===== Операции ===== */
	function add(item) {
		const items = read();
		const ex = items.find(i => i.slug === item.slug);
		if (ex) {
			ex.area = (parseInt(ex.area, 10) || 0) + (parseInt(item.area, 10) || 1);
		} else {
			items.push({
				slug: item.slug, name: item.name, url: item.url, image: item.image,
				price: item.price, packArea: item.packArea, unit: item.unit || 'м²',
				area: parseInt(item.area, 10) || 1
			});
		}
		write(items);
	}
	function setArea(slug, area) {
		const items = read();
		const it = items.find(i => i.slug === slug);
		if (it) { it.area = Math.max(1, parseInt(area, 10) || 1); write(items); }
	}
	function remove(slug) { write(read().filter(i => i.slug !== slug)); }
	function clear() { write([]); }
	function count() { return read().length; } // число позиций

	/* ===== Бейдж в шапке ===== */
	function updateBadge() {
		const n = count();
		document.querySelectorAll('.cart .count, .mob-icon .count').forEach(el => { el.textContent = n; });
		document.querySelectorAll('.js-cart-link').forEach(el => { el.textContent = 'Корзина (' + n + ')'; });
	}

	/* ===== Отправка заявки в Telegram (общая для корзины и «в один клик») ===== */
	async function submitOrder(payload, statusEl, btn, onSuccess) {
		// Лёгкая валидация на клиенте
		const name  = (payload.name  || '').trim();
		const phone = (payload.phone || '').trim();
		if (name.length < 2) { setStatus(statusEl, 'Введите имя', 'err'); return; }
		if (phone.replace(/\D/g, '').length < 10) { setStatus(statusEl, 'Введите телефон', 'err'); return; }

		if (btn) { btn.disabled = true; btn.dataset.label = btn.textContent; btn.textContent = 'Отправка…'; }
		setStatus(statusEl, '', '');

		let ok = false;
		let errMsg = 'Ошибка сети. Попробуйте ещё раз.';
		try {
			const fd = new FormData();
			Object.keys(payload).forEach(k => fd.append(k, payload[k]));
			const r = await fetch('/source/php/send.php', { method: 'POST', body: fd });
			// Читаем как текст и парсим вручную — устойчиво к мусору перед JSON (PHP warning и т.п.)
			const text = await r.text();
			let d = null;
			try { d = JSON.parse(text); } catch (e) {}
			if (d && d.ok) {
				ok = true;
			} else if (d) {
				errMsg = (d.errors && d.errors.join(', ')) || d.error || 'Не удалось отправить';
			} else if (r.ok && text.indexOf('"ok":true') !== -1) {
				ok = true; // ответ испорчен, но заявка ушла
			}
		} catch (e) {
			// errMsg уже про сеть
		} finally {
			if (btn) { btn.disabled = false; btn.textContent = btn.dataset.label || 'Отправить'; }
		}

		if (ok) {
			setStatus(statusEl, 'Заявка принята! Менеджер скоро свяжется с вами.', 'ok');
			// колбэк изолируем — его ошибка не должна превращаться в «Ошибка сети»
			if (typeof onSuccess === 'function') { try { onSuccess(); } catch (e) {} }
		} else {
			setStatus(statusEl, errMsg, 'err');
		}
	}
	function setStatus(el, msg, type) {
		if (!el) return;
		el.textContent = msg;
		el.className = 'form-status' + (type ? ' ' + type : '');
	}

	/* ===== Простая телефонная маска ===== */
	function phoneMask(input) {
		if (!input) return;
		input.addEventListener('input', function () {
			let d = this.value.replace(/\D/g, '');
			if (d.startsWith('8')) d = '7' + d.slice(1);
			if (d.startsWith('9')) d = '7' + d;
			d = d.slice(0, 11);
			let s = '+7';
			if (d.length > 1) s += ' (' + d.slice(1, 4);
			if (d.length >= 4) s += ') ' + d.slice(4, 7);
			if (d.length >= 7) s += '-' + d.slice(7, 9);
			if (d.length >= 9) s += '-' + d.slice(9, 11);
			this.value = s;
		});
	}

	/* ===== Рендер страницы /cart/ ===== */
	function renderCartPage() {
		const root = document.getElementById('cartRoot');
		if (!root) return;

		const items = read();
		const checkout = document.getElementById('cartCheckout');

		if (!items.length) {
			root.innerHTML =
				'<div class="cart-empty">' +
					'<p>Корзина пуста.</p>' +
					'<a href="/catalog/" class="btn btn--accent">Перейти в каталог</a>' +
				'</div>';
			if (checkout) checkout.hidden = true;
			return;
		}

		let grand = 0;
		let rows = '';
		items.forEach(item => {
			const c = calcLine(item);
			grand += c.total;
			rows +=
				'<div class="cart-row" data-slug="' + esc(item.slug) + '">' +
					'<a class="cart-row__img" href="' + esc(item.url) + '"><img src="' + esc(item.image) + '" alt="' + esc(item.name) + '" width="90" height="90"></a>' +
					'<div class="cart-row__main">' +
						'<a class="cart-row__name" href="' + esc(item.url) + '">' + esc(item.name) + '</a>' +
						'<div class="cart-row__price">' + money(c.price) + ' / ' + esc(item.unit || 'м²') + '</div>' +
					'</div>' +
					'<div class="cart-row__qty">' +
						'<button type="button" class="cart-minus" aria-label="Меньше">−</button>' +
						'<span class="cart-area">' + c.area + '</span>' +
						'<button type="button" class="cart-plus" aria-label="Больше">+</button>' +
						'<span class="cart-row__sub">' + c.qtyText + '</span>' +
					'</div>' +
					'<div class="cart-row__sum">' + money(c.total) + '</div>' +
					'<button type="button" class="cart-row__del" aria-label="Удалить">×</button>' +
				'</div>';
		});

		root.innerHTML =
			'<div class="cart-list">' + rows + '</div>' +
			'<div class="cart-total"><span>Итого</span><b id="cartGrand">' + money(grand) + '</b></div>';

		if (checkout) checkout.hidden = false;

		// Навешиваем обработчики строк
		root.querySelectorAll('.cart-row').forEach(row => {
			const slug = row.dataset.slug;
			row.querySelector('.cart-minus').addEventListener('click', () => {
				const it = read().find(i => i.slug === slug);
				if (it) { setArea(slug, (parseInt(it.area, 10) || 1) - 1); renderCartPage(); }
			});
			row.querySelector('.cart-plus').addEventListener('click', () => {
				const it = read().find(i => i.slug === slug);
				if (it) { setArea(slug, (parseInt(it.area, 10) || 1) + 1); renderCartPage(); }
			});
			row.querySelector('.cart-row__del').addEventListener('click', () => { remove(slug); renderCartPage(); });
		});
	}

	/* ===== Оформление заказа из корзины ===== */
	function initCheckout() {
		const form = document.getElementById('cartForm');
		if (!form) return;
		phoneMask(form.querySelector('input[type="tel"]'));

		const btn      = form.querySelector('button[type="button"]');
		const statusEl = form.querySelector('.form-status');
		const btnLabel = btn ? btn.textContent : 'Отправить заявку';

		// Сброс формы в исходное состояние (cartForm — div, не form, .reset() нет)
		function resetForm() {
			form.querySelectorAll('input, textarea').forEach(el => el.value = '');
			if (btn)      { btn.disabled = false; btn.textContent = btnLabel; }
			if (statusEl) { statusEl.textContent = ''; statusEl.className = 'form-status'; }
		}

		btn.addEventListener('click', () => {
			const items = read();
			if (!items.length) return;

			// Состав заказа текстом + общая сумма
			let grand = 0;
			const lines = items.map((item, idx) => {
				const c = calcLine(item);
				grand += c.total;
				return (idx + 1) + '. ' + item.name + ' — ' + c.qtyText + ' — ' + money(c.total);
			});

			submitOrder({
				source:  'Корзина',
				name:    form.querySelector('[name="name"]').value,
				phone:   form.querySelector('[name="phone"]').value,
				comment: form.querySelector('[name="comment"]').value,
				items:   lines.join('\n'),
				total:   money(grand)
			}, statusEl, btn, () => {
				// Успех: блокируем кнопку (повторно не отправить), чистим поля.
				// Сообщение «менеджер свяжется» уже в статусе.
				btn.disabled = true;
				btn.textContent = 'Заявка отправлена';
				form.querySelectorAll('input, textarea').forEach(el => el.value = '');
				// Через 5с чистим корзину и возвращаем форму в исходное (на след. заказ)
				setTimeout(() => { clear(); renderCartPage(); resetForm(); }, 5000);
			});
		});
	}

	/* ===== Утилита ===== */
	function esc(s) {
		return String(s == null ? '' : s)
			.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
	}

	/* ===== Инициализация ===== */
	document.addEventListener('DOMContentLoaded', () => {
		updateBadge();
		renderCartPage();
		initCheckout();
	});
	updateBadge(); // на случай, если DOM уже готов

	// Публичный API (для product.js)
	return { add, remove, setArea, get: read, count, updateBadge, submitOrder, phoneMask, money, calcLine };
})();