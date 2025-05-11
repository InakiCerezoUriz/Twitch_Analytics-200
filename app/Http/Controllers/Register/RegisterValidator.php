<?php

namespace App\Http\Controllers\Register;

use App\Exceptions\EmptyEmailParameterException;
use App\Exceptions\InvalidArgumentException;

class RegisterValidator
{
    public function validateEmail(?string $email): string
    {
        if (!isset($email)) {
            throw new EmptyEmailParameterException();
        }

        $sanitizedEmail = strip_tags($email);
        $sanitizedEmail = htmlspecialchars($sanitizedEmail, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedEmail)) {
            throw new EmptyEmailParameterException();
        }

        if (!filter_var($sanitizedEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException();
        }

        return $sanitizedEmail;
    }
}
