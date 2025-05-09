<?php

namespace App\Exceptions;

class EmptyParameterException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The email is mandatory');
    }
}
