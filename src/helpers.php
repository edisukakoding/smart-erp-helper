<?php

use Esikat\Helper\URL;
use Esikat\Helper\Lang;
use Esikat\Helper\Tampilan;

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

function trace()
{
    echo '<pre>';
    foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $i => $trace) {
        $file = $trace['file'] ?? '[internal function]';
        $line = $trace['line'] ?? '';
        $function = $trace['function'] ?? '';
        echo "#$i $function() at $file:$line\n";
    }
    echo '</pre>';
    exit;
}

function benchmark($start = null)
{
    if ($start === null) {
        return microtime(true);
    }
    return round((microtime(true) - $start) * 1000, 2) . ' ms';
}

if (!function_exists('__')) {
    function __(string $key, string $default = ''): string
    {
        return Lang::get($key, $default);
    }
}