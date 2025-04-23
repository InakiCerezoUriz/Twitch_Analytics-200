<?php

function getTopOfTops($since): void
{
    require_once 'funcionesAuxiliares/conectarBBDD.php';
    require_once './funcionesAuxiliares/conseguirToken.php';
    require_once './funcionesAuxiliares/comprobarExpiracion.php';

    if (!validarPeticion($since)) {
        return;
    }

    $token   = conseguirToken();
    $headers = getHeaders($token);

    list($res, $response) = manejarSSLVerifyer('https://api.twitch.tv/helix/games/top?first=3', $headers);

    match ($res) {
        200     => manejarExito($response, $since, $headers),
        401     => responderError(401, 'Unauthorized. Twitch access token is invalid or has expired.'),
        404     => responderError(404, 'Not Found. No data available.'),
        500     => responderError(500, 'Internal Server error.'),
        default => responderError(500, 'Unexpected error.'),
    };
}

function validarPeticion($since): bool
{
    comprobarAuthorization();

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        responderError(401, 'Unauthorized. Token is invalid or expired.');
        return false;
    }

    if (!filter_var($since, FILTER_VALIDATE_INT)) {
        responderError(400, 'Bad Request. Invalid or missing parameters.');
        return false;
    }

    return true;
}

function getHeaders(string $token): array
{
    return [
        "Authorization: Bearer $token",
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
        'Content-Type: application/json',
    ];
}

function manejarExito(string $response, int $since, array $headers): void
{
    $data = json_decode($response, true);
    $db   = conectarBBDD();

    if (deberActualizarCache($db, $since)) {
        actualizarCache($db, $data, $headers);
    }

    $final = compilarEstadisticas($db);
    responderJson(200, $final);
}

function deberActualizarCache(PDO $db, int $since): bool
{
    $stmt = $db->prepare('SELECT ultima_solicitud FROM cache ORDER BY ultima_solicitud DESC LIMIT 1');
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $ultima_solicitud = strtotime($result['ultima_solicitud'] ?? '0');
    return (time() - $ultima_solicitud) > $since;
}

function actualizarCache(PDO $db, array $data, array $headers): void
{
    $db->exec('DELETE FROM cache');

    foreach ($data['data'] as $game) {
        $game_id   = $game['id'];
        $game_name = $game['name'];

        $videos = obtenerVideosJuego($game_id, $headers);
        insertarVideosEnCache($db, $videos, $game_id, $game_name);
    }
}

function obtenerVideosJuego(string $game_id, array $headers): array
{
    $api_url = "https://api.twitch.tv/helix/videos?game_id=$game_id&first=40&sort=views";

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true)['data'] ?? [];
}

function insertarVideosEnCache(PDO $db, array $videos, string $game_id, string $game_name): void
{
    $stmt = $db->prepare(
        'INSERT INTO cache (
        game_id,
        game_name,
        ultima_solicitud,
        user_name,
        title,
        views,
        duration,
        created_at
    ) VALUES (
        :game_id,
        :game_name,
        :ultima_solicitud,
        :user_name,
        :title,
        :views,
        :duration,
        :created_at
    )'
    );

    foreach ($videos as $video) {
        $stmt->execute([
            ':game_id'          => $game_id,
            ':game_name'        => $game_name,
            ':ultima_solicitud' => date('Y-m-d H:i:s'),
            ':user_name'        => $video['user_name'],
            ':title'            => $video['title'],
            ':views'            => $video['view_count'],
            ':duration'         => $video['duration'],
            ':created_at'       => $video['created_at'],
        ]);
    }
}

function compilarEstadisticas(PDO $db): array
{
    $final   = [];
    $nombres = $db->query('SELECT GAME_NAME, USER_NAME FROM CACHE GROUP BY GAME_NAME, USER_NAME')->fetchAll(PDO::FETCH_ASSOC);

    foreach ($nombres as $nombre) {
        $stmt = $db->prepare('WITH MaxViews AS (
                                    SELECT GAME_ID, GAME_NAME, USER_NAME, TITLE, VIEWS, DURATION, CREATED_AT,
                                           ROW_NUMBER() OVER (PARTITION BY GAME_ID, USER_NAME ORDER BY VIEWS DESC) AS row_num
                                    FROM CACHE)
                                SELECT c.GAME_ID, c.GAME_NAME, c.USER_NAME, COUNT(*) AS TOTAL_VIDEOS, SUM(c.VIEWS) AS TOTAL_VIEWS,
                                       mv.TITLE AS MOST_VIEWED_TITLE, mv.VIEWS AS MOST_VIEWED_VIEWS,
                                       mv.DURATION AS MOST_VIEWED_DURATION, mv.CREATED_AT AS MOST_VIEWED_CREATED_AT
                                FROM CACHE c
                                JOIN MaxViews mv ON c.GAME_ID = mv.GAME_ID AND c.USER_NAME = mv.USER_NAME AND mv.row_num = 1
                                WHERE c.GAME_NAME = :game_name AND c.USER_NAME = :user_name
                                GROUP BY c.GAME_ID, c.GAME_NAME, c.USER_NAME, mv.TITLE, mv.VIEWS, mv.DURATION, mv.CREATED_AT
                                ORDER BY TOTAL_VIEWS DESC');

        $stmt->execute([
            ':game_name' => $nombre['game_name'],
            ':user_name' => $nombre['user_name'],
        ]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $final[] = array_map('strval', $row);
        }
    }

    return $final;
}

function responderJson(int $code, array $data): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
}

function responderError(int $code, string $message): void
{
    http_response_code($code);
    echo json_encode(['error' => $message], JSON_PRETTY_PRINT);
}
