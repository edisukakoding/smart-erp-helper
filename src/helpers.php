<?php

use Esikat\Helper\Tampilan;
use Esikat\Helper\URL;

function URLEncryp($url) 
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