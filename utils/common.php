<?php
namespace Communicator\Utils;

abstract class Common {

    public static function getTypeOrClass(mixed $content): string
    {
        $type = gettype($content);
        if($type == 'object')
            return get_class($content);
        else
            return $type;
    }
}
