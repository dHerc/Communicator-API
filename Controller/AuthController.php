<?php declare(strict_types=1);

namespace Communicator\Controller;

use Communicator\Database\DAOs\UsersDAO;
use Communicator\Database\DatabaseAccess as DB;
use Communicator\Model\Users\User;
use Communicator\Exceptions\Database\BadQueryException;
use Communicator\Exceptions\Database\NoConnectionException;
use Communicator\Exceptions\Users\UserAlreadyExistsException;
use Communicator\Exceptions\Users\UserNotFoundException;
use Communicator\Utils\Common;
use Communicator\Utils\Error;
use Communicator\Utils\Logger;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Klasa służąca do autoryzacji użytkownika
 */
class AuthController
{
    /**
     * Klasa służąca do zapisywania logów
     * @var Logger
     */
    private Logger $logger;
    /**
     * Obiekt użytkownika
     * @var User
     */
    private User $user;
    /**
     * Obiekt zawierający funkcje pozwalające na operacje na bazie danych związane z użytkownikami
     * @var UsersDAO
     */
    private UsersDAO $usersDAO;

    #[Pure] public function __construct(Logger $logger, DB $dbAccess)
    {
        $this->logger = $logger;
        $this->usersDAO = new UsersDAO($dbAccess);
    }

    /**
     * Funkcja pozwalająca zarejestrować użytkownika
     * @param string $login Nazwa użytkownika
     * @param string $passwordHash Zakodowane hasło
     * @param string $salt Sól dodana do hasła, aby zapewnić większe bezpieczeństwo
     * @return array Tablica zawierająca identyfikator użytkownika oraz jego żeton dostępowy (access token)
     * @throws UserAlreadyExistsException Ten wyjątek jest rzucany, jeżeli użytkownik o takiej nazwie już istnieje
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    #[ArrayShape(['id' => "int", 'token' => "string"])]
    public function register(string $login, string $passwordHash, string $salt): array
    {
        if($this->usersDAO->existsUser($login))
            throw new UserAlreadyExistsException($login);
        $token = $this->generateToken($login);
        $userId = $this->usersDAO->addUser($login,$passwordHash,$salt,$token['token'],$token['expire']);
        return ['id' => $userId,
            'token' => $token['token']];
    }

    /**
     * Funkcja pozwalająca na logowanie użytkownika
     * @param string $login Nazwa użytkownika
     * @param string $passwordHash Zakodowane hasło
     * @return array Tablica zawierająca identyfikator użytkownika oraz jego żeton dostępowy (access token)
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    #[ArrayShape(['id' => "int", 'token' => "string"])]
    public function login(string $login, string $passwordHash): array
    {
        $user = $this->usersDAO->checkCredentials($login,$passwordHash);
        $token = $this->generateToken($login);
        $this->usersDAO->saveToken(intval($user['id']),$token['token'], $token['expire']);
        return ['id' => $user['id'],
            'token' => $token['token']];
    }

    /**
     * Funkcja pozwalająca na uzyskanie soli w celu zakodowania hasła
     * @param string $login Nazwa użytkownika
     * @return string Sól przypisana do danego użytkownika
     * @throws UserNotFoundException Ten wyjątek jest rzucany, jeżeli użytkownik o danej nazwie nie został znaleziony
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getSalt(string $login): string
    {
        $user = $this->usersDAO->getUserByLogin($login);
        if(!$user || count($user) == 0)
            throw new UserNotFoundException();
        return $user['salt'];
    }

    /**
     * Funkcja generująca żeton dostępowy dla użytkownika o danej nazwie
     * @param string $login Nazwa użytkownika
     * @return array Tabela zawierająca żeton dostępowy oraz czas jego wygaśnięcia
     */
    #[ArrayShape(['token' => "string", 'expire' => "string"])]
    private function generateToken(string $login): array
    {
        $issuedAt   = new DateTimeImmutable();
        $expire     = $issuedAt->modify('+1 day');
        $serverName = "communicator";

        $data = [
            'iat'  => $issuedAt->getTimestamp(),
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire->getTimestamp(),
            'username' => $login,
        ];
        $token =  JWT::encode(
            $data,
            getenv('HTTP_SECRET'),
            'HS512'
        );
        return ['token' => $token,
            'expire' => $expire->format("Y-m-d H:i:s")];
    }

    /**
     * Funkcja ładująca użytkownika o danym żetonie dostępowym
     * @param string|null $token Żeton dostępowy
     * @return bool Informacja czy udało się załadować użytkownika
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function loadUser(?string $token): bool
    {
        if($token === null)
            return false;
        $secretKey  = getenv("HTTP_SECRET");
        $token = str_replace("Bearer ", "", $token);
        try {
            $decodedToken = JWT::decode($token, $secretKey, ['HS512']);
        }
        catch (\Exception $e)
        {
            return false;
        }
        $now = new DateTimeImmutable();
        $serverName = "communicator";
        if ($decodedToken->iss !== $serverName ||
            $decodedToken->nbf > $now->getTimestamp() ||
            $decodedToken->exp < $now->getTimestamp())
            return false;
        $user = $this->usersDAO->getUserByLogin($decodedToken->username);
        if($user['access_token'] == $token) {
            $this->user = new User(intval($user['id']),$decodedToken->username,$token);
            return true;
        }
        return false;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Funkcja zwracająca wszystkie uprawnienia powyżej podanego
     * @param string $minPermission Minimalne wymagane uprawnienie
     * @return array Tabela zawierająca wszystkie pasujące uprawnienia
     */
    private function getPermissions(string $minPermission): array
    {
        switch ($minPermission)
        {
            case "viewer":
                return ["viewer", "editor", "admin", "owner"];
            case "editor":
                return ["editor", "admin", "owner"];
            case "admin":
                return ["admin", "owner"];
            case "owner":
                return ["owner"];
            default:
                $this->logger->log("info", "wrongly provided minimal permission");
                return [];
        }
    }

    /**
     * Funkcja sprawdzająca uprawnienia użytkownika do danej notatki
     * @param int $noteId Identyfikator notatki
     * @param string $minPermission Minimalne wymagane uprawnienie
     * @return bool Informacja czy użytkownika posiada uprawnienie do wykonania danej czynności, czy nie
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function checkNotePermission(int $noteId, string $minPermission): bool
    {
        $boardId = $this->usersDAO->getNoteBoard($noteId);
        return $this->checkBoardPermission($boardId, $minPermission);
    }

    /**
     * Funkcja sprawdzająca uprawnienia użytkownika do danej tablicy
     * @param int $boardId Identyfikator tablicy
     * @param string $minPermission Minimalne wymagane uprawnienie
     * @return bool Informacja czy użytkownika posiada uprawnienie do wykonania danej czynności, czy nie
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function checkBoardPermission(int $boardId, string $minPermission): bool
    {
        $groupId = $this->usersDAO->getBoardGroup($boardId);
        return $this->checkGroupPermission($groupId, $minPermission);
    }

    /**
     * Funkcja sprawdzająca uprawnienia użytkownika do danej grupy
     * @param int $groupId Identyfikator grupy
     * @param string $minPermission Minimalne wymagane uprawnienie
     * @return bool Informacja czy użytkownika posiada uprawnienie do wykonania danej czynności, czy nie
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function checkGroupPermission(int $groupId, string $minPermission): bool
    {
        $permissions = $this->getPermissions($minPermission);
        $permission = $this->usersDAO->getPermission($groupId, $this->user->id);
        return in_array($permission, $permissions);
    }


}