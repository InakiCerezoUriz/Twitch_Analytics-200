<?php

namespace App\Exceptions;

class EmptyOrInvalidIdException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Invalid or missing 'id' parameter.");
    }
}
