<?php

namespace App\Repositories;

use App\Models\User;

class TwitchApiRepository
{
    public function getUserFromTwitchApi(string $id, string $token): array
    {
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $id;

        $headers = [
            'Authorization: Bearer ' . $token,  // Token
            'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
            'Content-Type: application/json',
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $res      = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $dataArray = json_decode($response, true);

        if (empty($dataArray['data'])) {
            return [[], $res];
        }
        $user = new User(
            $dataArray['data'][0]
        );

        return [$user, $res];
    }

    public function getApiTokenFromApi(): bool|string
    {
        $api_url = 'https://id.twitch.tv/oauth2/token';

        $post_fields = http_build_query([
            'client_id'     => env('TWITCH_CLIENT_ID'),
            'client_secret' => env('TWITCH_CLIENT_SECRET'),
            'grant_type'    => 'client_credentials',
        ]);

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);

        $response = curl_exec($ch);

        curl_close($ch);

        return $response;
    }
    public function getStreamsFromTwitchApi(string $token): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';

        $headers = [
            "Authorization: Bearer $token",  // Token
            'Client-Id: ' . env('TWITCH_CLIENT_ID'),  // Client ID de la aplicación de twitch
            'Content-Type: application/json',
        ];

        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $res      = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [$response, $res];
    }
    public function getUserData(string $userId, string $token): array
    {
        $headers = [
            "Authorization: Bearer $token",  // Token
            'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',  // Client ID de la aplicación de twitch
            'Content-Type: application/json',
        ];
        $url = 'https://api.twitch.tv/helix/users?id=' . $userId;
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        return $data['data'][0] ?? [];
    }
}
