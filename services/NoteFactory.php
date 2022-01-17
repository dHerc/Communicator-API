<?php declare(strict_types=1);
namespace Communicator\Services;

use Communicator\Model\Boards\Note;
use Communicator\Model\Boards\Notes\Helpers\Contact;
use Communicator\Model\Boards\Notes\Helpers\File;
use Communicator\Model\Boards\Notes;
use Communicator\Exceptions\Boards as Exceptions;
use Communicator\Model\Boards\Notes\Helpers\Task;
use Communicator\Utils\Common as Commons;

/**
 * Klasa służąca do tworzenia nowych i ładowania istniejących notatek
 */
class NoteFactory {

    /**
     * Funkcja tworząca nową notatkę
     * @param string $type Typ notatki
     * @param string $message Wiadomość w notatce
     * @param mixed $content Dodatkowa zawartość notatki
     * @return Note Nowy obiekt notatki
     * @throws Exceptions\InvalidContentException Ten wyjątek jest rzucany, jeżeli typ zawartości notatki nie zgadza się z typem notatki
     * @throws Exceptions\InvalidTypeException Ten wyjątek jest rzucany, jeżeli typ notatki nie istnieje
     */
    public function createNote(string $type,string $message, mixed $content = null): Note
    {
        switch($type)
        {
            case 'text':
                if($content === null)
                    return new Notes\TextNote(0,'text',$message);
                else
                    throw new Exceptions\InvalidContentException('text','null',Commons::getTypeOrClass($content));
            case 'image':
                if(gettype($content) === 'string')
                    return new Notes\ImageNote(0,'image',$message, new File($content));
                else
                    throw new Exceptions\InvalidContentException('image','string',Commons::getTypeOrClass($content));
            case 'file':
                if(gettype($content) === 'string')
                    return new Notes\FileNote(0,'file',$message, new File($content));
                else
                    throw new Exceptions\InvalidContentException('file','string',Commons::getTypeOrClass($content));
            case 'contact':
                if(gettype($content) === 'string' || gettype($content) === 'array')
                    return new Notes\ContactNote(0,'contact',$message, new Contact($content));
                else
                    throw new Exceptions\InvalidContentException('contact','array or array',Commons::getTypeOrClass($content));
            case 'task':
                if(gettype($content) === 'string' || gettype($content) === 'array')
                    return new Notes\TaskNote(0,'task',$message, new Task($content));
                else
                    throw new Exceptions\InvalidContentException('task','string or array',Commons::getTypeOrClass($content));
            default:
                throw new Exceptions\InvalidTypeException();
            
        }
    }

    /**
     * Funkcja ładująca istniejąca notatkę
     * @param array $note Informacje o notatce
     * @return Note Załadowany obiekt notatki
     * @throws Exceptions\InvalidContentException Ten wyjątek jest rzucany, jeżeli typ zawartości notatki nie zgadza się z typem notatki
     * @throws Exceptions\InvalidTypeException Ten wyjątek jest rzucany, jeżeli typ notatki nie istnieje
     */
    public function getNote(array $note): Note
    {
        switch($note["type"])
        {
            case 'text':
                if($note["content"] === null)
                    return new Notes\TextNote(intval($note["id"]),'text',$note["message"]);
                else
                    throw new Exceptions\InvalidContentException('text','null',Commons::getTypeOrClass($note["content"]));
            case 'image':
                if(gettype($note['content']) === 'string')
                    return new Notes\ImageNote(intval($note["id"]),'image',$note["message"], new File($note['content']));
                else
                    throw new Exceptions\InvalidContentException('image','string',Commons::getTypeOrClass($note["content"]));
            case 'file':
                if(gettype($note['content']) === 'string')
                    return new Notes\FileNote(intval($note["id"]),'file',$note["message"], new File($note['content']));
                else
                    throw new Exceptions\InvalidContentException('file','string',Commons::getTypeOrClass($note["content"]));
            case 'contact':
                if(gettype($note['content']) === 'string' || gettype($note['content']) === 'array')
                    return new Notes\ContactNote(intval($note["id"]),'contact',$note["message"], new Contact($note['content']));
                else
                    throw new Exceptions\InvalidContentException('contact','string or array',Commons::getTypeOrClass($note["content"]));
            case 'task':
                if(gettype($note['content']) === 'string' || gettype($note['content']) === 'array')
                    return new Notes\TaskNote(intval($note["id"]),'task',$note["message"], new Task($note['content']));
                else
                    throw new Exceptions\InvalidContentException('task','string or array',Commons::getTypeOrClass($note["content"]));
            default:
                throw new Exceptions\InvalidTypeException();
            
        }
    }
}
