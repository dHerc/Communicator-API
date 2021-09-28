<?php declare(strict_types=1);
namespace Communicator\Manage;

use Communicator\Data\Boards as Boards;
use Communicator\Utils\Logger as Logger;
use Communicator\Data\Database\DatabaseAccess as DB;
use Communicator\Data\Database\DAOs\BoardsDAO as DAO;
use Communicator\Exceptions\Boards as Exceptions;

class BoardController {
    
    private Logger $logger;
    private DAO $boardsDAO;
    private ?Boards\NoteFactory $factory;
    
    public function __construct(Logger $logger, DB $dbAccess, Boards\NoteFactory $factory = null)
    {
        $this->logger = $logger;
        $this->boardsDAO = new DAO($dbAccess);
        $this->factory = $factory;
    }
    public function getBoard(int $id): Boards\Board
    {
        $board_name = $this->boardsDAO->getBoard($id)["name"];
        $board = new Boards\Board($id,$board_name);
        $chains = $this->boardsDAO->getAllChains($id);
        foreach($chains as $chain)
        {
            $notes = $this->boardsDAO->getAllNotes(intval($chain["id"]));
            if(!isset($notes) || empty($notes))
                $this->logger->log('warning',"chain with id ".$chain['id']." is empty");
            else
            {
                foreach($notes as $place => $note)
                {
                    $new_chain[$place] = $this->factory->getNote($note);
                }
                $board->addChain(intval($chain["placement"]),$new_chain);
                unset($new_chain);
            }
        }
        return $board;
    }
    public function addBoard(string $name): Boards\Board
    {
        $board_id = $this->boardsDAO->addBoard($name);
        $board = new Boards\Board($board_id,$name);
        return $board;
    }
    public function updateBoard(array $board_array): void
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_array["id"]))
            throw new Exceptions\BoardNotFoundException($board_array["id"]);
        $data = array("name" => $board_array["name"]);
        $this->boardsDAO->updateBoard($board_array["id"], $data);
    }
    public function deleteBoard(int $board_id): void
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_id))
            throw new Exceptions\BoardNotFoundException($board_id);
        $this->boardsDAO->deleteBoard($board_id);
    }
    public function getChain(int $board_id, int $chain_pos): int
    {
        return $this->boardsDAO->getChain($board_id, $chain_pos);
    }
    public function addChain(int $board_id): int
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_id))
            throw new Exceptions\BoardNotFoundException($board_id);
        $placement = $this->boardsDAO->getHighestChainPlacement($board_id)+1;
        $chain_id = $this->boardsDAO->addChain($board_id, $placement);
        return $chain_id;
    }
    public function deleteChain(int $board_id, int $chain_pos): void
    {
        $chain_id = $this->boardsDAO->getChain($board_id, $chain_pos);
        $this->boardsDAO->deleteChain($chain_id);
    }
    public function addNote(int $chain_id, string $type, string $message, mixed $content = null, int $placement = null): Boards\Note
    {
        if($placement === null)
            $placement = $this->boardsDAO->getHighestNotePlacement($chain_id)+1;
        $note = $this->factory->createNote($type,$message,$content);
        $new_id = $this->boardsDAO->addNote($chain_id,$type,$message,$content,$placement);
        $note->setId($new_id);
        return $note;
    }
    public function updateNote(array $note_array): Boards\Note
    {
        if(!$this->boardsDAO->checkIfNoteExists($note_array["id"]))
            throw new Exceptions\NoteNotFoundException($note_array["id"]);
        $note = $this->factory->getNote($note_array);
        $this->boardsDAO->updateNote($note->getId(),$note->getData());
        return $note;
    }
    public function deleteNote(int $note_id): void
    {
        if(!$this->boardsDAO->checkIfNoteExists($note_id))
            throw new Exceptions\NoteNotFoundException($note_id);
        $chain_id = $this->boardsDAO->getChainIdByNoteId($note_id);
        $this->boardsDAO->deleteNote($note_id);
        if($this->boardsDAO->notesAmountInChain($chain_id)==0)
            $this->boardsDAO->deleteChain($chain_id);
    }
}
