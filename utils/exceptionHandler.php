<?php
namespace Communicator\Utils;

use Communicator\Exceptions as Exceptions;

abstract class exceptionHandler {

    public static function handleException(\Exception $e, Logger $logger = null): Error
    {
        if($e instanceof Exceptions\ItemNotFoundException)
        {
            return new Error(404, $e->getMessage());
        }
        if($e instanceof Exceptions\InternalException)
        {
            if($logger)
                $logger->log(
                    'emergency',
                    "internal server error",
                    array("exception" => $e)
                    );
            return new Error(500, "server side error happend, please wait a while and try again");
        }
        if($e instanceof Exceptions\Boards\InvalidContentException
            ||$e instanceof Exceptions\Boards\InvalidTypeException)
        {
            return new Error(400, $e->getMessage());
        }
        return new Error(500, "unknown error happened, please wait a while and try again");
    }
}
