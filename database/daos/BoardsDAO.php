<?php declare(strict_types=1);
namespace Communicator\Database\DAOs;

use Communicator\Database\DatabaseAccess as DB;
use Communicator\Exceptions\Boards as Exceptions;
use Communicator\Exceptions\Database\BadQueryException;

/**
 * Klasa pozwalająca na operacji na bazie danych związane z tablicami
 */
class BoardsDAO {

    /**
     * Obiekt pozwalający na dostęp do bazy danych
     * @var DB
     */
    private DB $dbAccess;
    
    public function __construct(DB $dbAccess)
    {
        $this->dbAccess = $dbAccess;
    }

    /**
     * @param int $boardId
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getBoard(int $boardId): array
    {
        $board = $this->dbAccess->getOneBy("boards",array("id" => $boardId));
        if($board === null)
        throw new Exceptions\BoardNotFoundException($boardId);
        return $board;
    }

    /**
     * @param int $board_id
     * @param int $chain_pos
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
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
        return intval($chain[0]["id"]);
    }

    /**
     * @param int $board_id
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getAllChains(int $board_id): array
    {
        return $this->dbAccess->executeQuery(
            "SELECT * FROM chains 
            WHERE `board_id`=$board_id 
            ORDER BY `placement`"
        );
    }

    /**
     * @param int $chain_id
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getAllNotes(int $chain_id): array
    {
        return $this->dbAccess->executeQuery(
            "SELECT * FROM notes 
            WHERE `chain_id`=$chain_id 
            ORDER BY `placement`"
        );
    }

    /**
     * @param string $name
     * @param int $groupId
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addBoard(string $name, int $groupId): int
    {
        return $this->dbAccess->add(
            "boards",
            ["name" => $name, "group_id" => $groupId]);
    }

    /**
     * @param int $chain_id
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getHighestNotePlacement(int $chain_id): int
    {
        $highest_placement = $this->dbAccess->executeQuery(
            "SELECT MAX(placement) as top_place 
            FROM notes
            WHERE `chain_id`=$chain_id
            GROUP BY chain_id"
        );
        if(count($highest_placement)==0)
            return 0;
        return intval($highest_placement[0]["top_place"]);
    }

    /**
     * @param int $chain_id
     * @param string $type
     * @param string $message
     * @param mixed $content
     * @param int $placement
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addNote(int $chain_id, string $type, string $message, mixed $content, int $placement): int
    {
        $values = array("chain_id" => $chain_id,
                        "type" => $type,
                        "message" => $message,
                        "placement" => $placement);
        if($content)
            $values["content"] = $content->get();
        $id = $this->dbAccess->add("notes", $values);
        return $id;
    }

    /**
     * @param int $board_id
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
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
        return intval($highest_placement[0]["top_place"]);
    }

    /**
     * @param int $board_id
     * @param int $placement
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addChain(int $board_id, int $placement): int
    {
        $values = array("board_id" => $board_id,
                        "placement" => $placement);
        return $this->dbAccess->add("chains",$values);
    }

    /**
     * @param int $id
     * @param array $data
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function updateNote(int $id, array $data): void
    {
        $this->dbAccess->edit("notes", $data, array("id" => $id));
    }

    /**
     * @param int $id
     * @param array $data
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function updateBoard(int $id, array $data): void
    {
        $this->dbAccess->edit("boards", $data, array("id" => $id));
    }

    /**
     * @param int $id
     * @return bool
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function checkIfBoardExists(int $id): bool
    {
        return boolval($this->dbAccess->exists("boards",array("id" => $id)));
    }

    /**
     * @param int $id
     * @return bool
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function checkIfNoteExists(int $id): bool
    {
        return boolval($this->dbAccess->exists("notes",array("id" => $id)));
    }

    /**
     * @param int $board_id
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function deleteBoard(int $board_id): void
    {
        $this->dbAccess->delete("boards", array("id" => $board_id));
    }

    /**
     * @param int $chain_id
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function deleteChain(int $chain_id): void
    {
        $this->dbAccess->delete("chains", array("id" => $chain_id));
    }

    /**
     * @param int $note_id
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function deleteNote(int $note_id): void
    {
        $this->dbAccess->delete("notes", array("id" => $note_id));
    }

    /**
     * @param $note_id
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getChainIdByNoteId($note_id): int
    {
        return intval($this->dbAccess->executeQuery(
            "SELECT chain_id as id FROM notes 
            WHERE id = $note_id"
        )[0]["id"]);
    }

    /**
     * @param $chain_id
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function notesAmountInChain($chain_id): int
    {
        return $this->dbAccess->executeQuery(
            "SELECT COUNT(id) as count FROM notes 
            WHERE chain_id = $chain_id"
        )[0]["count"];
    }
}
