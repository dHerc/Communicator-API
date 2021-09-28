<?php declare(strict_types=1);
namespace Communicator\Data\Boards;

use Communicator\Exceptions\Boards as Exceptions;
use Communicator\Utils\Common as Commons;

class NoteFactory {
    
    public function createNote($type,string $message, mixed $content = null): Note
    {
        switch($type)
        {
            case 'text':
                if($content === null)
                    return new Notes\TextNote(0,'text',$message);
                else
                    throw new Exceptions\InvalidContentException('text','null',Commons::getTypeOrClass($content));
            default:
                throw new Exceptions\InvalidTypeException();
            
        }
    }
    public function getNote(array $note): Note
    {
        switch($note["type"])
        {
            case 'text':
                if($note["content"] === null)
                    return new Notes\TextNote(intval($note["id"]),'text',$note["message"]);
                else
                    throw new Exceptions\InvalidContentException('text','null',Commons::getTypeOrClass($note["content"]));
            default:
                throw new Exceptions\InvalidTypeException();
            
        }
    }
}
