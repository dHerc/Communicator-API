<?php
namespace Communicator\Model\Groups;

/**
 * Klasa przechowująca informacje o grupie
 */
class Group
{
    public function __construct(int $id, string $name, string $permission)
    {
        $this->id = $id;
        $this->name = $name;
        $this->permission = $permission;
    }

    /**
     * Identyfikator grupy
     * @var int
     */
    public int $id;
    /**
     * Nazwa grupy
     * @var string
     */
    public string $name;
    /**
     * Uprawnienie do grupy
     * @var string
     */
    public string $permission;
}