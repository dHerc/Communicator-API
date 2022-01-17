<?php
namespace Communicator\Exceptions\Database;

/**
 * Wyjątek oznaczający, że nie udało się połączyć z bazą danych
 */
use Communicator\Exceptions\InternalException as InternalException;

class NoConnectionException extends InternalException 
{

    public function __construct(string $dbName, string $tableName) {
        parent::__construct("cannot connect with database $dbName table $tableName",2);
    }
}
