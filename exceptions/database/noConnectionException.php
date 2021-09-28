<?php
namespace Communicator\Exceptions\Database;

use Communicator\Exceptions\InternalException as InternalException;

class NoConnectionException extends InternalException 
{

    public function __construct(string $dbName, string $tableName) {
        parent::__construct("cannot connect with database $dbName table $tableName",2);
    }
}
