<?php declare(strict_types=1);
namespace Communicator\Controller;

use Communicator\Exceptions\Database\BadQueryException;
use Communicator\Exceptions\Database\NoConnectionException;
use Communicator\Model\Boards\Board;
use Communicator\Model\Boards\Note;
use Communicator\Services\NoteFactory;
use Communicator\Utils\Logger as Logger;
use Communicator\Database\DatabaseAccess as DB;
use Communicator\Database\DAOs\BoardsDAO;
use Communicator\Exceptions\Boards as Exceptions;
use JetBrains\PhpStorm\Pure;

/**
 * Klasa służąca do obsługi tablic
 */
class BoardController {

    /**
     * Klasa służąca do zapisywania logów
     * @var Logger
     */
    private Logger $logger;
    /**
     * Obiekt zawierający funkcje pozwalające na operacje na bazie danych związane z tablicami
     * @var BoardsDAO
     */
    private BoardsDAO $boardsDAO;
    /**
     * Fabryka pozwalająca na tworzenie nowych oraz pobieranie już istniejących notatek
     * @var NoteFactory|null
     */
    private ?NoteFactory $factory;
    
    #[Pure] public function __construct(Logger $logger, DB $dbAccess, NoteFactory $factory = null)
    {
        $this->logger = $logger;
        $this->boardsDAO = new BoardsDAO($dbAccess);
        $this->factory = $factory;
    }

    /**
     * Funkcja pozwalająca na uzyskanie tablicy
     * @param int $boardId Identyfikator tablicy
     * @return Board Obiekt tablicy o podanym identyfikatorze
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getBoard(int $boardId): Board
    {
        $board_name = $this->boardsDAO->getBoard($boardId)["name"];
        $board = new Board($boardId,$board_name);
        $chains = $this->boardsDAO->getAllChains($boardId);
        foreach($chains as $chain)
        {
            $notes = $this->boardsDAO->getAllNotes(intval($chain["id"]));
            if(empty($notes))
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

    /**
     * Funkcja dodająca nową tablicę
     * @param string $name Nazwa tablicy
     * @param int $groupId Identyfikator grupy, do której ma być dodana tablica
     * @return Board Nowy obiekt tablicy
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function addBoard(string $name, int $groupId): Board
    {
        $board_id = $this->boardsDAO->addBoard($name, $groupId);
        return new Board($board_id,$name);
    }

    /**
     * Funkcja pozwalająca na aktualizację odpowiedniej tablicy
     * @param array $board_array Tablica zawierająca nowe dane
     * @throws Exceptions\BoardNotFoundException Ten wyjątek jest rzucany, jeżeli tablica o danym identyfikatorze nie została znaleziona
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function updateBoard(array $board_array): void
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_array["id"]))
            throw new Exceptions\BoardNotFoundException($board_array["id"]);
        $data = array("name" => $board_array["name"]);
        $this->boardsDAO->updateBoard($board_array["id"], $data);
    }

    /**
     * Funkcja pozwalająca na usunięcie tablicy o danym identyfikatorze
     * @param int $board_id Identyfikator tablicy do usunięcia
     * @throws Exceptions\BoardNotFoundException Ten wyjątek jest rzucany, jeżeli tablica o danym identyfikatorze nie została znaleziona
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function deleteBoard(int $board_id): void
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_id))
            throw new Exceptions\BoardNotFoundException($board_id);
        $this->boardsDAO->deleteBoard($board_id);
    }

    /**
     * @param int $board_id
     * @param int $chain_pos
     * @return int
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getChain(int $board_id, int $chain_pos): int
    {
        return $this->boardsDAO->getChain($board_id, $chain_pos);
    }

    /**
     * Funkcja dodająca nowy łańcuch do tablicy o podanym identyfikatorze
     * @param int $board_id Identyfikator tablicy
     * @return int Identyfikator nowego łańcucha
     * @throws Exceptions\BoardNotFoundException Ten wyjątek jest rzucany, jeżeli tablica o danym identyfikatorze nie została znaleziona
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function addChain(int $board_id): int
    {
        if(!$this->boardsDAO->checkIfBoardExists($board_id))
            throw new Exceptions\BoardNotFoundException($board_id);
        $placement = $this->boardsDAO->getHighestChainPlacement($board_id)+1;
        return $this->boardsDAO->addChain($board_id, $placement);
    }

    /**
     * Funkcja usuwająca odpowiedni łańcuch z tablicy o podanym identyfikatorze
     * @param int $board_id Identyfikator tablicy
     * @param int $chain_pos Pozycja łańcucha
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function deleteChain(int $board_id, int $chain_pos): void
    {
        $chain_id = $this->boardsDAO->getChain($board_id, $chain_pos);
        $this->boardsDAO->deleteChain($chain_id);
    }

    /**
     * Funkcja dodająca nową notatkę
     * @param int $chain_id Identyfikator łańcucha, do którego ma być dodana
     * @param string $type Typ notatki
     * @param string $message Wiadomość w notatce
     * @param mixed|null $content Dodatkowa zawartość notatki
     * @param int|null $placement Położenie notatki w łańcuchu
     * @return Note Nowy obiekt notatki
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function addNote(int $chain_id, string $type, string $message, mixed $content = null, int $placement = null): Note
    {
        if($placement === null)
            $placement = $this->boardsDAO->getHighestNotePlacement($chain_id)+1;
        $note = $this->factory->createNote($type,$message,$content);
        $new_id = $this->boardsDAO->addNote($chain_id,$type,$message,$note->content,$placement);
        $note->setId($new_id);
        return $note;
    }

    /**
     * Funkcja aktualizująca odpowiednią notatkę
     * @param array $note_array Tablica z informacjami o notatce
     * @return Note Zaktualizowany obiekt notatki
     * @throws Exceptions\NoteNotFoundException Ten wyjątek jest rzucany, jeżeli notatka o danym identyfikatorze nie została znaleziona
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function updateNote(array $note_array): Note
    {
        if(!$this->boardsDAO->checkIfNoteExists($note_array["id"]))
            throw new Exceptions\NoteNotFoundException($note_array["id"]);
        $note = $this->factory->getNote($note_array);
        $this->boardsDAO->updateNote($note->getId(),$note->getData());
        return $note;
    }

    /**
     * Funkcja usuwająca notatkę o danym identyfikatorze
     * @param int $note_id Identyfikator notatki
     * @throws Exceptions\NoteNotFoundException Ten wyjątek jest rzucany, jeżeli tablica o danym identyfikatorze nie została znaleziona
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
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
