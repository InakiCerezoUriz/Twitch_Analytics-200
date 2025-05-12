<?php

function getStreams(): void
{

    require_once __DIR__ . '/src/funcionesAuxiliares/conseguirToken.php';
    require_once __DIR__ . '/src/funcionesAuxiliares/comprobarExpiracion.php';

    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Authorization header is missing.'], JSON_PRETTY_PRINT);
        exit();
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized. Token is invalid or expired.'], JSON_PRETTY_PRINT);
        return;
    }

    $token = conseguirToken();

    $headers = [
        "Authorization: Bearer $token",  // Token
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
        'Content-Type: application/json',
    ];

    $api_url = 'https://api.twitch.tv/helix/streams';

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

    header('Content-Type: application/json; charset=UTF-8');

    switch ($res) {
        case 200:
            http_response_code(200);
            $data  = json_decode($response, true);
            $lista = [];
            for ($i = 0; $i < count($data['data']); $i++) {
                $title     = $data['data'][$i]['title'];
                $user_name = $data['data'][$i]['user_name'];
                $lista[$i] = [
                    'title'     => $title,
                    'user_name' => $user_name,
                ];
            }
            $lista = json_encode($lista, JSON_PRETTY_PRINT);
            print($lista);
            break;
        case 401:
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized. Twitch access token is invalid or has expired.'], JSON_PRETTY_PRINT);
            break;
        case 500:
            http_response_code(500);
            echo json_encode(['error' => 'Internal Server error.'], JSON_PRETTY_PRINT);
            break;
    }
}
