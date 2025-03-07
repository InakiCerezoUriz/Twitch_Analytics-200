<?php

function getEnrichedStreams($limit)
{
    require_once './funcionesAuxiliares/conseguirToken.php';
    require_once './funcionesAuxiliares/comprobarExpiracion.php';

    comprobarAuthorization();

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        sendErrorResponse(401, 'Unauthorized. Token is invalid or has expired.');
        return;
    }

    $limit = filter_var($limit, FILTER_VALIDATE_INT);

    if ($limit === false || $limit <= 0 || $limit > 20) {
        sendErrorResponse(400, "Invalid 'limit' parameter.");
        return;
    }

    $api_url = 'https://api.twitch.tv/helix/streams';
    $token   = conseguirToken();
    $headers = getHeaders($token);

    [$res, $response] = iniciarCurl($api_url, $headers);

    handleApiResponse($res, $response, $headers, $limit);
}

function getHeaders($token): array
{
    return [
        "Authorization: Bearer $token",
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
        'Content-Type: application/json',
    ];
}

function sendErrorResponse($statusCode, $message)
{
    header("HTTP/1.1 $statusCode");
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
    exit();
}

function handleApiResponse($res, $response, $headers, $limit)
{
    switch ($res) {
        case 200:
            processStreamsResponse($response, $headers, $limit);
            break;
        case 400:
            sendErrorResponse(400, "Invalid or missing 'limit' parameter.");
            break;
        case 401:
            sendErrorResponse(401, 'Unauthorized. Twitch access token is invalid or has expired.');
            break;
        case 500:
            sendErrorResponse(500, 'Internal Server error.');
            break;
    }
}

function processStreamsResponse($response, $headers, $limit)
{
    $lista = [];
    header('HTTP/1.1 200 Ok');
    header('Content-Type: application/json');
    $data = json_decode($response, true);

    for ($i = 0; $i < $limit; $i++) {
        $user_id   = $data['data'][$i]['user_id'];
        $data_user = fetchUserData($user_id, $headers);

        $lista[$i] = [
            'stream_id'         => $data['data'][$i]['id'],
            'user_id'           => $user_id,
            'user_name'         => $data['data'][$i]['user_name'],
            'viwer_count'       => $data['data'][$i]['viewer_count'],
            'title'             => $data['data'][$i]['title'],
            'user_display_name' => $data_user['data'][0]['display_name'],
            'profile_image_url' => $data_user['data'][0]['profile_image_url'],
        ];
    }

    echo json_encode($lista, JSON_PRETTY_PRINT);
}

function fetchUserData($user_id, $headers)
{
    $api_url = 'https://api.twitch.tv/helix/users?id=' . $user_id;
    $ch      = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
