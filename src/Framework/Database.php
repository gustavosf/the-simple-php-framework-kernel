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

	###########################################################################
	###   Initializer   #######################################################
	###########################################################################

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

	###########################################################################
	###   Constructor   #######################################################
	###########################################################################

	/**
	 * Constructor.
	 * 
	 * @param array $config Connection configuration
	 */
	protected function __construct($config)
	{
		$this->_config = $config;
	}

	###########################################################################
	###   Instantiator   ######################################################
	###########################################################################
	
	/**
	 * Returns a database instance
	 *
	 * @return Database
	 * @throws \Exception
	 */
	final public static function &instance()
	{
		if (static::$instance === null)
		{
			if (!isset(static::$config['driver']))
				throw new DatabaseException('Database driver not defined');

			# Create an instance using the respective driver
			$driver = 'Framework\Database\Driver\\'.ucfirst(static::$config['driver']);
			static::$instance = new $driver(static::$config);
		}

		return static::$instance;
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

	###########################################################################
	###   Abstract Methods                                         ############
	###   Must be implemented by drivers that extends this class   ############
	###########################################################################

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
	
	###########################################################################
	###   Query Building Methods   ############################################
	###########################################################################

	/**
	 * Indicate the columns to be retrieved in a query
	 *
	 * @return $this
	 */
	public function select()
	{
		$this->_query_build['select'] = func_get_args();
		unset($this->_query_build['where']);
		unset($this->_query_build['from']);
		return $this;
	}

	/**
	 * Indicate with table should be queried for results
	 *
	 * @return $this
	 */
	public function from($table)
	{
		$this->_query_build['from'] = $table;
		return $this;
	}

	/**
	 * Indicate conditions to the query
	 *
	 * @return $this
	 */
	public function where()
	{
		$this->_query_build['where'][] = array_merge(['AND'], func_get_args());
		return $this;
	}

	###########################################################################
	###   Abstract Query Resolving Methods                         ############
	###   Must be implemented by drivers that extends this class   ############
	###########################################################################

	/**
	 * Retrieve elements using the query
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	abstract public function get();

	/**
	 * Retrieve one element using the query
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	abstract public function getOne();
}