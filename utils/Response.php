<?php declare(strict_types=1);
namespace Communicator\Utils;

use Communicator\Exceptions as Exceptions;

/**
 * Klasa zawierająca informacje o odpowiedzi
 */
class Response 
{
    /**
     * Kod odpowiedzi
     * @var int
     */
    private int $code;
    /**
     * Ciało odpowiedzi
     * @var mixed|null
     */
    private mixed $response;
    /**
     * Typ ciała odpowiedzi
     * @var string
     */
    private string $content_type;
    
    public function __construct(int $code, mixed $response = null, string $content_type = "application/json") 
    {
        $this->code = $code;
        $this->response = $response;
        $this->content_type = $content_type;
    }

    /**
     * Funkcja wysyłająca odpowiedź
     * @throws Exceptions\UnserializableObjectException Ten wyjątek jest rzucany, jeżeli serializacja obiektu nie jest możliwa
     */
    public function send()
    {
        if(isset($this->response))
        {
            $response = json_encode($this->response);
            if($response === false)
                throw new Exceptions\UnserializableObjectException($response);
            echo $response;
        }
        else
            echo "{}";
        \http_response_code($this->code);
        header("Content-Type: $this->content_type");
    }
}
