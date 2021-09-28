<?php declare(strict_types=1);
namespace Communicator\Exceptions\Boards;

class InvalidContentException extends \InvalidArgumentException 
{
    public function __construct(string $type, string $supported_content, string $provided_content)
    {
        parent::__construct("$type note only support $supported_content type content, $provided_content provided",2);
    }
}
