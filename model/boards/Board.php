<?php declare(strict_types=1);
namespace Communicator\Model\Boards;

use JetBrains\PhpStorm\ArrayShape;

/**
 * Klasa służąca do przechowywania informacji o tablicy
 */
class Board implements \JsonSerializable
{
    /**
     * Identyfikator tablicy
     * @var int
     */
    private int $id;
    /**
     * Tablica łańcuchów
     * @var array
     */
    private array $chains;
    /**
     * Nazwa tablicy
     * @var string
     */
    public string $name;
    
    public function __construct(int $id, string $name) 
    {
        $this->id = $id;
        $this->chains = array();
        $this->name = $name;
    }

    /**
     * Funkcja dodająca nowy łańcuch
     * @param int $position Pozycja łańcucha
     * @param array $notes Notatki w łańcuchu
     */
    public function addChain(int $position, array $notes)
    {
        $this->chains[$position] = $notes;
    }

    /**
     * Funkcja usuwająca łańcuch o danej pozycji
     * @param int $position Pozycja łańcucha
     */
    public function removeChain(int $position)
    {
        unset($this->chains[$position]);
    }

    /**
     * Funkcja zwracająca identyfikator tablicy
     * @return int Identyfikator tablicy
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Funkcja zwracająca obiekt w formie tablicy, która może zostać poddana serializacji
     * @return array Obiekt w formie tablicy
     */
    #[ArrayShape(["id" => "int", "name" => "string", "chains" => "array"])]
    public function jsonSerialize(): array
    {
        $jsonArray = array(
            "id" => $this->id,
            "name" => $this->name
            );
        $jsonArray["chains"] = array();
        foreach($this->chains as $placement => $chain)
        {
            $notes = array();
            foreach($chain as $note)
            {
                $notes[]=$note->jsonSerialize();
            }
            $jsonArray["chains"][] = array("placement" => $placement, "notes" => $notes);
        }
        return $jsonArray;
    }
}
