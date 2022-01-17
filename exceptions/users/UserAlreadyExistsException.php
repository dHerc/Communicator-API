<?php

namespace Communicator\Exceptions\Users;

/**
 * Wyjątek oznaczający, że użytkownik o danej nazwie już istnieje
 */
class UserAlreadyExistsException extends \InvalidArgumentException
{
    public function __construct($login)
    {
        parent::__construct("User with login $login already exists",2);
    }
}