<?php

namespace Communicator\Exceptions\Users;

/**
 * Wyjątek oznaczający, że użytkownik nie został znaleziony
 */
class UserNotFoundException extends \Communicator\Exceptions\ItemNotFoundException
{
    public function __construct()
    {
        parent::__construct("User with provided credentials does not exist",2);
    }
}