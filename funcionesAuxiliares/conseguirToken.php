<?php

function conseguirToken()
{
    $api_url = 'https://id.twitch.tv/oauth2/token';
    $client_id = "pdp08hcdlqz3u2l18wz5eeu6kyll93";
    $client_secret = "yzefb8wctntjt757lhvp6atbx3hu9k";

    // Datos a enviar en la petición POST
    $post_fields = http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'grant_type' => 'client_credentials'
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
        return $data['access_token'];
    } else {
        return "Error al obtener el token: " . $response;
    }
}
