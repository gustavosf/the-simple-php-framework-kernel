<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework\Database\Driver;
use Framework\Database;
use Framework\DatabaseException;

/**
 * Database Driver for PDO
 */
class Pdo extends Database {
	
	/**
	 * Connect to the database
	 *
	 * @return void
	 * @throws DatabaseException as PDOException occurs
	 */
	public function connect()
	{
		if ($this->_connection) return;

		$config = $this->_config + [
			'dsn'      => '',
			'username' => null,
			'password' => null
		];

		try
		{
			$this->_connection = new \PDO(
				$config['dsn'], $config['username'], $config['password'],
				[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
		}
		catch (\PDOException $e)
		{
			$ecode = $this->_connection ? $this->_connection->errorinfo()[1] : 0;
			throw new DatabaseException($e->getMessage(), $ecode, $e);
		}
	}

	/**
	 * Disconnect from the database
	 *
	 * @return void
	 */
	public function disconnect()
	{
		$this->_connection = null;
	}

	/* ********************************************************************* */
	/* * Query resolving methods ******************************************* */
	/* ********************************************************************* */

	/**
	 * Retrieve elements using the query
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	public function get()
	{
		$return = $this->query($this->mountQuery());
		$this->_query_build = []; # resets query
		return $return;
	}

	/**
	 * Mounts an SQL query string from query_build
	 * 
	 * @return string
	 * @throws DatabaseException
	 * @todo   Implement insert, update, etc, etc
	 */
	private function mountQuery()
	{
		$parts = $this->_query_build;
		$sql = [];
		
		if (isset($parts['select']))
		{
			$sql[] = 'SELECT';
			if (empty($parts['select'])) $sql[] = '*';
			else $sql[] = implode(',', $parts['select']);
		}
		else throw new DatabaseException('Invalid query');
		
		$sql[] = 'FROM '.$parts['from'];
		
		$where = [];
		if (isset($parts['where']))
		{
			$where[]= 'WHERE';
			foreach ($parts['where'] as $w)
			{
				if (count($where) != 1) $where[] = $w[0];
				if (count($w) == 3) $where[] = "{$w[1]} = '{$w[2]}'";
				else $where[] = "{$w[1]} {$w[2]} '{$w[3]}'";
			}
		}
		return implode(' ', array_merge($sql, $where));
	}

	/**
	 * Queries the database
	 *
	 * @param string $sql SQL Query
	 *
	 * @return array   Result list for SELECT queries
	 * @return integer Row count for INSERT queries
	 * @return integer Number of affected rows otherwise
	 * 
	 * @todo Implement return for other types of queries (insert, update, delete...)
	 */
	private function query($sql)
	{
		$this->_connection or $this->connect();

		try
		{
			$result = $this->_connection->query($sql);
		}
		catch (\Exception $e)
		{
			$ecode = $this->_connection ? $this->_connection->errorinfo()[1] : 0;
			throw new DatabaseException("{$e->getMessage()} with query \"{$sql}\"", $ecode, $e);
		}

		return $result->fetchAll(\PDO::FETCH_ASSOC);

		// if (preg_match('/^select/i', $sql)) return $result->fetchAll(\PDO::FETCH_ASSOC);
		// elseif (preg_match('/^insert/i', $sql)) return $this->_connection->lastInsertId();
		// else return $result->errorCode() !== '00000' ? -1 : $result->rowCount();
	}
}
