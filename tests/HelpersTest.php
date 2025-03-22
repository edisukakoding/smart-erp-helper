<?php

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function testEnkripsiDekripsi()
    {
        $originalUrl    = '1234';
        $encrypted      = URLEncryp($originalUrl);
        $decrypted      = URLDecrypt($encrypted);
        
        $this->assertNotEquals($originalUrl, $encrypted, 'Nilai terenkripsi tidak boleh sama dengan nilai asli');
        $this->assertEquals($originalUrl, $decrypted, 'Nilai yang didekripsi harus sesuai dengan aslinya');
    }

    public function testUrlAktif()
    {
        $_GET['page']   = URLEncryp('0001');
        $this->assertEquals('active', urlActive('0001'));
    }

    public function testUrlTidakAktif()
    {
        $_GET['page']   = URLEncryp('0002');
        $this->assertNotEquals('active', urlActive('0001'));
    }
}
