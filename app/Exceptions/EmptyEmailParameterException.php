<?php

namespace TwitchAnalytics\Exceptions;

class EmptyEmailParameterException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('The email is mandatory');
    }
}
