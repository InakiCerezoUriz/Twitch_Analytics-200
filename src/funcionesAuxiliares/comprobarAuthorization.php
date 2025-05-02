<?php

function comprobarAuthorization(): void
{
    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authorization header is missing.'], JSON_PRETTY_PRINT);
        exit();
    }
}
