<?php declare(strict_types=1);
namespace Communicator\Data\Boards;

class Board implements \JsonSerializable
{
    private int $id;
    private array $chains;
    public string $name;
    
    public function __construct(int $id, string $name) 
    {
        $this->id = $id;
        $this->chains = array();
        $this->name = $name;
    }
    public function addChain(int $position, array $notes)
    {
        $this->chains[$position] = $notes;
    }
    public function removeChain(int $position)
    {
        unset($this->chains[$position]);
    }
    public function getId()
    {
        return $this->id;
    }
    public function jsonSerialize(): mixed
    {
        $jsonArray = array(
            "id" => $this->id,
            "name" => $this->name
            );
        $jsonArray["chains"] = array();
        $jsonArray["chains"]["amount"] = count($this->chains);
        foreach($this->chains as $placement => $chain)
        {
            $jsonArray["chains"][$placement] = array();
            foreach($chain as $note)
            {
                $jsonArray["chains"][$placement][]=$note->jsonSerialize();
            }
        }
        return $jsonArray;
    }
}
