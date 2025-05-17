<?php

function conseguirToken()
{
    $db   = conectarBBDD();
    $stmt = $db->prepare('SELECT * FROM token_twitch LIMIT 1');
    $stmt->execute();
    $result           = $stmt->fetch(PDO::FETCH_ASSOC);
    $ultima_solicitud = strtotime($result['expiracion']);
    $current_time     = time();
    if (isset($result['token']) && $ultima_solicitud > $current_time) {
        return $result['token'];
    }

    $api_url       = 'https://id.twitch.tv/oauth2/token';
    $client_id     = 'pdp08hcdlqz3u2l18wz5eeu6kyll93';
    $client_secret = 'yzefb8wctntjt757lhvp6atbx3hu9k';

    // Datos a enviar en la petición POST
    $post_fields = http_build_query([
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'grant_type'    => 'client_credentials',
    ]);


    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

    if ($_SERVER['SERVER_NAME'] == 'localhost') {
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    }

    $response = curl_exec($ch);
    curl_close($ch);

    // Decodificar la respuesta JSON
    $data = json_decode($response, true);
    // Retornar el token obtenido o un mensaje de error
    if (isset($data['access_token'])) {
        if (!empty($result)) {
            $stmt = $db->prepare('UPDATE token_twitch SET token = :token, expiracion = :expiracion');
        } else {
            $stmt = $db->prepare('INSERT INTO token_twitch (token, expiracion) VALUES (:token, :expiracion)');
        }
        $stmt->bindValue(':token', $data['access_token'], PDO::PARAM_STR);
        $stmt->bindValue(':expiracion', date('Y-m-d H:i:s', strtotime('+60 days')), PDO::PARAM_STR);
        $stmt->execute();

        return $data['access_token'];
    } else {
        return 'Error al obtener el token: ' . $response;
    }
}
