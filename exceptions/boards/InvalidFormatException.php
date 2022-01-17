<?php declare(strict_types=1);
namespace Communicator\Exceptions\Boards;

/**
 * Wyjątek oznaczający, że zawartość ma nieprawidłowy format
 */
class InvalidFormatException extends \InvalidArgumentException
{
    public function __construct(string $type)
    {
        parent::__construct("provided content is wrongly formatted for $type content",2);
    }
}
