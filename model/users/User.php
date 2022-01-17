<?php
namespace Communicator\Model\Users;

/**
 * Klasa przechowująca informacje o użytkowniku
 */
class User
{
    public function __construct(int $id, string $username, string $token)
    {
        $this->id = $id;
        $this->username = $username;
        $this->token = $token;
    }

    /**
     * Identyfikator użytkownika
     * @var int
     */
    public int $id;
    /**
     * Nazwa użytkownika
     * @var string
     */
    public string $username;
    /**
     * Żeton dostępowy
     * @var string
     */
    public string $token;
}