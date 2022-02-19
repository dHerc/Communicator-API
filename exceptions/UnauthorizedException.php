<?php

namespace Communicator\Exceptions;

/**
 * Grupa wyjątków oznaczająca, że użytkownik nie posiada wymaganych uprawnień
 */
class UnauthorizedException extends \Exception
{
    public function __construct()
    {
        parent::__construct("you don't have enough permission to do this",2);
    }
}