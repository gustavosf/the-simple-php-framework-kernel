<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework;

/**
 * Abstract Database class
 */
abstract class Db {

	protected $_connection;
	protected $_config;

	/**
	 * Generates a database instance
	 *
	 * @param  array $config Database configuration
	 * @return Db
	 * @throws \Exception
	 */
	public static function instance(array $config)
	{
		if (!isset($config['type']))
		{
			throw new \Exception('Database type not defined in "'.$name.'" configuration or "'.$name.'" configuration does not exist');
		}

		# Create an instance using the respective connection driver
		$driver = '\Framework\Db_Driver_'.ucfirst($config['type']);
		return new $driver($config);

		return static::$instances[$name];
	}

	/**
	 * Class Constructor
	 * 
	 * The constructor is protected. You should use static method "instance"
	 * to generate a DB abstract instance
	 *
	 * @param array $config
	 */
	protected function __construct(array $config)
	{
		$this->_config = $config;
	}

	/**
	 * Disconnect from the database if the object is destroyed
	 *
	 * @return  void
	 */	
	final public function __destruct()
	{
		$this->disconnect();
	}

	/**
	 * Connect to the database
	 *
	 * @return void
	 * @throws DBException
	 */
	abstract public function connect();

	/**
	 * Disconnect from the database
	 * 
	 * @return void
	 */
	abstract public function disconnect();

	/**
	 * Queries the database
	 *
	 * @param string $sql SQL Query
	 *
	 * @return array   Result list for SELECT queries
	 * @return integer Row count for INSERT queries
	 * @return integer Number of affected rows otherwise
	 */
	abstract public function query($sql);

}

class DBException extends \Exception {}