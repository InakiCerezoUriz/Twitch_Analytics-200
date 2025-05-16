<?php

namespace App\Http\Controllers\GetTopOfTops;

use App\Exceptions\EmptyOrInvalidSinceException;

class GetTopOfTopsValidator
{
    public function validateSince(?string $since): ?string
    {
        if (!isset($since)) {
            return null;
        }

        $sanitizedSince = strip_tags($since);
        $sanitizedSince = htmlspecialchars($sanitizedSince, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedSince)) {
            throw new EmptyOrInvalidSinceException();
        }

        if ((int)$sanitizedSince < 1) {
            throw new EmptyOrInvalidSinceException();
        }

        return $sanitizedSince;
    }
}
