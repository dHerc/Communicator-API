<?php

namespace Communicator\Model\Groups;

/**
 * Klasa przechowująca informacje o uprawnieniach użytkownika w grupie
 */
class GroupUser
{
    public function __construct(string $username, string $permission)
    {
        $this->username = $username;
        $this->permission = $permission;
    }

    /**
     * Nazwa użytkownika
     * @var string
     */
    public string $username;
    /**
     * Uprawnienie
     * @var string
     */
    public string $permission;
}