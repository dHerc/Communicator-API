<?php
namespace Communicator\Data\Database\DAOs;

use Communicator\Data\Database\DatabaseAccess as DB;
use Communicator\Exceptions\Boards as Exceptions;

class BoardsDAO {

    private DB $dbAccess;
    
    public function __construct(DB $dbAccess)
    {
        $this->dbAccess = $dbAccess;
    }
    public function getBoard(int $boardId): array
    {
        $board = $this->dbAccess->getOneBy("boards",array("id" => $boardId));
        if($board === null)
        throw new Exceptions\BoardNotFoundException($boardId);
        return $board;
    }
    public function getChain(int $board_id, int $chain_pos): int
    {
        $chain = $this->dbAccess->executeQuery(
            "SELECT id FROM chains 
            WHERE `board_id`=$board_id 
            AND `placement`=$chain_pos 
            ORDER BY `placement`"
        );
        if(count($chain)==0)
            throw new Exceptions\ChainNotFoundException($board_id, $chain_pos);
        return $chain[0]["id"];
    }
    public function getAllChains(int $board_id): array
    {
        return $this->dbAccess->executeQuery(
            "SELECT * FROM chains 
            WHERE `board_id`=$board_id 
            ORDER BY `placement`"
        );
    }
    public function getAllNotes(int $chain_id): array
    {
        return $this->dbAccess->executeQuery(
            "SELECT * FROM notes 
            WHERE `chain_id`=$chain_id 
            ORDER BY `placement`"
        );
    }
    public function addBoard(string $name): int
    {
        return $this->dbAccess->add(
            "boards",
            array("name" => $name)
            );
    }
    public function getHighestNotePlacement(int $chain_id): int
    {
        $highest_placement = $this->dbAccess->executeQuery(
            "SELECT MAX(placement) as top_place 
            FROM notes
            WHERE `chain_id`=$chain_id
            GROUP BY chain_id"
        );
        if(count($highest_placement)==0)
            throw new Exceptions\ChainNotFoundException($chain_id);
        return $highest_placement[0]["top_place"];
    }
    public function addNote(int $chain_id, string $type, string $message, mixed $content, int $placement): int
    {
        $values = array("chain_id" => $chain_id,
                        "type" => $type,
                        "message" => $message,
                        "placement" => $placement);
        if($content)
            $values["content"] = $content;
        $id = $this->dbAccess->add("notes", $values);
        return $id;
    }
    public function getHighestChainPlacement(int $board_id): int
    {
        $highest_placement = $this->dbAccess->executeQuery(
            "SELECT MAX(placement) as top_place 
            FROM chains
            WHERE `board_id`=$board_id
            GROUP BY board_id"
        );
        if(count($highest_placement)==0)
            return 0;
        return $highest_placement[0]["top_place"];
    }
    public function addChain(int $board_id, int $placement): int
    {
        $values = array("board_id" => $board_id,
                        "placement" => $placement);
        return $this->dbAccess->add("chains",$values);
    }
    public function updateNote(int $id, array $data): void
    {
        $this->dbAccess->edit("notes", $data, array("id" => $id));
    }
    public function updateBoard(int $id, array $data): void
    {
        $this->dbAccess->edit("boards", $data, array("id" => $id));
    }
    public function checkIfBoardExists(int $id): bool
    {
        return boolval($this->dbAccess->exists("boards",array("id" => $id)));
    }
    public function checkIfNoteExists(int $id): bool
    {
        return boolval($this->dbAccess->exists("notes",array("id" => $id)));
    }
    public function deleteBoard(int $board_id): void
    {
        $this->dbAccess->delete("boards", array("id" => $board_id));
    }
    public function deleteChain(int $chain_id): void
    {
        $this->dbAccess->delete("chains", array("id" => $chain_id));
    }
    public function deleteNote(int $note_id): void
    {
        $this->dbAccess->delete("notes", array("id" => $note_id));
    }
    public function getChainIdByNoteId($note_id): int
    {
        return $this->dbAccess->executeQuery(
            "SELECT chain_id as id FROM notes 
            WHERE id = $note_id"
        )[0]["id"];
    }
    public function notesAmountInChain($chain_id): int
    {
        return $this->dbAccess->executeQuery(
            "SELECT COUNT(id) as count FROM notes 
            WHERE chain_id = $chain_id"
        )[0]["count"];
    }
}
