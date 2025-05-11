<?php

namespace App\Exceptions;

class EmptyApiKeyException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The api_key is mandatory');
    }
}
