<?php

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testEnkripsiDekripsi()
    {
        $originalUrl    = '1234';
        $encrypted      = URLEncrypt($originalUrl);
        $decrypted      = URLDecrypt($encrypted);
        
        $this->assertNotEquals($originalUrl, $encrypted, 'Nilai terenkripsi tidak boleh sama dengan nilai asli');
        $this->assertEquals($originalUrl, $decrypted, 'Nilai yang didekripsi harus sesuai dengan aslinya');
    }

    public function testUrlAktif()
    {
        $_GET['page']   = URLEncrypt('0001');
        $this->assertEquals('active', urlActive('0001'));
    }

    public function testUrlTidakAktif()
    {
        $_GET['page']   = URLEncrypt('0002');
        $this->assertNotEquals('active', urlActive('0001'));
    }

    public function testDorongDanAmbilTumpukan()
    {
        startPush();
        echo "Test1";
        endPush('tes');

        startPush();
        echo "Test2";
        endPush('tes');

        $result = stack('tes');
        $expected = "Test1\nTest2";

        $this->assertEquals($expected, $result);
    }

    public function testSimpanDanAmbilPesanKilat()
    {
        $_SESSION = [];
        flashMessage('test', 'Ini adalah pesan uji', 'info');
        
        $this->assertArrayHasKey('test', $_SESSION['flash']);
        $this->assertEquals('Ini adalah pesan uji', $_SESSION['flash']['test']['message']);
        $this->assertEquals('info', $_SESSION['flash']['test']['type']);
    }

    public function testAmbilDanHapusPesanKilat()
    {
        $_SESSION = [];
        flashMessage('test', 'Ini adalah pesan uji', 'info');
        $messageHtml = flashMessage('test');
        
        $this->assertStringContainsString('Ini adalah pesan uji', $messageHtml);
        $this->assertStringContainsString('alert-info', $messageHtml);
        $this->assertArrayNotHasKey('test', $_SESSION['flash']);
    }
}
