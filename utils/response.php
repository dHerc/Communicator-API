<?php declare(strict_types=1);
namespace Communicator\Utils;

use Communicator\Exceptions as Exceptions;

class Response 
{
    private int $code;
    private mixed $response;
    private string $content_type;
    
    public function __construct(int $code, mixed $response = null, string $content_type = "application/json") 
    {
        $this->code = $code;
        $this->response = $response;
        $this->content_type = $content_type;
    }
    
    public function send()
    {
        if(isset($this->response))
        {
            $response = json_encode($this->response);
            if($response === false)
                throw new Exceptions\UnserializableObjectException($response);
            echo $response;
        }
        \http_response_code($this->code);
        header("Content-Type: $this->content_type");
    }
}
