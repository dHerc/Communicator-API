<?php declare(strict_types=1);
namespace Communicator\Model\Boards\Notes;

use Communicator\Model\Boards\Note as Note;
use Communicator\Model\Boards\Notes\Helpers\File;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

class ImageNote extends Note {

    public function __construct(int $id, $type, string $message, File $content) {
        parent::__construct($id,$type,$message,$content);
    }
    #[ArrayShape(["type" => "string", "message" => "string", "content" => "string"])]
    public function getData(): array
    {
        return array(
            "type" => 'image',
            "message" => $this->message,
            "content" => $this->content->get()
            );
    }
    #[Pure] #[ArrayShape(["id" => "int", "type" => "string", "message" => "string", "content" => "string"])]
    public function jsonSerialize(): array
    {
        return array(
            "id" => $this->getId(),
            "type" => $this->getType(),
            "message" => $this->message,
            "content" => $this->content->get()
            );
    }
}
