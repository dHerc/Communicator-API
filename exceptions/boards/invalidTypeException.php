<?php declare(strict_types=1);
namespace Communicator\Exceptions\Boards;

class InvalidTypeException extends \InvalidArgumentException 
{
    public function __construct() 
    {
        parent::__construct("wrong note type provided",2);
    }
}
