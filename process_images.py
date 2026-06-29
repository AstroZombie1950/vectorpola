#!/usr/bin/env python3
# Приведение товарных картинок к единому виду 800x800.
# Обрезает белые поля по контенту, затем заполняет квадрат (cover) — как у донора.
# Пишет в ОТДЕЛЬНУЮ папку, оригиналы не трогает. Подозрительные (битые/мелкие) — в лог.

import sys, os, glob, csv
from PIL import Image
import numpy as np

# ── настройки ──────────────────────────────────────────────
SRC    = "uploads/products"          # папка с оригиналами
DST    = "uploads/products_fixed"    # куда писать результат
TARGET = 800                         # сторона квадрата
MODE   = "cover"                     # "cover" (заполнить) | "contain" (поля)
PAD    = 0.07                        # поле для режима contain (доля стороны)
MAX_UP = 2.0                         # макс. апскейл — защита от мыла на битых
SUSPECT_SCALE = 1.8                  # если для заполнения нужен апскейл больше — в лог
BG_THR = 250                         # порог «почти-белого» пикселя (0..255)
COL_FRAC = 0.992                     # доля фона в строке/столбце, чтобы счесть полем
Q      = 82                          # качество webp
# ───────────────────────────────────────────────────────────

# Находим bbox контента: режем строки/столбцы, которые почти целиком белые
def content_bbox(im):
	a = np.asarray(im.convert("RGB")).astype(np.int16)
	bg = (a[:,:,0]>=BG_THR)&(a[:,:,1]>=BG_THR)&(a[:,:,2]>=BG_THR)
	fg = ~bg
	rmask = fg.mean(axis=1) > (1 - COL_FRAC)
	cmask = fg.mean(axis=0) > (1 - COL_FRAC)
	if not rmask.any() or not cmask.any():
		return None
	y0, y1 = int(np.argmax(rmask)), len(rmask) - int(np.argmax(rmask[::-1]))
	x0, x1 = int(np.argmax(cmask)), len(cmask) - int(np.argmax(cmask[::-1]))
	return x0, y0, x1, y1

# Обрезка белых полей
def trim(im):
	bb = content_bbox(im)
	return im.crop(bb) if bb else im

# Заполнить квадрат целиком, лишнее по краям обрезать
def make_cover(c):
	s = max(TARGET / c.width, TARGET / c.height)
	s = min(s, MAX_UP)
	nw, nh = max(1, round(c.width * s)), max(1, round(c.height * s))
	c = c.resize((nw, nh), Image.LANCZOS)
	canvas = Image.new("RGB", (TARGET, TARGET), (255, 255, 255))
	if nw >= TARGET and nh >= TARGET:                       # хватает на полный кроп
		left, top = (nw - TARGET)//2, (nh - TARGET)//2
		return c.crop((left, top, left + TARGET, top + TARGET))
	canvas.paste(c, ((TARGET - nw)//2, (TARGET - nh)//2))    # мелкий контент — по центру
	return canvas

# Вписать с единым полем (запасной режим)
def make_contain(c):
	box = int(TARGET * (1 - 2*PAD))
	s = min(box / c.width, box / c.height, MAX_UP)
	nw, nh = max(1, round(c.width * s)), max(1, round(c.height * s))
	c = c.resize((nw, nh), Image.LANCZOS)
	canvas = Image.new("RGB", (TARGET, TARGET), (255, 255, 255))
	canvas.paste(c, ((TARGET - nw)//2, (TARGET - nh)//2))
	return canvas

def main():
	os.makedirs(DST, exist_ok=True)
	files = []
	for ext in ("webp","jpg","jpeg","png"):
		files += glob.glob(os.path.join(SRC, f"*.{ext}"))
	files = sorted(files)
	if not files:
		print(f"Нет картинок в {SRC}"); sys.exit(1)

	suspects, errors, done = [], [], 0
	for i, f in enumerate(files, 1):
		name = os.path.splitext(os.path.basename(f))[0] + ".webp"
		try:
			im = Image.open(f).convert("RGB")
			c = trim(im)
			# нужный апскейл для заполнения — индикатор битого/мелкого исходника
			need = max(TARGET / c.width, TARGET / c.height)
			if need > SUSPECT_SCALE:
				suspects.append((os.path.basename(f), f"{c.width}x{c.height}", round(need,2)))
			out = make_cover(c) if MODE == "cover" else make_contain(c)
			out.save(os.path.join(DST, name), "WEBP", quality=Q, method=6)
			done += 1
		except Exception as e:
			errors.append((os.path.basename(f), str(e)))
		if i % 500 == 0:
			print(f"  …{i}/{len(files)}")

	# лог подозрительных — на ручную проверку/перекачку
	with open("images_suspects.csv", "w", newline="") as fh:
		w = csv.writer(fh); w.writerow(["file","content_size","need_upscale"])
		w.writerows(suspects)
	if errors:
		with open("images_errors.csv", "w", newline="") as fh:
			w = csv.writer(fh); w.writerow(["file","error"]); w.writerows(errors)

	print(f"\nГотово: {done}/{len(files)} → {DST}/  (режим: {MODE})")
	print(f"Подозрительных (битые/мелкие): {len(suspects)} → images_suspects.csv")
	if errors:
		print(f"Ошибок чтения: {len(errors)} → images_errors.csv")

if __name__ == "__main__":
	main()
