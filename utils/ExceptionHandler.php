<?php
namespace Communicator\Utils;

use Communicator\Exceptions as Exceptions;

/**
 * Klasa obsługująca wyjątki
 */
abstract class exceptionHandler {

    /**
     * Funkcja obsługująca wyjątek
     * @param \Exception $e Rzucony wyjątek
     * @param Logger|null $logger Obiekt służący do zapisywania logów
     * @return Error Obiekt odpowiedzi związany z odpowiednim błędem
     */
    public static function handleException(\Exception $e, Logger $logger = null): Error
    {
        if($e instanceof Exceptions\ItemNotFoundException)
        {
            return new Error(404, $e->getMessage());
        }
        if($e instanceof Exceptions\UnauthorizedException)
        {
            return new Error(401, "You don't have enough permissions to do that");
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
            ||$e instanceof Exceptions\Boards\InvalidTypeException
            ||$e instanceof Exceptions\Users\UserAlreadyExistsException
            ||$e instanceof Exceptions\Users\InvalidPermissionException
            ||$e instanceof Exceptions\Boards\InvalidFormatException)
        {
            return new Error(400, $e->getMessage());
        }
        return new Error(500, "unknown error happened, please wait a while and try again");
    }
}
