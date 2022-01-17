<?php declare(strict_types=1);
namespace Communicator\Model\Boards\Notes;

use Communicator\Model\Boards\Note as Note;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class TextNote extends Note {

    public function __construct(int $id, $type, string $message) {
        parent::__construct($id,$type,$message);
    }
    #[ArrayShape(["type" => "string", "message" => "string"])]
    public function getData(): array
    {
        $data = array(
            "type" => 'text',
            "message" => $this->message
            );
        return $data;
    }
    #[Pure] #[ArrayShape(["id" => "int", "type" => "string", "message" => "string"])]
    public function jsonSerialize(): array
    {
        return array(
            "id" => $this->getId(),
            "type" => $this->getType(),
            "message" => $this->message
            );
    }
}
