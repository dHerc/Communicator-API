<?php

namespace Communicator\Exceptions\Users;

/**
 * Wyjątek oznaczający, że dane uprawnienie nie jest poprawne
 */
class InvalidPermissionException extends \InvalidArgumentException
{
    public function __construct($permission)
    {
        parent::__construct("Permission $permission is not allowed",2);
    }
}