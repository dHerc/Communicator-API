<?php declare(strict_types=1);
namespace Communicator\Database;

require 'Connection.php';

use Communicator\Exceptions\Database as Exceptions;

/**
 * Klasa służąca do wykonywania operacji na bazie danych
 */
class DatabaseAccess
{
    /**
     * Funkcja pobierająca pierwszy element spełniający dane kryteria
     * @param string $table Nazwa tablicy
     * @param array $conditions Tablica warunków
     * @return array|null Wynik zapytania
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	public function getOneBy(string $table, array $conditions = array()): ?array
	{
		$connection = $this->connect();
        $query = "SELECT * FROM $table";
        $bindings = [];
        $bindingsTypes = "";
        $query.= $this->addConditions($conditions, $bindings, $bindingsTypes);
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        $statement->execute();
        $query_result = $statement->get_result();
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        return $query_result->fetch_assoc();
	}

    /**
     * Funkcja pobierająca wszystkie elementy spełniające dane kryteria
     * @param string $table Nazwa tablicy
     * @param array $conditions Tablica warunków
     * @return array Tablica zawierająca wszystkie wyniki
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	public function getAllBy(string $table, array $conditions = array()): array
	{
		$connection = $this->connect();
        $query = "SELECT * FROM $table";
        $bindings = [];
        $bindingsTypes = "";
        $query.= $this->addConditions($conditions, $bindings, $bindingsTypes);
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        $statement->execute();
        $query_result = $statement->get_result();
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = array();
        while($row = $query_result->fetch_assoc())
        {
            $result[] = $row;
        }
		return $result;
	}

    /**
     * Funkcja zapisująca element do bazy danych
     * @param string $table Nazwa tablicy
     * @param array $data Informacje do zapisania
     * @return int Identyfikator nowego wiersza
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	public function add(string $table, array $data = array()): int
	{
		$connection = $this->connect();
        $query = "INSERT INTO $table";
        $bindings = [];
        $bindingsTypes = "";
        if(!empty($data))
        {
            $fields = "";
            $values = "";
            foreach($data as $field => $value)
            {
                $fields.= "`$field`,";
                $values.= "?,";
                $bindings[] = $value;
                $bindingsTypes .= gettype($value)[0];
            }
            $query.= " (".substr($fields,0,-1).") VALUES (".substr($values,0,-1).")";
        }
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        if(!$statement->execute())
            throw new Exceptions\BadQueryException($query, $connection->error);
        $id = $connection->insert_id;
		return $id;
	}

    /**
     * Funkcja edytująca dany wiersz
     * @param string $table Nazwa tablicy
     * @param array $data Informacje do zapisania
     * @param array $conditions Tablica warunków
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	public function edit(string $table, array $data, array $conditions = array()): void
	{
		$connection = $this->connect();
        $query = "UPDATE $table SET";
        $sets = "";
        $bindings = [];
        $bindingsTypes = "";
        foreach($data as $field => $value)
        {
            $sets.="`$field`=?,";
            $bindings[] = $value;
            $bindingsTypes .= gettype($value)[0];
        }
        $query.=substr($sets,0,-1);
        $query.= $this->addConditions($conditions, $bindings, $bindingsTypes);
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        if(!$statement->execute())
            throw new Exceptions\BadQueryException($query, $connection->error);
	}

    /**
     * Funkcja usuwająca odpowiednie wiersze
     * @param string $table Nazwa tablicy
     * @param array $conditions Tablica warunków
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	public function delete(string $table, array $conditions = array()): void
	{
		$connection = $this->connect();
        $query = "DELETE FROM $table";
        $bindings = [];
        $bindingsTypes = "";
        $query.= $this->addConditions($conditions, $bindings, $bindingsTypes);
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        if(!$statement->execute())
            throw new Exceptions\BadQueryException($query, $connection->error);
	}

    /**
     * Funkcja sprawdzająca, czy odpowiedni wiersz istnieje
     * @param string $table Nazwa tablicy
     * @param array $conditions Tablica warunków
     * @return bool Informacja czy odpowiedni wiersz istnieje
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
    public function exists(string $table, array $conditions = array()): bool
    {
        $connection = $this->connect();
        $query = "SELECT EXISTS(SELECT * FROM $table";
        $bindings = [];
        $bindingsTypes = "";
        $query.= $this->addConditions($conditions, $bindings, $bindingsTypes).") as result";
        $statement = $connection->prepare($query);
        $statement->bind_param($bindingsTypes, ...$bindings);
        $statement->execute();
        $query_result = $statement->get_result();
        if(!$query_result)
            throw new Exceptions\BadQueryException($query, $connection->error);
        $result = boolval($query_result->fetch_assoc()["result"]);
		return $result;
    }

    /**
     * Funkcja wykonująca podane zapytanie
     * @param string $query Zapytanie
     * @return array Odpowiedź na zapytanie
     * @throws Exceptions\BadQueryException Ten wyjątek jest rzucany, jeżeli wygenerowane zapytanie nie jest poprawne
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
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

    /**
     * Funkcja dodająca warunki do zapytania
     * @param array $conditions Tablica warunków
     * @return string Fragment zapytania zawierający warunki
     */
	private function addConditions(array $conditions, array & $bindings, string & $bindingsTypes): string
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
			$query.="$field=?";
            $bindings[]=$value;
            $bindingsTypes.=gettype($value)[0];
		}
		return $query;
	}

    /**
     * Funkcja tworząca połączenie z bazą danych
     * @return \mysqli Obiekt połączenia
     * @throws Exceptions\NoConnectionException Ten wyjątek jest rzucany, jeżeli nie można połączyć się z bazą danych
     */
	private function connect(): \mysqli
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