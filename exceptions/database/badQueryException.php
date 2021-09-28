<?php
namespace Communicator\Exceptions\Database;

use Communicator\Exceptions\InternalException as InternalException;

class BadQueryException extends InternalException 
{
    public function __construct(string $query, string $reason) 
    {
        parent::__construct("query: $query cannot be executed, reason: $reason",2);
    }
}
