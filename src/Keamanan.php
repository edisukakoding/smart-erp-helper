<?php

namespace Esikat\Helper;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Keamanan
{
    private string $kunci;
    private string $algo;

    public function __construct(string $kunci, $algo = 'HS256')
    {
        $this->kunci    = $kunci;
        $this->algo     = $algo;
    }

    /**
     * Fungsi untuk membuat cookie.
     *
     * @param string $nama              Kunci / nama dari cookie.
     * @param string $nilai             Isi dari cookie.
     * @param int    $kadaluarsa        Waktu / lama cookie akan expired (default = 0).
     */
    public function setHttpCookie(string $nama, string $nilai, int $kadaluarsa = 0): void
    {
        $opsi = [
            'path' => '/',
            'secure' => false, // Wajib pakai HTTPS
            'httponly' => true, // Tidak bisa diakses oleh JavaScript
            'samesite' => 'Strict' // Mencegah CSRF
        ];
        if ($kadaluarsa > 0) {
            $opsi['expires'] = time() + $kadaluarsa;
        }
        setcookie($nama, $nilai, $opsi);
    }

    /**
     * Fungsi untuk membuat token JWT.
     *
     * @param int    $iduser            ID unique dari user.
     * @param int    $kadaluarsa        Waktu / lama token berlaku.
     * 
     * @return string token yang telah dibuat.
     */
    public function buatToken(int $iduser, int $kadaluarsa): string
    {
        return JWT::encode([
            'iat' => time(),
            'exp' => time() + $kadaluarsa,
            'sub' => $iduser
        ], $this->kunci, $this->algo);
    }

    /**
     * Fungsi untuk memvalidasi token.
     *
     * @param string $token             token yang telah dibuat.
     * 
     * @return array|bool mengembalikan token yang telah di decode dalam bentuk array atau false jika validasi gagal.
     */
    public function cekToken(string $token): array|bool
    {
        try {
            $decoded = JWT::decode($token, new Key($this->kunci, $this->algo));
            return (array) $decoded;
        } catch (Exception $e) {
            return false;
        }
    }
}