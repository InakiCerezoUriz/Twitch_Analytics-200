<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Exceptions\InvalidLimitException;

class GetEnrichedStreamsValidator
{
    public function validateStream(?string $limit): string
    {
        if (!isset($limit) || !is_numeric($limit)) {
            throw new InvalidLimitException();
        }

        $sanitizedLimit = strip_tags($limit);
        $sanitizedLimit = htmlspecialchars($sanitizedLimit, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedLimit)) {
            throw new InvalidLimitException();
        }

        if ((int)$sanitizedLimit < 0 || (int)$sanitizedLimit > 20) {
            throw new InvalidLimitException();
        }

        return $sanitizedLimit;
    }
}
