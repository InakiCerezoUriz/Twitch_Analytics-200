<?php

namespace App\Interfaces;

interface TwitchApiRepositoryInterface
{
    public function getUserFromTwitchApi(string $id, string $token): array;

    public function getStreamsFromTwitchApi(string $token): array;

    public function getEnrichedStreamsFromTwitchApi(string $token, string $limit): array;

    public function getApiTokenFromApi(): bool|string;

    public function getTopGames(string $token): array;

    public function getTopStreamer(array $game, string $token);
}
