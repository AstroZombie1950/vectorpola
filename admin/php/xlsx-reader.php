<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

/**
 * XlsxReader — минималистичный парсер xlsx без зависимостей.
 * Читает первый лист, возвращает массив строк.
 *
 * Использование:
 *   $r    = new XlsxReader('/path/to/file.xlsx');
 *   $rows = $r->getRows();  // [ ['Название', 'Цена', ...], ['Ламинат', 1200, ...], ... ]
 */
class XlsxReader {

	private string $filePath;
	private array  $sharedStrings = [];

	public function __construct(string $filePath) {
		$this->filePath = $filePath;
	}

	/* Возвращает все строки первого листа (включая заголовки) */
	public function getRows(): array {
		$zip = new ZipArchive();
		if ($zip->open($this->filePath) !== true) {
			throw new RuntimeException('Не удалось открыть файл XLSX');
		}

		// Shared strings
		$ssXml = $zip->getFromName('xl/sharedStrings.xml');
		if ($ssXml !== false) {
			$this->sharedStrings = $this->parseSharedStrings($ssXml);
		}

		// Первый лист (sheet1 или sheet по workbook)
		$sheetName = $this->resolveSheetName($zip);
		$sheetXml  = $zip->getFromName($sheetName);
		$zip->close();

		if ($sheetXml === false) {
			throw new RuntimeException('Не удалось прочитать лист xlsx');
		}

		return $this->parseSheet($sheetXml);
	}

	/* ===== Парсинг sharedStrings ===== */
	private function parseSharedStrings(string $xml): array {
		$strings = [];
		$doc     = $this->loadXml($xml);
		foreach ($doc->si as $si) {
			// Объединяем все <t> внутри <si> (учитываем rich text)
			$text = '';
			foreach ($si->r as $r) {
				$text .= (string)$r->t;
			}
			// Если нет <r>, берём <t> напрямую
			if ($text === '' && isset($si->t)) {
				$text = (string)$si->t;
			}
			$strings[] = $text;
		}
		return $strings;
	}

	/* ===== Определяем путь к первому листу ===== */
	private function resolveSheetName(ZipArchive $zip): string {
		// Пробуем стандартное имя
		if ($zip->locateName('xl/worksheets/sheet1.xml') !== false) {
			return 'xl/worksheets/sheet1.xml';
		}
		// Ищем через workbook.xml
		$wb = $zip->getFromName('xl/workbook.xml');
		if ($wb !== false) {
			$doc = $this->loadXml($wb);
			$doc->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
			$sheets = $doc->xpath('//ns:sheet');
			if (!empty($sheets)) {
				// r:id ведёт на xl/_rels/workbook.xml.rels
				$rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
				if ($rels) {
					$rDoc = $this->loadXml($rels);
					$rDoc->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/package/2006/relationships');
					$rId  = (string)$sheets[0]->attributes('r', true)['id'];
					foreach ($rDoc->xpath('//r:Relationship') as $rel) {
						if ((string)$rel['Id'] === $rId) {
							return 'xl/' . ltrim((string)$rel['Target'], '/');
						}
					}
				}
			}
		}
		return 'xl/worksheets/sheet1.xml';
	}

	/* ===== Парсинг листа ===== */
	private function parseSheet(string $xml): array {
		$doc  = $this->loadXml($xml);
		$doc->registerXPathNamespace('ns', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');

		$rows     = [];
		$maxCol   = 0;
		$rawRows  = [];

		foreach ($doc->xpath('//ns:row') as $row) {
			$rowIdx  = (int)$row['r'] - 1;
			$rowData = [];

			foreach ($row->c as $cell) {
				$ref    = (string)$cell['r'];
				$colIdx = $this->colIndex($ref);
				$type   = (string)$cell['t'];
				$val    = isset($cell->v) ? (string)$cell->v : '';

				// Shared string
				if ($type === 's') {
					$val = $this->sharedStrings[(int)$val] ?? '';
				}
				// Inline string
				if ($type === 'inlineStr' && isset($cell->is->t)) {
					$val = (string)$cell->is->t;
				}

				$rowData[$colIdx] = $val;
				$maxCol = max($maxCol, $colIdx);
			}

			$rawRows[$rowIdx] = $rowData;
		}

		// Выравниваем все строки по ширине
		ksort($rawRows);
		foreach ($rawRows as $rowIdx => $rowData) {
			$row = [];
			for ($c = 0; $c <= $maxCol; $c++) {
				$row[] = $rowData[$c] ?? '';
			}
			$rows[] = $row;
		}

		return $rows;
	}

	/* ===== Буква(ы) столбца → индекс (A=0, B=1, AA=26 …) ===== */
	private function colIndex(string $cellRef): int {
		preg_match('/^([A-Z]+)/', strtoupper($cellRef), $m);
		$letters = $m[1] ?? 'A';
		$idx     = 0;
		$len     = strlen($letters);
		for ($i = 0; $i < $len; $i++) {
			$idx = $idx * 26 + (ord($letters[$i]) - 64);
		}
		return $idx - 1;
	}

	/* ===== Загрузка XML без предупреждений ===== */
	private function loadXml(string $xml): SimpleXMLElement {
		libxml_use_internal_errors(true);
		$doc = simplexml_load_string($xml);
		libxml_clear_errors();
		if ($doc === false) {
			throw new RuntimeException('Ошибка парсинга XML');
		}
		return $doc;
	}
}
