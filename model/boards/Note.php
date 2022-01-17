<?php declare(strict_types=1);
namespace Communicator\Model\Boards;

use Communicator\Model\Boards\Notes\Helpers\Content;

/**
 * Klasa służąca do przechowywania informacji o notatce
 */
abstract class Note implements \JsonSerializable {

    /**
     * Identyfikator notatki
     * @var int
     */
    private int $id;
    /**
     * Typ notatki
     * @var string
     */
    private string $type;
    /**
     * Wiadomość tekstowa w notatce
     * @var string
     */
    public string $message;
    /**
     * Zawartość notatki
     * @var Content|null
     */
    public ?Content $content;
    public function __construct(int $id, $type, string $message, mixed $content = null)
    {
        $this->id = $id;
        $this->type = $type;
        $this->message = $message;
        $this->content = $content;
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

    /**
     * Funkcja zwracająca obiekt w formie tablicy, która może zostać zapisana w bazie danych
     * @return array Obiekt w formie tablicy
     */
    abstract public function getData(): array;
    /**
     * Funkcja zwracająca obiekt w formie tablicy, która może zostać poddana serializacji
     * @return array Obiekt w formie tablicy
     */
    abstract public function jsonSerialize(): array;
}
