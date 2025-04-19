<?php

use PHPUnit\Framework\TestCase;
use Esikat\Helper\SpreadsheetBuilder;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SpreadsheetBuilderTest extends TestCase
{
    public function testSaveToFileCreatesXlsxFile()
    {
        $config = [
            ['koordinat' => 'A', 'text' => 'NOMOR AJU', 'data' => 'nomorAju'],
        ];

        $data = [
            ['nomorAju' => '123'],
        ];

        $tempFile = tempnam(sys_get_temp_dir(), 'spreadsheet_test_') . '.xlsx';

        $builder = new SpreadsheetBuilder();
        $builder->build($config, $data);
        $builder->saveToFile($tempFile);

        // Cek file ada dan bukan kosong
        $this->assertFileExists($tempFile);
        $this->assertGreaterThan(0, filesize($tempFile));

        // Cek konten dalam file
        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load($tempFile);
        $sheet = $spreadsheet->getActiveSheet();

        $this->assertEquals('NOMOR AJU', $sheet->getCell('A1')->getValue());
        $this->assertEquals('123', $sheet->getCell('A2')->getValue());

        // Hapus file setelah test
        unlink($tempFile);
    }
}
