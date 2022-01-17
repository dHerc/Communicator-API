<?php declare(strict_types=1);
namespace Communicator\Model\Boards\Notes\Helpers;

use Communicator\Exceptions\Boards\InvalidFormatException;

/**
 * Klasa służąca do przechowywania informacji o notatce zawierającej zadanie
 */
class Task extends Content
{
    /**
     * Informacje odnośnie zadania
     * @var object
     */
    public object $task;

    public function __construct(string|array $data)
    {
        if(gettype($data) === 'string')
            $this->task = json_decode($data);
        else
            $this->task = json_decode(json_encode($data));
        if(!$this->task || !isset($this->task->state))
            throw new InvalidFormatException('task');
    }

    public function get(): string
    {
        return json_encode($this->task);
    }
}