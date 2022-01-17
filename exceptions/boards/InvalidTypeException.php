<?php declare(strict_types=1);
namespace Communicator\Exceptions\Boards;

/**
 * Wyjątek oznaczający, że typ tablicy nie istnieje
 */
class InvalidTypeException extends \InvalidArgumentException 
{
    public function __construct() 
    {
        parent::__construct("wrong note type provided",2);
    }
}
