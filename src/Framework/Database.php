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
abstract class Database {

	/**
	 * Database's active instance cache
	 * 
	 * @var Database
	 */
	protected static $instance;

	/**
	 * Global configuration
	 * 
	 * @var array
	 */
	protected static $config;

	/**
	 * Instance connection
	 * 
	 * @var array
	 */
	
	protected $_connection;
	
	/**
	 * Instance configuration
	 * 
	 * @var array
	 */
	protected $_config;


	/**
	 * Instance query bulder
	 * 
	 * @var array
	 */
	protected $_query_build;

	/**
	 * Sets global configuration for forthcoming instances
	 * 
	 * @param  array  $config
	 * @return void
	 */
	final public static function configure(array $config)
	{
		static::$config = $config;
		static::$instance = null;
	}

	/**
	 * Returns a database instance
	 *
	 * @return Database
	 * @throws \Exception
	 */
	final public static function instance()
	{
		if (static::$instance === null)
		{
			if (!isset(static::$config['type']))
				throw new DatabaseException('Database type not defined');

			# Create an instance using the respective driver
			$driver = 'Framework\Database_Driver_'.ucfirst(static::$config['type']);
			static::$instance = new $driver(static::$config);
		}

		return static::$instance;
	}

	protected function __construct($config)
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
	 * @throws DatabaseException
	 */
	abstract public function connect();

	/**
	 * Disconnect from the database
	 * 
	 * @return void
	 */
	abstract public function disconnect();
	
	/* ********************************************************************* */
	/* * Query building methods ******************************************** */
	/* ********************************************************************* */

	public function select()
	{
		$this->_query_build['select'] = func_get_args();
		return $this;
	}

	public function from($table)
	{
		$this->_query_build['from'] = $table;
		return $this;
	}

	public function where()
	{
		$this->_query_build['where'][] = array_merge(['and'], func_get_args());
		return $this;
	}

	/* ********************************************************************* */
	/* * Query resolving methods ******************************************* */
	/* ********************************************************************* */

	abstract public function get();
	abstract public function getOne();
}

/**
 * Database Exception
 */
class DatabaseException extends \Exception {}