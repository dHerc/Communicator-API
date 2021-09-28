<?php
namespace Communicator\Data\Boards\Notes;

use Communicator\Data\Boards\Note as Note;

class TextNote extends Note {

    public function __construct(int $id, $type, string $message) {
        parent::__construct($id,$type,$message);
    }
    public function getData(): array
    {
        $data = array(
            "type" => 'text',
            "message" => $this->message
            );
        return $data;
    }
    public function jsonSerialize(): mixed
    {
        $jsonArray = array(
            "id" => $this->getId(),
            "type" => $this->getType(),
            "message" => $this->message
            );
        return $jsonArray;
    }
}
