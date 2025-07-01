<?php

use PHPUnit\Framework\TestCase;
use Esikat\Helper\Lang;

class LangTest extends TestCase
{
    protected function setUp(): void
    {
        // Path ke folder lang (disesuaikan dengan struktur project kamu)
        $langPath = realpath(__DIR__ . '/../lang');
        Lang::setLangPath($langPath);

        // Reset session/lang sebelum init
        $_SESSION = [];
        $_GET = [];
        Lang::init();
    }

    public function testDefaultLanguageLoadsCorrectly()
    {
        // Karena default 'id'
        Lang::init();
        $this->assertSame('Selamat Datang', Lang::get('title'));
    }

    public function testFallbackToDefaultLanguage()
    {
        $_GET['lang'] = 'en'; // en tidak punya nested.key
        Lang::init();

        $this->assertSame('Nilai Terbenam', Lang::get('nested.key'));
    }

    public function testCurrentLanguage()
    {
        $_GET['lang'] = 'id';
        Lang::init();

        $this->assertSame('id', Lang::current());
    }

    public function testReturnDefaultIfKeyNotFoundAnywhere()
    {
        $_GET['lang'] = 'en';
        Lang::init();

        $this->assertSame('default value', Lang::get('tidak.ada', 'default value'));
    }

    public function testAvailableLanguagesDetectsAllJson()
    {
        $langs = Lang::available();

        $this->assertContains('id', $langs);
        $this->assertContains('en', $langs);
    }

    public function testHelperFunctionReturnsTranslation()
    {
        $_GET['lang'] = 'id';
        Lang::init();

        $this->assertSame('Selamat Datang', __('title'));
    }
}
