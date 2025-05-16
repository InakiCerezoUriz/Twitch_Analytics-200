<?php

namespace App\Repositories;

class TwitchApiRepository
{
    private string $clientId;

    public function __construct()
    {
        $this->clientId = env('TWITCH_CLIENT_ID');
    }

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
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $userId;
        $result  = $this->fetchFromTwitch($api_url, $this->getHeaders($token));
        return $result[0] ?? [];
    }

    public function getTopGames(int $limit, string $token): array
    {
        $url = "https://api.twitch.tv/helix/games/top?first=$limit";
        return $this->fetchFromTwitch($url, $this->getHeaders($token));
    }

    private function getHeaders(string $token): array
    {
        return [
            "Authorization: Bearer $token",
            "Client-Id: {$this->clientId}",
            'Content-Type: application/json',
        ];
    }

    private function fetchFromTwitch(string $url, array $headers): ?array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_exec($ch), CURLINFO_HTTP_CODE);
      
        curl_close($ch);
      
        return [$response, $httoCode];
    }
}
