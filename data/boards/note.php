<?php declare(strict_types=1);
namespace Communicator\Data\Boards;

use NoteTypes as Type;

abstract class Note implements \JsonSerializable {
    
    private int $id;
    private $type;
    public string $message;
    public function __construct(int $id, $type, string $message) 
    {
        $this->id = $id;
        $this->type = $type;
        $this->message = $message;
    }
    public function setId(int $id): void
    {
        $this->id = $id;
    }
    public function getId(): int
    {
        return $this->id;
    }
    public function getType(): string
    {
        return $this->type;
    }
    abstract public function getData(): array;
    abstract public function jsonSerialize(): mixed;
}
