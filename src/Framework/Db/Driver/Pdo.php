<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework;

/**
 * Database Driver for PDO
 */
class Db_Driver_Pdo extends Db {
	
	/**
	 * Connect to the database
	 *
	 * @return void
	 * @throws DBException as PDOException occurs
	 */
	public function connect()
	{
		if ($this->_connection) return;
		$config = $this->_config + ['dsn' => '', 'username' => null, 'password' => null];

		try
		{
			$this->_connection = new \PDO(
				$config['dsn'], $config['username'], $config['password'],
				[\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]);
		}
		catch (\PDOException $e)
		{
			$ecode = $this->_connection ? $this->_connection->errorinfo()[1] : 0;
			throw new DBException($e->getMessage(), $ecode, $e);
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

	/**
	 * Queries the database
	 *
	 * @param string $sql SQL Query
	 *
	 * @return array   Result list for SELECT queries
	 * @return integer Row count for INSERT queries
	 * @return integer Number of affected rows otherwise
	 */
	public function query($sql)
	{
		$this->_connection or $this->connect();

		try
		{
			$result = $this->_connection->query($sql);
		}
		catch (\Exception $e)
		{
			$ecode = $this->_connection ? $this->_connection->errorinfo()[1] : 0;
			throw new DBException("{$e->getMessage()} with query \"{$sql}\"", $ecode, $e);
		}

		if (preg_match('/^select/i', $sql)) return $result->fetchAll(/*\PDO::FETCH_ASSOC*/);
		elseif (preg_match('/^insert/i', $sql)) return $this->_connection->lastInsertId();
		else return $result->errorCode() !== '00000' ? -1 : $result->rowCount();
	}
}
