<?php
namespace Communicator\Exceptions;

use Communicator\Utils\Common as Commons;

class UnserializableObjectException extends InternalException
{

    public function __construct(mixed $object) 
    {
        $type = Commons::getTypeOrClass($object);
        parent::__construct("provided object of type $type is unserializable",2);
    }
}
