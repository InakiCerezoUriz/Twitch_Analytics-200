<?php

namespace App\Repositories;

class TwitchApiRepository
{
    public function getUserFromTwitchApi(string $id): array
    {
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $id;
        $token   = conseguirToken();

        $headers = [
            "Authorization: Bearer $token",  // Token
            'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
            'Content-Type: application/json',
        ];

        [$response, $res] = iniciarCurl($api_url, $headers);
        return [$response, $res];
    }
}
