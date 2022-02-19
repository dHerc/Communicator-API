<?php declare(strict_types=1);

namespace Communicator\Database\DAOs;

use Communicator\Database\DatabaseAccess as DB;
use Communicator\Exceptions\Database\BadQueryException;
use Communicator\Exceptions\Users\UserNotFoundException;

class UsersDAO
{
    private DB $dbAccess;

    public function __construct(DB $dbAccess)
    {
        $this->dbAccess = $dbAccess;
    }

    /**
     * @param int $groupId
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getGroupUsers(int $groupId): array
    {
        return $this->dbAccess->executeQuery(
            "SELECT users.username,permission  FROM groups_users
                    LEFT JOIN users
                    ON user_id = users.id
                    WHERE group_id = $groupId
                    AND permission != 'owner'");
    }

    /**
     * @param int $userId
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getGroups(int $userId): array
    {
        return $this->dbAccess->getAllBy('groups_users',['user_id' => $userId]);
    }

    /**
     * @param array $ids
     * @param int $user_id
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getGroupsByIds(array $ids, int $user_id): array
    {
        $idString = $this->idsToString($ids);
        if($idString == null)
            return [];
        return $this->dbAccess->executeQuery(
            "SELECT groups.*,permission  FROM groups
                    LEFT JOIN groups_users
                    ON group_id = groups.id
                    AND user_id = $user_id
                    WHERE groups.id IN $idString"
        );
    }

    /**
     * @param string $name
     * @param int $userId
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addGroup(string $name, int $userId): int
    {
        $groupId = $this->dbAccess->add('groups',['name' => $name]);
        $this->dbAccess->add('groups_users',[
            'user_id' => $userId,
            'group_id' => $groupId,
            'permission' => 'owner']);
        return $groupId;

    }

    /**
     * @param int $groupId
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function deleteGroup(int $groupId)
    {
        $this->dbAccess->delete('groups', ['id' => $groupId]);
    }

    /**
     * @param int $groupId
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getBoards(int $groupId): array
    {
        return $this->dbAccess->getAllBy('boards', ['group_id' => $groupId]);
    }

    /**
     * @param array $ids
     * @return string|null
     */
    private function idsToString(array $ids): ?string
    {
        if(sizeof($ids) == 0)
            return null;
        $data = "(";
        foreach ($ids as $id)
            $data.=$id.",";
        $data = substr($data,0,strlen($data)-1).")";
        return $data;
    }

    /**
     * @param int $userId
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getUser(int $userId): array
    {
        return $this->dbAccess->getOneBy('users', ['id' => $userId]);
    }

    /**
     * @param string $login
     * @return array|null
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getUserByLogin(string $login): ?array
    {
        return $this->dbAccess->getOneBy('users', ['username' => $login]);
    }

    /**
     * @param string $login
     * @return bool
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function existsUser(string $login): bool
    {
        return $this->dbAccess->exists('users', ['username' => $login]);
    }

    /**
     * @param string $login
     * @param string $passwordHash
     * @param string $salt
     * @param string $token
     * @param string $validTo
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addUser(string $login, string $passwordHash, string $salt, string $token, string $validTo): int
    {
        return $this->dbAccess->add('users',
            ['username' => $login,
            'password' => $passwordHash,
            'salt' => $salt,
            'access_token' => $token,
            'valid_to' => $validTo
            ]);
    }

    /**
     * @param string $login
     * @param string $passwordHash
     * @return array
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function checkCredentials(string $login, string $passwordHash): array
    {
        $user = $this->dbAccess->getOneBy('users', ['username'=> $login, 'password' => $passwordHash]);
        if(!$user || count($user) == 0)
            throw new UserNotFoundException();
        else
            return $user;
    }

    /**
     * @param int $userId
     * @param string $token
     * @param string $validTo
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function saveToken(int $userId, string $token, string $validTo)
    {
        $this->dbAccess->edit('users', ['access_token' => $token, 'valid_to' => $validTo], ['id' => $userId]);
    }

    /**
     * @param int $noteId
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getNoteBoard(int $noteId): int
    {
        $chainId = $this->dbAccess->getOneBy('notes', ['id' => $noteId])['chain_id'];
        return intval($this->dbAccess->getOneBy('chains', ['id' => $chainId])['board_id']);
    }

    /**
     * @param int $boardId
     * @return int
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getBoardGroup(int $boardId): int
    {
        $board = $this->dbAccess->getOneBy('boards', ['id' => $boardId]);
        return $board?intval($board['group_id']):0;
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @return mixed|null
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function getPermission(int $groupId, int $userId)
    {
        $user = $this->dbAccess->getOneBy('groups_users', ['user_id'=> $userId, 'group_id' => $groupId]);
        if($user==null || count($user) == 0)
            return null;
        else
            return $user['permission'];
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @param string $permission
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function addUserToGroup(int $groupId, int $userId, string $permission)
    {
        $this->dbAccess->add('groups_users', ['user_id' => $userId, 'group_id' => $groupId, 'permission' => $permission]);
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @param string $permission
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function editUserInGroup(int $groupId, int $userId, string $permission)
    {
        $this->dbAccess->edit('groups_users', ['permission' => $permission], ['user_id' => $userId, 'group_id' => $groupId]);
    }

    /**
     * @param int $groupId
     * @param int $userId
     * @throws BadQueryException
     * @throws \Communicator\Exceptions\Database\NoConnectionException
     */
    public function deleteUserFromGroup(int $groupId, int $userId)
    {
        $this->dbAccess->delete('groups_users', ['user_id' => $userId, 'group_id' => $groupId]);
    }
}