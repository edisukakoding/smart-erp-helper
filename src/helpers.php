<?php

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