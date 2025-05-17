<?php

namespace App\Exceptions;

class InvalidLimitException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct("Invalid 'limit' parameter.");
    }
}
