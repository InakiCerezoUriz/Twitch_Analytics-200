<?php

namespace App\Http\Controllers\GetUserById;

use App\Exceptions\EmptyOrInvalidIdException;

class GetUserByIdValidator
{
    public function validateId(?string $id): string
    {
        if (!isset($id)) {
            throw new EmptyOrInvalidIdException();
        }

        $sanitizedId = strip_tags($id);
        $sanitizedId = htmlspecialchars($sanitizedId, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        if (empty($sanitizedId)) {
            throw new EmptyOrInvalidIdException();
        }

        if ((int)$sanitizedId < 1) {
            throw new EmptyOrInvalidIdException();
        }

        return $sanitizedId;
    }
}
