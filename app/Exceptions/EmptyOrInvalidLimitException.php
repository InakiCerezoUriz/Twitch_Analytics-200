<?php

namespace App\Exceptions;

class EmptyOrInvalidLimitException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Invalid or missing 'id' parameter.");
    }
}
