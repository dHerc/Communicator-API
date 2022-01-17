<?php declare(strict_types=1);
namespace Communicator\Model\Boards\Notes\Helpers;

use Communicator\Exceptions\Boards\InvalidFormatException;

/**
 * Klasa służąca do przechowywania informacji o notatce zawierającej plik
 */
class File extends Content
{
    /**
     * Link do pliku
     * @var string
     */
    public string $url;

    public function __construct(string $data)
    {
        if(preg_match("/http.?:\/\/.*/",$data))
            $this->url = $data;
        else
            $this->url = $this->postFile($data);
    }

    public function get(): string
    {
        return $this->url;
    }

    /**
     * Funkcja umieszczająca plik na serwerze
     * @param $file string Plik do umieszczenia zakodowany w base64
     * @return string Link do pliku na serwerze
     * @throws InvalidFormatException Ten wyjątek jest rzucany, jeżeli plik ma nieprawidłowy format
     */
    private function postFile(string $file): string
    {
        if(!preg_match("/data:.*\/.*;base64,.*/",$file))
            throw new InvalidFormatException('image');
        $split = explode(',', $file);
        $base64 = $split[1];
        $extension = explode('/',explode(';', $split[0])[0])[1];
        $data = base64_decode($base64);
        $filename = uniqid('',true).".".$extension;
        file_put_contents(dirname(__DIR__,4)."/api/localcdn/".$filename,$data);
        return "http://localhost/localcdn/".$filename;
    }
}