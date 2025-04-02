<?php

namespace Esikat\Helper;

use Dotenv\Dotenv;

class URL
{
    /**
     * Mengambil kunci enkripsi dari environment variable.
     *
     * @return string Kunci enkripsi sepanjang 8 karakter.
     */
    private static function ambilKunci(): string
    {
        if (file_exists(__DIR__ . '/../.env')) {
            $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
            $dotenv->load();
        }
        
        $key = $_ENV['MODULE_KEY'] ?? 'password';
        return substr(hash('sha256', $key, true), 0, 8);
    }

    /**
     * Mengenkripsi URL menggunakan XOR dengan kunci enkripsi.
     *
     * @param string $url URL yang akan dienkripsi.
     *
     * @return string URL yang sudah dienkripsi dalam format Base64.
     *
     * @example
     * // Contoh penggunaan:
     * $encrypted = URL::enkripsi('https://example.com');
     */
    public static function enkripsi(string $url): string
    {
        $key    = self::ambilKunci();
        $token  = $url ^ $key;
        return rtrim(strtr(base64_encode($token), '+/', '-_'), '=');
    }

    /**
     * Mendekripsi URL yang telah dienkripsi dengan fungsi enkripsi().
     *
     * @param string $encryptedUrl URL terenkripsi dalam format Base64.
     *
     * @return string URL asli setelah dekripsi.
     *
     * @example
     * // Contoh penggunaan:
     * $decrypted = URL::dekripsi($token);
     */
    public static function dekripsi(string $token): string
    {
        $key = self::ambilKunci();
        $decoded = base64_decode(strtr($token, '-_', '+/'));
        return $decoded ^ $key;
    }

    /**
     * Mengembalikan 'active'.
     *
     * @param string $encryptedUrl URL terenkripsi dalam format Base64.
     *
     * @return string string active atau ''.
     *
     * @example
     * // Contoh penggunaan:
     * $isaktif = URL::aktif($token);
     */
    public static function aktif(string $token): string
    {
        $page = $_GET['page'] ?? '';
        return $page === self::enkripsi($token) ? 'active' : '';
    }

    /**
     * Fungsi untuk mengambil URL utama.
     *
     * @param string $path      path url.
     *
     * @return string URL lengkap.
     *
     * @example
     * // Contoh penggunaan:
     * $isaktif = URL::aktif($token);
     */
    public static function urlUtama(string $path = '/'): string
    {
        return ($_ENV['BASE_URL'] ?? '') . $path;
    }
}