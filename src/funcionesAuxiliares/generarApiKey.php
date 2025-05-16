<?php

function generateApiKey(): string
{
    $characters       = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $apiKey           = '';

    for ($i = 0; $i < 16; $i++) {
        $apiKey .= $characters[rand(0, $charactersLength - 1)];
    }

    return $apiKey;
}
