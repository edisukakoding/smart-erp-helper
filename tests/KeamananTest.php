<?php

use Esikat\Helper\Keamanan;
use PHPUnit\Framework\TestCase;

class KeamananTest extends TestCase
{
    private Keamanan    $keamanan;
    private string      $kunci  = 'sangatrahasia';
    private string      $algo   = 'HS256';

    protected function setUp(): void
    {
        $this->keamanan = new Keamanan($this->kunci, $this->algo);
    }

    public function testBuatToken()
    {
        $iduser = 123;
        $token  = $this->keamanan->buatToken($iduser, 3600);
        $this->assertNotEmpty($token);
    }

    public function testCekTokenBerhasil()
    {
        $iduser = 123;
        $token  = $this->keamanan->buatToken($iduser, 3600);
        $decode = $this->keamanan->cekToken($token);

        $this->assertIsArray($decode);
        $this->assertEquals($iduser, $decode['sub']);
    }

    public function testCekTokenGagal()
    {
        $tokensalah = 'contoh.token.salah';
        $decoded    = $this->keamanan->cekToken($tokensalah);
        $this->assertFalse($decoded);
    }

}