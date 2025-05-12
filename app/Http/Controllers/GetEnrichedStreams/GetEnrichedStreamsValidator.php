<?php

namespace App\Http\Controllers\GetEnrichedStreams;

use App\Exceptions\EmptyOrInvalidLimitException;

class GetEnrichedStreamsValidator
{
    public function validateStream(?string $limit): string
    {
        if (!isset($limit) ||  !is_numeric($limit)) {
            throw new EmptyOrInvalidLimitException();
        }

        $sanitizedLimit = strip_tags($limit);
        $sanitizedLimit = htmlspecialchars($sanitizedLimit, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedLimit)) {
            throw new EmptyOrInvalidLimitException();
        }

        if ((int)$sanitizedLimit < 0 || (int)$sanitizedLimit > 20) {
            throw new EmptyOrInvalidLimitException();
        }

        return $sanitizedLimit;
    }
}