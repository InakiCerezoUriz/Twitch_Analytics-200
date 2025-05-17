<?php

function iniciarCurl($api_url, $headers): array
{
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    $response = curl_exec($ch);
    $res      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return [$response, $res];
}
