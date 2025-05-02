<?php

namespace TwitchAnalytics\funcionesAuxiliares;

function comprobarAuthorization(): void
{
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Authorization header is missing.'], JSON_PRETTY_PRINT);
        exit();
    }
}
