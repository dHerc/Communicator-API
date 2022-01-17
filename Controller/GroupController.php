<?php

namespace Communicator\Controller;

use Communicator\Database\DAOs\BoardsDAO as DAO;
use Communicator\Database\DAOs\UsersDAO;
use Communicator\Database\DatabaseAccess as DB;
use Communicator\Model\Groups\Group;
use Communicator\Model\Groups\GroupUser;
use Communicator\Model\Users\User;
use Communicator\Exceptions\Database\BadQueryException;
use Communicator\Exceptions\Database\NoConnectionException;
use Communicator\Exceptions\Users\UserNotFoundException;
use Communicator\Exceptions\Users\InvalidPermissionException;
use Communicator\Exceptions\UnauthorizedException;
use Communicator\Utils\Logger as Logger;
use JetBrains\PhpStorm\Pure;

/**
 * Klasa służąca do obsługi grup
 */
class GroupController
{
    /**
     * Klasa służąca do zapisywania logów
     * @var Logger
     */
    private Logger $logger;
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
     * Funkcja zwracająca grupę o danym identyfikatorze
     * @param int $groupId Identyfikator grupy
     * @param int $userId Identyfikator użytkownika
     * @return GroupUser[] Obiekt grupy
     * @throws UnauthorizedException Wyjątek ten jest rzucany, jeżeli użytkownik nie ma wystarczających uprawnień
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getGroup(int $groupId, int $userId): array
    {
        $userPermission = $this->usersDAO->getPermission($groupId, $userId);
        if(!in_array($userPermission, ['admin', 'owner']))
            throw new UnauthorizedException();
        $group_users = $this->usersDAO->getGroupUsers($groupId);
        $users = [];
        foreach ($group_users as $user) {
            $users[] = new GroupUser($user['username'], $user['permission']);
        }
        return $users;
    }

    /**
     * Funckja zwracająca wszystkie grupy użytkownika o danym identyfikatorze
     * @param int $userId Identyfikator użytkownika
     * @return array Tablica grup danego użytkownika
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getGroups(int $userId): array
    {
        $groups = $this->usersDAO->getGroups($userId);
        $groupIds = [];
        foreach ($groups as $group)
            $groupIds[] = $group['group_id'];
        return $this->usersDAO->getGroupsByIds($groupIds, $userId);
    }

    /**
     * Funkcja zwracająca wszystkie tablice w grupie o danym identyfikatorze
     * @param int $groupId Identyfikator grupy
     * @return array Tablica tablic danej grupy
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function getBoards(int $groupId): array
    {
        return $this->usersDAO->getBoards($groupId);
    }

    /**
     * Funkcja dodająca nową grupę
     * @param string $name Nazwa grupy
     * @param int $userId Identyfikator użytkownika
     * @return Group Nowy obiekt grupy
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function addGroup(string $name, int $userId): Group
    {
        $group_id = $this->usersDAO->addGroup($name, $userId);
        return new Group($group_id, $name, 'owner');
    }

    /**
     * Funkcja usuwająca grupę o danym identyfikatorze
     * @param int $groupId Identyfikator grupy
     * @param int $userId Identyfikator użytkownika
     * @throws UnauthorizedException Wyjątek ten jest rzucany, jeżeli użytkownik nie ma wystarczających uprawnień
     * @throws UserNotFoundException Wyjątek ten jest rzucany, jeżeli użytkownik o podanym identyfikatorze nie został znaleziony
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function delGroup(int $groupId, int $userId)
    {
        $userPermission = $this->usersDAO->getPermission($groupId, $userId);
        if($userPermission == null)
            throw new UserNotFoundException();
        if($userPermission != 'owner')
            throw new UnauthorizedException();
        $this->usersDAO->deleteGroup($groupId);
    }

    /**
     * Funkcja zmieniająca uprawnienia użytkownika o danej nazwie
     * @param int $groupId Identyfikator grupy
     * @param string $username Nazwa użytkownika do zmiany uprawnień
     * @param string $permission Nowe uprawnienie
     * @param int $userId Identyfikator użytkownika zmieniającego uprawnienie
     * @throws UnauthorizedException Wyjątek ten jest rzucany, jeżeli użytkownik nie ma wystarczających uprawnień
     * @throws InvalidPermissionException Wyjątek ten jest rzucany, jeżeli podane uprawnienie nie istnieje
     * @throws BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function editUserPermissions(int $groupId, string $username, string $permission, int $userId)
    {
        if(!in_array($permission, ['viewer', 'editor', 'admin', 'none']))
            throw new InvalidPermissionException($permission);
        $userPermission = $this->usersDAO->getPermission($groupId, $userId);
        if($userPermission == null)
            throw new UserNotFoundException();
        if(!in_array($userPermission, ['admin', 'owner']))
            throw new UnauthorizedException();
        $editedUser = $this->usersDAO->getUserByLogin($username);
        if($editedUser == null || $editedUser['id'] == $userId)
            throw new UserNotFoundException();
        $editedUserPermission = $this->usersDAO->getPermission($groupId, $editedUser['id']);
        if($editedUserPermission == 'owner'
            ||$editedUserPermission == 'admin' && $userPermission == 'admin')
            throw new UnauthorizedException();
        if($editedUserPermission == null)
            $this->usersDAO->addUserToGroup($groupId, $editedUser['id'], $permission);
        elseif($permission == 'none')
            $this->usersDAO->deleteUserFromGroup($groupId, $editedUser['id']);
        else
            $this->usersDAO->editUserInGroup($groupId, $editedUser['id'], $permission);
    }

}