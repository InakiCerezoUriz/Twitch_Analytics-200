<?php

namespace App\Infrastructure;

use App\Interfaces\DataBaseRepositoryInterface;
use App\Repositories\TwitchApiRepository;

class TokenManager
{
    public function __construct(
        private readonly DataBaseRepositoryInterface $dataBaseRepository,
        private readonly TwitchApiRepository $twitchApiRepository
    ) {
    }

    public function generarToken(): array
    {
        $token      = bin2hex(random_bytes(16));
        $expiracion = date('Y-m-d H:i:s', strtotime('+3 days'));
        return ['token' => $token, 'expiracion' => $expiracion];
    }

    public function getToken(): string
    {
        [$fechaExpiracion, $token] = $this->dataBaseRepository->getApiTokenFromDataBase();

        if (isset($token) && $fechaExpiracion > time()) {
            return $token;
        }
        $response = $this->twitchApiRepository->getApiTokenFromApi();

        $data = json_decode($response, true);

        if (!empty($token)) {
            $this->dataBaseRepository->updateApiTokenInDataBase($data);
        } else {
            $this->dataBaseRepository->insertApiTokenInDataBase($data);
        }

        return $data['access_token'];
    }

    public function tokenActive(string $token): bool
    {
        $fechaExpiracion = $this->dataBaseRepository->getTokenExpirationDateFromDataBase($token);

        if (!$fechaExpiracion) {
            return false;
        }

        return ($fechaExpiracion > time());
    }
}
