<?php declare(strict_types=1);
namespace Communicator\Utils;

use Communicator\Utils\Response as Response;

class Error extends Response
{
    public function __construct(int $code, string $reason) 
    {
        $response = array("error: " => $reason);
        $this->response = $response;
        parent::__construct($code,$response);
    }
}