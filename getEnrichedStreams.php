<?php

function getEnrichedStreams(): void
{

    require_once './funcionesAuxiliares/conseguirToken.php';
    require_once './funcionesAuxiliares/comprobarExpiracion.php';
    require_once './funcionesAuxiliares/comprobarAuthorization.php';

    comprobarAuthorization();

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        returnError(401, 'Unauthorized. Token is invalid or expired.');
        return;
    }

    $limit = filter_var($_GET['limit'], FILTER_VALIDATE_INT);
    if ($limit === false || $limit <= 0 || $limit > 20) {
        returnError(400, "Invalid 'limit' parameter.");
        return;
    }

    $token   = conseguirToken();
    $headers = buildHeaders($token);

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

    if ($res !== 200) {
        handleError($res);
        return;
    }

    $data  = json_decode($response, true);
    $lista = buildEnrichedStreamList($data['data'], $limit, $headers);

    header('HTTP/1.1 200 OK');
    header('Content-Type: application/json');
    echo json_encode($lista, JSON_PRETTY_PRINT);
}

function buildHeaders(string $token): array
{
    return [
        "Authorization: Bearer $token",
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
        'Content-Type: application/json',
    ];
}

function buildEnrichedStreamList(array $streams, int $limit, array $headers): array
{
    $lista = [];

    for ($i = 0; $i < $limit && isset($streams[$i]); $i++) {
        $user_id  = $streams[$i]['user_id'];
        $userData = getUserData($user_id, $headers);

        $lista[] = [
            'stream_id'         => $streams[$i]['id'],
            'user_id'           => $user_id,
            'user_name'         => $streams[$i]['user_name'],
            'viewer_count'      => $streams[$i]['viewer_count'],
            'title'             => $streams[$i]['title'],
            'user_display_name' => $userData['display_name']      ?? null,
            'profile_image_url' => $userData['profile_image_url'] ?? null,
        ];
    }

    return $lista;
}

function getUserData(string $userId, array $headers): array
{
    $url = 'https://api.twitch.tv/helix/users?id=' . $userId;
    $ch  = curl_init($url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($response, true);
    return $data['data'][0] ?? [];
}

function returnError(int $status, string $message): void
{
    header("HTTP/1.1 $status");
    header('Content-Type: application/json');
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
}

function handleError(int $res): void
{
    switch ($res) {
        case 400:
            returnError(400, "Invalid or missing 'limit' parameter.");
            break;
        case 401:
            returnError(401, 'Unauthorized. Twitch access token is invalid or has expired.');
            break;
        default:
            returnError(500, 'Internal Server Error.');
            break;
    }
}
