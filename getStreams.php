<?php

function getStreams(): void
{
    require_once './funcionesAuxiliares/conseguirToken.php';
    require_once './funcionesAuxiliares/comprobarExpiracion.php';

    if (!isset($_SERVER['HTTP_AUTHORIZATION'])) {
        header('HTTP/1.1 400 Bad Request');
        echo json_encode(['error' => 'Authorization header is missing.'], JSON_PRETTY_PRINT);
        exit();
    }

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        header('HTTP/1.1 401 Unauthorized');
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

    list($res, $response) = manejarSSLVerifyer($api_url, $headers);


    header('Content-Type: application/json; Charset: UTF-8');

    switch ($res) {
        case 200:
            header('HTTP/1.1 200 Ok');
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
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error' => 'Unauthorized. Twitch access token is invalid or has expired.'], JSON_PRETTY_PRINT);
            break;
        case 500:
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['error' => 'Internal Server error.'], JSON_PRETTY_PRINT);
            break;
    }
}
