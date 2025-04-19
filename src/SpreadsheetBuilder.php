<?php

namespace Esikat\Helper;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetBuilder
{
    protected Spreadsheet $spreadsheet;
    protected $sheet;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        $this->sheet = $this->spreadsheet->getActiveSheet();
    }

    public function build(array $config, array $data): Spreadsheet
    {
        // Header
        foreach ($config as $col) {
            $this->sheet->setCellValue($col['koordinat'] . '1', $col['text']);
        }

        // Data
        $rowIndex = 2;
        foreach ($data as $row) {
            foreach ($config as $col) {
                $value = $row[$col['data']] ?? '';
                $this->sheet->setCellValue($col['koordinat'] . $rowIndex, $value);
            }
            $rowIndex++;
        }

        return $this->spreadsheet;
    }

    public function download(string $filename = 'export.xlsx'): void
    {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($this->spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function saveToFile(string $path): void
    {
        $writer = new Xlsx($this->spreadsheet);
        $writer->save($path);
    }
}
