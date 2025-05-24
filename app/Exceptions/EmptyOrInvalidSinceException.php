<?php

namespace App\Exceptions;

class EmptyOrInvalidSinceException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Bad Request. Invalid or missing parameters.');
    }
}
