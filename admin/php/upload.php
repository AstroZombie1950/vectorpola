<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

require_once __DIR__ . '/products-data.php';

/* ===== Настройки сжатия ===== */
define('IMG_SIZE',    800);  // квадрат 800×800, кроп по центру
define('IMG_QUALITY', 82);   // качество WebP

/* ===== Создаём папку если нет ===== */
function uploadsEnsureDir(): bool {
	if (!is_dir(UPLOADS_DIR)) {
		return mkdir(UPLOADS_DIR, 0755, true);
	}
	return true;
}

/* ===== Загрузка из $_FILES ===== */
function imageUploadFromFile(array $file): array {
	if ($file['error'] !== UPLOAD_ERR_OK) {
		return ['ok' => false, 'error' => 'Ошибка загрузки файла (код ' . $file['error'] . ')'];
	}

	$allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
	$mime    = mime_content_type($file['tmp_name']);
	if (!in_array($mime, $allowed, true)) {
		return ['ok' => false, 'error' => 'Недопустимый тип файла: ' . $mime];
	}

	return imageProcess($file['tmp_name']);
}

/* ===== Загрузка по URL ===== */
function imageUploadFromUrl(string $url): array {
	$url = trim($url);
	if (!filter_var($url, FILTER_VALIDATE_URL)) {
		return ['ok' => false, 'error' => 'Некорректная ссылка: ' . $url];
	}

	// Скачиваем во временный файл
	$tmp = tempnam(sys_get_temp_dir(), 'vp_img_');
	$ch  = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_TIMEOUT        => 20,
		CURLOPT_MAXREDIRS      => 3,
		// Ограничиваем размер — не больше 15 MB
		CURLOPT_BUFFERSIZE     => 1024 * 128,
	]);
	$data = curl_exec($ch);
	$err  = curl_error($ch);
	curl_close($ch);

	if ($err || $data === false) {
		@unlink($tmp);
		return ['ok' => false, 'error' => 'Не удалось скачать изображение: ' . $err];
	}

	file_put_contents($tmp, $data);

	// Проверяем что это действительно картинка
	$mime = mime_content_type($tmp);
	if (!str_starts_with($mime, 'image/')) {
		@unlink($tmp);
		return ['ok' => false, 'error' => 'По ссылке не изображение: ' . $url];
	}

	$result = imageProcess($tmp);
	@unlink($tmp);
	return $result;
}

/* ===== Ресайз и сохранение в WebP ===== */
function imageProcess(string $srcPath): array {
	uploadsEnsureDir();

	// Читаем исходник
	$info = @getimagesize($srcPath);
	if (!$info) {
		return ['ok' => false, 'error' => 'Не удалось прочитать изображение'];
	}

	[$srcW, $srcH, $type] = $info;

	$src = match ($type) {
		IMAGETYPE_JPEG => imagecreatefromjpeg($srcPath),
		IMAGETYPE_PNG  => imagecreatefrompng($srcPath),
		IMAGETYPE_WEBP => imagecreatefromwebp($srcPath),
		IMAGETYPE_GIF  => imagecreatefromgif($srcPath),
		default        => null,
	};

	if (!$src) {
		return ['ok' => false, 'error' => 'Неподдерживаемый формат изображения'];
	}

	// Кроп по центру до квадрата IMG_SIZE × IMG_SIZE
	$size = IMG_SIZE;
	$dst  = imagecreatetruecolor($size, $size);

	// Белый фон (для JPEG без прозрачности)
	$white = imagecolorallocate($dst, 255, 255, 255);
	imagefilledrectangle($dst, 0, 0, $size, $size, $white);

	// Вычисляем область кропа из источника
	$srcRatio = $srcW / $srcH;
	if ($srcRatio > 1) {
		// Широкое — обрезаем по ширине
		$cropH = $srcH;
		$cropW = $srcH;
		$cropX = (int)(($srcW - $cropW) / 2);
		$cropY = 0;
	} elseif ($srcRatio < 1) {
		// Высокое — обрезаем по высоте
		$cropW = $srcW;
		$cropH = $srcW;
		$cropX = 0;
		$cropY = (int)(($srcH - $cropH) / 2);
	} else {
		// Уже квадрат
		$cropW = $srcW;
		$cropH = $srcH;
		$cropX = 0;
		$cropY = 0;
	}

	imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $size, $size, $cropW, $cropH);
	imagedestroy($src);

	// Сохраняем как WebP
	$filename = 'img_' . bin2hex(random_bytes(8)) . '.webp';
	$savePath = UPLOADS_DIR . $filename;

	if (!imagewebp($dst, $savePath, IMG_QUALITY)) {
		imagedestroy($dst);
		return ['ok' => false, 'error' => 'Не удалось сохранить изображение'];
	}
	imagedestroy($dst);

	return [
		'ok'   => true,
		'url'  => UPLOADS_URL . $filename,
		'file' => $savePath,
		'w'    => IMG_SIZE,
		'h'    => IMG_SIZE,
	];
}

/* ===== Удаление файла изображения с диска ===== */
function imageDelete(string $url): void {
	if (!str_starts_with($url, UPLOADS_URL)) return;
	$file = $_SERVER['DOCUMENT_ROOT'] . $url;
	if (file_exists($file)) @unlink($file);
}
