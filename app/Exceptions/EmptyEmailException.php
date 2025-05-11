<?php

namespace App\Exceptions;

class EmptyEmailException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The email is mandatory');
    }
}
