<?php

namespace App\Repositories;

use App\Interfaces\TwitchApiRepositoryInterface;
use App\Models\EnrichedStream;
use App\Models\Stream;
use App\Models\TopStreamer;

class TwitchApiRepository implements TwitchApiRepositoryInterface
{
    public function getUserFromTwitchApi(string $id, string $token): array
    {
        $api_url = 'https://api.twitch.tv/helix/users?id=' . $id;

        return $this->fetchFromTwitch($api_url, $this->getHeaders($token));
    }

    public function getStreamsFromTwitchApi(string $token): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';

        $streams               = [];
        [$response, $httpCode] = $this->fetchFromTwitch($api_url, $this->getHeaders($token));

        if ($httpCode != 200) {
            return [$response, $httpCode];
        }

        $data = json_decode($response, true);
        foreach ($data['data'] as $streamData) {
            $streams[] = new Stream($streamData['title'], $streamData['user_name']);
        }

        return [$streams, 200];
    }

    public function getEnrichedStreamsFromTwitchApi(string $token, string $limit): array
    {
        $api_url = 'https://api.twitch.tv/helix/streams';

        [$result, $httpCode] = $this->fetchFromTwitch($api_url, $this->getHeaders($token));

        if ($httpCode != 200) {
            return [$result, $httpCode];
        }

        $result = json_decode($result, true);

        $result['data'] = array_slice($result['data'], 0, $limit);

        $enrichedStreams = [];
        foreach ($result['data'] as $stream) {
            $enrichedStream = new EnrichedStream();

            $enrichedStream->setStreamInfo($stream);

            $user_id = $stream['user_id'];

            [$result, $httpCode] = $this->getUserFromTwitchApi($user_id, $token);

            if ($httpCode != 200) {
                return [$result, $httpCode];
            }
            $user_data = json_decode($result, true);
            $enrichedStream->setUserInfo($user_data['data'][0]);

            $enrichedStreams[] = $enrichedStream->getEnrichedStream();
        }

        return [$enrichedStreams, 200];
    }

    public function getApiTokenFromApi(): bool|string
    {
        $api_url     = 'https://id.twitch.tv/oauth2/token';
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

    public function getTopGames(string $token): array
    {
        $url = 'https://api.twitch.tv/helix/games/top?first=3';
        return $this->fetchFromTwitch($url, $this->getHeaders($token));
    }

    public function getTopStreamer(array $game, string $token): TopStreamer
    {
        $url = 'https://api.twitch.tv/helix/videos?game_id=' . $game['id'] . '&first=40&sort=views';

        $streams = $this->fetchFromTwitch($url, $this->getHeaders($token));
        $streams = json_decode($streams[0], true)['data'];

        $topStreamerStreams = array_filter($streams, fn ($stream) => $stream['user_name'] === $streams[0]['user_name']);
        $totalViews         = array_sum(array_column($topStreamerStreams, 'view_count'));

        return new TopStreamer($game, $streams[0], count($topStreamerStreams), $totalViews);
    }

    private function getHeaders(string $token): array
    {
        return [
            "Authorization: Bearer $token",
            'Client-Id: pdp08hcdlqz3u2l18wz5eeu6kyll93',
            'Content-Type: application/json',
        ];
    }

    private function fetchFromTwitch(string $url, array $headers): ?array
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return [$response, $httpCode];
    }
}
