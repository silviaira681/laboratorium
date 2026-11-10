<?php

function e($text)
{
    return htmlspecialchars(
        $text ?? '',
        ENT_QUOTES,
        'UTF-8'
    );
}


function redirect($url)
{
    header("Location: " . $url);
    exit;
}


function pesan($text)
{
    $_SESSION['pesan'] = $text;
}


function ambilPesan()
{
    if (isset($_SESSION['pesan'])) {

        $pesan = $_SESSION['pesan'];

        unset($_SESSION['pesan']);

        return $pesan;
    }

    return "";
}