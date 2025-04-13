<?php

use Esikat\Helper\KodeBuilder;
use Esikat\Helper\QueryBuilder;
use PHPUnit\Framework\TestCase;

class KodeBuilderTest extends TestCase
{
    private $mockQueryBuilder;
    private $kodeBuilder;

    protected function setUp(): void
    {
        $this->mockQueryBuilder = $this->createMock(QueryBuilder::class);
        $this->kodeBuilder = new KodeBuilder($this->mockQueryBuilder, 1);
    }

    public function testPreviewNoTransaksiTanpaTransaksiSebelumnya()
    {
        $this->mockQueryBuilder->method('table')->willReturnSelf();

        $this->mockQueryBuilder->method('where')->willReturnSelf();

        $this->mockQueryBuilder->method('first')->willReturnOnConsecutiveCalls(
            ['singkatan' => 'ABC'], // getUsaha
            null                     // getTransaksi (tidak ditemukan)
        );

        $hasil = $this->kodeBuilder->previewNoTransaksi('TRX');

        $this->assertStringContainsString('/' . date('ym') . '/ABC/0001', $hasil);
    }

    public function testBuatNoTransaksiDenganTransaksiSebelumnya()
    {
        $this->mockQueryBuilder->method('table')->willReturnSelf();
        $this->mockQueryBuilder->method('where')->willReturnSelf();

        $this->mockQueryBuilder->method('first')->willReturnOnConsecutiveCalls(
            ['singkatan' => 'DEF'], // getUsaha
            ['noakhir' => '5']      // getTransaksi (ditemukan)
        );

        $this->mockQueryBuilder->expects($this->once())
            ->method('update')
            ->with($this->equalTo(['noakhir' => '0006']));

        $hasil = $this->kodeBuilder->buatNoTransaksi('INV');

        $this->assertStringContainsString('/' . date('ym') . '/DEF/0006', $hasil);
    }
}
