<?php

use Esikat\Helper\Tampilan;
use Esikat\Helper\URL;

function URLEncrypt($url) 
{
    return URL::enkripsi($url);
}

function URLDecrypt($urlEncrypted) 
{
    return URL::dekripsi($urlEncrypted);
}

function urlActive($code)
{
    return URL::aktif($code);
}

function startPush() 
{
    Tampilan::mulai();
}

function endPush($key) 
{
    Tampilan::dorong($key);
}

function stack($key) 
{
    return Tampilan::tumpukan($key);
}

function flashMessage(string $key, ?string $message = null, string $type = 'success')
{
    return Tampilan::pesanKilat($key, $message, $type);
}

function base_url($path = '/')
{
    return URL::urlUtama($path);
}

function dd(...$vars)
{
    echo '<pre>';
    foreach ($vars as $var) {
        echo json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo "\n\n";
    }
    echo '</pre>';
    exit;
}


function dump(...$vars)
{
    echo '<pre>';
    foreach ($vars as $var) {
        echo json_encode($var, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        echo "\n\n";
    }
    echo '</pre>';
}
