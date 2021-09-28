<?php declare(strict_types=1);
namespace Communicator\Data\Database;

require 'connection.php';

use Communicator\Exceptions\Database as Exceptions;

class DatabaseAccess
{
    
	public function getOneBy(string $name, array $conditions = array()): ?array
	{
		$connection = $this->connect();
        $query = "SELECT * FROM $name";
        $query.= $this->addConditions($conditions);
        $query_result = $connection->query($query);
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = $query_result->fetch_assoc();
		return $result;
	}
	public function getAllBy(string $name, array $conditions = array()): array
	{
		$connection = $this->connect();
        $query = "SELECT * FROM $name";
        $query.= $this->addConditions($conditions);
        $query_result = $connection->query($query);
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = array();
        while($row = $query_result->fetch_assoc())
        {
            $result[] = $row;
        }
		return $result;
	}
	public function add(string $name, array $data = array()): int
	{
		$connection = $this->connect();
        $query = "INSERT INTO $name";
        if(!empty($data))
        {
            $fields = "";
            $values = "";
            foreach($data as $field => $value)
            {
                $fields.= "`$field`,";
                $values.= "'$value',";
            }
            $query.= " (".substr($fields,0,-1).") VALUES (".substr($values,0,-1).")";
        }
        if(!$connection->query($query))
            throw new Exceptions\BadQueryException($query, $connection->error);
        $id = $connection->insert_id;
		return $id;
	}
	public function edit(string $name, array $data, array $conditions = array()): void
	{
		$connection = $this->connect();
        $query = "UPDATE $name SET";
        $sets = "";
        foreach($data as $field => $value)
        {
            $sets.="`$field`='$value',";
        }
        $query.=substr($sets,0,-1);
        $query.= $this->addConditions($conditions);
        if(!$connection->query($query))
            throw new Exceptions\BadQueryException($query, $connection->error);
	}
	public function delete(string $name, array $conditions = array()): void
	{
		$connection = $this->connect();
        $query = "DELETE FROM $name";
        $query.= $this->addConditions($conditions);
        if(!$connection->query($query))
            throw new Exceptions\BadQueryException($query, $connection->error);
	}
    public function exists(string $name, array $conditions = array()): bool
    {
        $connection = $this->connect();
        $query = "SELECT EXISTS(SELECT * FROM $name";
        $query.= $this->addConditions($conditions).") as result";
        $query_result = $connection->query($query);
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = boolval($query_result->fetch_assoc()["result"]);
		return $result;
    }
    public function executeQuery(string $query): array
    {
        $connection = $this->connect();
        $query_result = $connection->query($query);
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = array();
        while($row = $query_result->fetch_assoc())
        {
            $result[] = $row;
        }
        return $result;
    }
	private function addConditions(array $conditions): string
	{
		$query = "";
		$first = true;
		foreach($conditions as $field => $value)
		{
			if($first)
			{
				$query.=" WHERE ";
				$first = false;
			}
			else
			{
				$query.=" AND ";
			}
			$query.="$field='$value'";
		}
		return $query;
	}
	private function connect(): mixed
	{
		@$connection = new \mysqli(Connection::host, Connection::user, Connection::password, Connection::name);
		if ($connection->connect_errno!=0)
		{
			throw new Exceptions\NoConnectionException(Connection::host,Connection::name);
		}
		else
		{
			return $connection;
		}
	}
}