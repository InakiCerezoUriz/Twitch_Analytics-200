<?php

namespace App\Interfaces;

interface DataBaseRepositoryInterface
{
    public function getApiKey(string $email): ?string;

    public function updateApiKey(string $apiKey, string $email): void;

    public function insertApiKey(string $email, mixed $apiKey): void;

    public function getTokenFromDataBase(string $email): ?array;

    public function updateUserTokenInDataBase(array $nuevoToken, string $email1): void;

    public function getUserFromDataBase(string $id): ?array;

    public function insertUserInDataBase(array $data): void;

    public function getApiTokenFromDataBase(): array;

    public function updateApiTokenInDataBase(array $data): void;

    public function insertApiTokenInDataBase(array $data): void;

    public function getTokenExpirationDateFromDataBase(string $token): ?int;

    public function getUltimaSolicitud(): int;

    public function getTops(): array;

    public function obtenerInformacionJuego(string $game_name, string $user_name): array;

    public function clearCache(): void;

    public function connect(): ?\PDO;
}
