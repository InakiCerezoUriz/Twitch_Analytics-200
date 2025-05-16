<?php

function getUserById($id): void
{
    require_once __DIR__ . '/src/funcionesAuxiliares/conseguirToken.php';
    require_once __DIR__ . '/src/funcionesAuxiliares/comprobarExpiracion.php';
    require_once __DIR__ . '/src/funcionesAuxiliares/comprobarAuthorization.php';
    require_once __DIR__ . '/src/funcionesAuxiliares/iniciarCurl.php';

    comprobarAuthorization();

    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    $token      = str_replace('Bearer ', '', $authHeader);

    if (!comprobarExpiracion($token)) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized. Token is invalid or has expired.'], JSON_PRETTY_PRINT);
        return;
    }

    $db  = conectarBBDD();
    $sql = $db->prepare('SELECT * FROM users WHERE id = :id');
    $sql->bindParam(':id', $id, PDO::PARAM_STR);
    $sql->execute();
    $result = $sql->fetch(PDO::FETCH_ASSOC);

    if (empty($result)) {
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $id;
        $token   = conseguirToken();

        $headers = [
        "Authorization: Bearer $token",  // Token
        'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
        'Content-Type: application/json',
        ];

        [$response, $res] = iniciarCurl($api_url, $headers);

        header('Content-Type: application/json');
        $data = json_decode($response, true);
        switch ($res) {
            case 200:
                if (empty($data['data'])) {
                    http_response_code(404);
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'User not found.'], JSON_PRETTY_PRINT);
                } else {
                    http_response_code(200);
                    header('Content-Type: application/json');
                    $user = json_encode($data['data'][0], JSON_PRETTY_PRINT);
                    $stmt = $db->prepare(
                        'INSERT INTO users (
                                        id, login, display_name, type, broadcaster_type, description, 
                                        profile_image_url, offline_image_url, view_count, created_at
                                    ) VALUES (
                                        :id, :login, :display_name, :type, :broadcaster_type, :description, 
                                        :profile_image_url, :offline_image_url, :view_count, :created_at
                                    )'
                    );
                    $stmt->bindParam(':id', $data['data'][0]['id'], PDO::PARAM_STR);
                    $stmt->bindParam(':login', $data['data'][0]['login'], PDO::PARAM_STR);
                    $stmt->bindParam(':display_name', $data['data'][0]['display_name'], PDO::PARAM_STR);
                    $stmt->bindParam(':type', $data['data'][0]['type'], PDO::PARAM_STR);
                    $stmt->bindParam(':broadcaster_type', $data['data'][0]['broadcaster_type'], PDO::PARAM_STR);
                    $stmt->bindParam(':description', $data['data'][0]['description'], PDO::PARAM_STR);
                    $stmt->bindParam(':profile_image_url', $data['data'][0]['profile_image_url'], PDO::PARAM_STR);
                    $stmt->bindParam(':offline_image_url', $data['data'][0]['offline_image_url'], PDO::PARAM_STR);
                    $stmt->bindParam(':view_count', $data['data'][0]['view_count'], PDO::PARAM_INT);
                    $stmt->bindParam(':created_at', $data['data'][0]['created_at'], PDO::PARAM_STR);

                    $stmt->execute();
                    echo $user;
                }
                break;
            case 400:
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode(['error' => "Invalid or missing 'id' parameter."], JSON_PRETTY_PRINT);
                break;
            case 401:
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Unauthorized. Twitch access token is invalid or has expired.'], JSON_PRETTY_PRINT);
                break;
            case 500:
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Internal Server error.'], JSON_PRETTY_PRINT);
                break;
        }
    } else {
        echo json_encode($result, JSON_PRETTY_PRINT);
    }
}
