<?php

use Esikat\Helper\URL;
use PHPUnit\Framework\TestCase;

class URLTest extends TestCase
{
    public function testEnkripsiDekripsi()
    {
        $originalUrl    = '1234';
        $encrypted      = URL::enkripsi($originalUrl);
        $decrypted      = URL::dekripsi($encrypted);
        
        $this->assertNotEquals($originalUrl, $encrypted, 'Nilai terenkripsi tidak boleh sama dengan nilai asli');
        $this->assertEquals($originalUrl, $decrypted, 'Nilai yang didekripsi harus sesuai dengan aslinya');
    }

    public function testUrlAktif()
    {
        $_GET['page']   = URL::enkripsi('0001');
        $this->assertEquals('active', URL::aktif('0001'));
    }

    public function testUrlTidakAktif()
    {
        $_GET['page']   = URL::enkripsi('0002');
        $this->assertNotEquals('active', URL::aktif('0001'));
    }

    public function testUrlUtama()
    {
        $path   = '/test';
        $url    = URL::urlUtama($path);
        $this->assertEquals($path, $url);
    }
}
