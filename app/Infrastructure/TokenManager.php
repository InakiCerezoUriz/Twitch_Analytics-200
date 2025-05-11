<?php

namespace App\Infrastructure;

class TokenManager
{
    public function generarToken(): array
    {
        $token      = bin2hex(random_bytes(16));
        $expiracion = date('Y-m-d H:i:s', strtotime('+3 days'));
        return ['token' => $token, 'expiracion' => $expiracion];
    }
}
