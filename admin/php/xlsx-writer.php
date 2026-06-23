<?php
// Запрещаем прямой вызов
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
	http_response_code(403); exit;
}

/**
 * XlsxWriter — минималистичный генератор xlsx без зависимостей.
 * Использует ZipArchive + ручной XML.
 *
 * Использование:
 *   $w = new XlsxWriter();
 *   $w->addSheet('Товары');
 *   $w->writeRow(['Название', 'Цена'], 'header');
 *   $w->writeRow(['Ламинат', 1200], 'row');
 *   $w->freezeFirstRow();
 *   $w->output('export.xlsx');   // отдаёт файл в браузер
 *   // или $w->save('/path/file.xlsx');
 */
class XlsxWriter {

	/* ===== Цвета и стили ===== */
	private const COLOR_HEADER_BG  = 'FF2B2F38'; // тёмный (как шапка сайта)
	private const COLOR_HEADER_FG  = 'FFFFFFFF';
	private const COLOR_FIXED_BG   = 'FFFFF3DC'; // медово-кремовый — фиксированные столбцы
	private const COLOR_DYNAMIC_BG = 'FFE8F5E9'; // бледно-зелёный — доп. характеристики
	private const COLOR_ROW_ODD    = 'FFFFFFFF';
	private const COLOR_ROW_EVEN   = 'FFFFF8EE'; // чередование строк

	private array  $rows        = [];
	private array  $colTypes    = []; // 'header' | 'fixed' | 'dynamic'
	private int    $fixedCols   = 0;  // кол-во фиксированных столбцов
	private bool   $freeze      = false;
	private string $sheetName   = 'Лист1';
	private array  $colWidths   = [];

	public function addSheet(string $name): void {
		$this->sheetName = $name;
	}

	/* Записать строку заголовков */
	public function writeHeader(array $cells, int $fixedCount = 0): void {
		$this->fixedCols = $fixedCount;
		$this->rows[]    = ['type' => 'header', 'cells' => $cells];
		$this->updateWidths($cells);
	}

	/* Записать строку данных */
	public function writeRow(array $cells, int $rowIndex = 0): void {
		$this->rows[] = ['type' => $rowIndex % 2 === 0 ? 'even' : 'odd', 'cells' => $cells];
		$this->updateWidths($cells);
	}

	/* Зафиксировать первую строку при прокрутке */
	public function freezeFirstRow(): void {
		$this->freeze = true;
	}

	/* Отдать файл в браузер */
	public function output(string $filename): void {
		$data = $this->build();
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
		header('Content-Length: ' . strlen($data));
		header('Cache-Control: no-cache');
		echo $data;
	}

	/* Сохранить в файл */
	public function save(string $path): bool {
		return (bool) file_put_contents($path, $this->build());
	}

	/* ===== Сборка xlsx (ZIP с XML) ===== */
	private function build(): string {
		$tmp = tempnam(sys_get_temp_dir(), 'vp_xlsx_');
		$zip = new ZipArchive();
		$zip->open($tmp, ZipArchive::OVERWRITE);

		$zip->addFromString('[Content_Types].xml',  $this->xmlContentTypes());
		$zip->addFromString('_rels/.rels',           $this->xmlRels());
		$zip->addFromString('xl/workbook.xml',       $this->xmlWorkbook());
		$zip->addFromString('xl/_rels/workbook.xml.rels', $this->xmlWorkbookRels());
		$zip->addFromString('xl/styles.xml',         $this->xmlStyles());
		$zip->addFromString('xl/worksheets/sheet1.xml', $this->xmlSheet());
		$zip->addFromString('xl/sharedStrings.xml',  $this->xmlSharedStrings());

		$zip->close();
		$data = file_get_contents($tmp);
		@unlink($tmp);
		return $data;
	}

	/* ===== XML-части ===== */

	private function xmlContentTypes(): string {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
<Default Extension="xml"  ContentType="application/xml"/>
<Override PartName="/xl/workbook.xml"            ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
<Override PartName="/xl/worksheets/sheet1.xml"   ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
<Override PartName="/xl/sharedStrings.xml"       ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>
<Override PartName="/xl/styles.xml"              ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>';
	}

	private function xmlRels(): string {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>';
	}

	private function xmlWorkbook(): string {
		$name = htmlspecialchars($this->sheetName, ENT_XML1);
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
<sheets><sheet name="' . $name . '" sheetId="1" r:id="rId1"/></sheets>
</workbook>';
	}

	private function xmlWorkbookRels(): string {
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"     Target="worksheets/sheet1.xml"/>
<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>
<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"        Target="styles.xml"/>
</Relationships>';
	}

	private function xmlStyles(): string {
		// 6 стилей: 0=header, 1=fixed-odd, 2=fixed-even, 3=dynamic-odd, 4=dynamic-even, 5=plain
		$hBg  = self::COLOR_HEADER_BG;
		$hFg  = self::COLOR_HEADER_FG;
		$fxBg = self::COLOR_FIXED_BG;
		$dyBg = self::COLOR_DYNAMIC_BG;
		$odd  = self::COLOR_ROW_ODD;
		$even = self::COLOR_ROW_EVEN;

		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
<fonts count="3">
  <font><sz val="11"/><name val="Calibri"/></font>
  <font><b/><sz val="11"/><name val="Calibri"/><color rgb="' . $hFg . '"/></font>
  <font><sz val="11"/><name val="Calibri"/></font>
</fonts>
<fills count="8">
  <fill><patternFill patternType="none"/></fill>
  <fill><patternFill patternType="gray125"/></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $hBg  . '"/></patternFill></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $fxBg . '"/></patternFill></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $dyBg . '"/></patternFill></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $even . '"/></patternFill></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $odd  . '"/></patternFill></fill>
  <fill><patternFill patternType="solid"><fgColor rgb="' . $fxBg . '"/></patternFill></fill>
</fills>
<borders count="2">
  <border><left/><right/><top/><bottom/><diagonal/></border>
  <border>
    <left style="thin"><color rgb="FFD0C8BC"/></left>
    <right style="thin"><color rgb="FFD0C8BC"/></right>
    <top style="thin"><color rgb="FFD0C8BC"/></top>
    <bottom style="thin"><color rgb="FFD0C8BC"/></bottom>
  </border>
</borders>
<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
<cellXfs count="7">
  <xf numFmtId="0" fontId="1" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="center"/></xf>
  <xf numFmtId="0" fontId="0" fillId="6" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
  <xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
  <xf numFmtId="0" fontId="0" fillId="6" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
  <xf numFmtId="0" fontId="0" fillId="4" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
  <xf numFmtId="0" fontId="0" fillId="5" borderId="1" applyFont="1" applyFill="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
  <xf numFmtId="0" fontId="0" fillId="0" borderId="1" applyBorder="1"><alignment wrapText="1" vertical="top"/></xf>
</cellXfs>
</styleSheet>';
	}

	/* Shared strings — все строковые значения выносим сюда */
	private array $strings    = [];
	private array $stringMap  = [];

	private function strIdx(string $s): int {
		if (!isset($this->stringMap[$s])) {
			$this->stringMap[$s] = count($this->strings);
			$this->strings[]     = $s;
		}
		return $this->stringMap[$s];
	}

	private function xmlSharedStrings(): string {
		$count = count($this->strings);
		$xml   = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
		$xml  .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . $count . '">';
		foreach ($this->strings as $s) {
			$xml .= '<si><t xml:space="preserve">' . htmlspecialchars((string)$s, ENT_XML1) . '</t></si>';
		}
		$xml .= '</sst>';
		return $xml;
	}

	private function xmlSheet(): string {
		// Сначала прогоняем строки, чтобы заполнить shared strings
		$rowsXml = '';
		$rowNum  = 1;

		foreach ($this->rows as $rowData) {
			$cells   = $rowData['cells'];
			$type    = $rowData['type'];
			$cellsXml = '';
			$colNum   = 1;

			foreach ($cells as $val) {
				$colLetter = $this->colLetter($colNum);
				$ref       = $colLetter . $rowNum;
				$style     = $this->cellStyle($type, $colNum);

				if (is_numeric($val) && $val !== '') {
					// Числовое значение
					$cellsXml .= '<c r="' . $ref . '" s="' . $style . '"><v>' . htmlspecialchars((string)$val, ENT_XML1) . '</v></c>';
				} else {
					// Строка через sharedStrings
					$idx       = $this->strIdx((string)$val);
					$cellsXml .= '<c r="' . $ref . '" t="s" s="' . $style . '"><v>' . $idx . '</v></c>';
				}
				$colNum++;
			}
			$rowsXml .= '<row r="' . $rowNum . '" ht="20" customHeight="1">' . $cellsXml . '</row>';
			$rowNum++;
		}

		// Ширины столбцов
		$colsXml = '<cols>';
		foreach ($this->colWidths as $i => $w) {
			$n        = $i + 1;
			$w        = min(max($w, 10), 60); // зажимаем в разумные пределы
			$colsXml .= '<col min="' . $n . '" max="' . $n . '" width="' . $w . '" bestFit="1" customWidth="1"/>';
		}
		$colsXml .= '</cols>';

		// Заморозка первой строки
		$freezeXml = '';
		if ($this->freeze) {
			$freezeXml = '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';
		}

		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
' . $freezeXml . '
' . $colsXml . '
<sheetData>' . $rowsXml . '</sheetData>
</worksheet>';
	}

	/* ===== Хелперы ===== */

	// Номер столбца → буква (1=A, 27=AA …)
	private function colLetter(int $n): string {
		$letter = '';
		while ($n > 0) {
			$n--;
			$letter = chr(65 + ($n % 26)) . $letter;
			$n      = intdiv($n, 26);
		}
		return $letter;
	}

	// Определяем стиль ячейки по типу строки и номеру столбца
	private function cellStyle(string $type, int $colNum): int {
		if ($type === 'header') return 0;

		$isEven   = $type === 'even';
		$isFixed  = $colNum <= $this->fixedCols;
		$isDynamic = !$isFixed && $this->fixedCols > 0;

		if ($isFixed)   return $isEven ? 2 : 1;
		if ($isDynamic) return $isEven ? 5 : 4;
		return $isEven ? 2 : 1;
	}

	// Обновляем примерные ширины столбцов
	private function updateWidths(array $cells): void {
		foreach ($cells as $i => $val) {
			$len = mb_strlen((string)$val);
			$this->colWidths[$i] = max($this->colWidths[$i] ?? 10, min($len + 2, 50));
		}
	}
}
