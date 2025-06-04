<?php

namespace TwitchAnalytics\Http\Controllers\Token;

use TwitchAnalytics\Exceptions\EmptyApiKeyException;
use TwitchAnalytics\Exceptions\EmptyEmailException;
use TwitchAnalytics\Exceptions\InvalidArgumentException;

class TokenValidator
{
    public function validateEmail(?string $email): string
    {
        if (!isset($email)) {
            throw new EmptyEmailException();
        }

        $sanitizedEmail = strip_tags($email);
        $sanitizedEmail = htmlspecialchars($sanitizedEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedEmail)) {
            throw new EmptyEmailException();
        }

        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException();
        }

        return $sanitizedEmail;
    }

    public function validateApiKey(?string $apiKey): string
    {
        if (!isset($apiKey)) {
            throw new EmptyApiKeyException();
        }

        $sanitizedApiKey = strip_tags($apiKey);
        $sanitizedApiKey = htmlspecialchars($sanitizedApiKey, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedApiKey)) {
            throw new EmptyApiKeyException();
        }

        return $sanitizedApiKey;
    }
}
