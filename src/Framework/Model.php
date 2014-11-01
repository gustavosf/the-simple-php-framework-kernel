<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework;

use Framework\Support\Str;

/**
 * Model class
 */
abstract class Model implements \ArrayAccess, \Iterator
{
	/**
	 * Table name
	 * 
	 * @var string
	 */
	protected static $_table = null;

	/**
	 * Default primary key
	 * 
	 * @var string
	 */
	protected static $_primary_key = 'id';

	/**
	 * Model data
	 * 
	 * @var array
	 */
	protected $_data;

	/**
	 * Model's original data
	 * 
	 * @var array
	 */
	protected $_original_data;

	###########################################################################
	###   Constructor   #######################################################
	###########################################################################

	/**
	 * Constructor
	 * 
	 * @param array $data
	 */
	public function __construct($data = [])
	{
		$this->_data = $data;
	}

	/**
	 * Creates a new model instance and registers the original data for it
	 * 
	 * @param  array $data Model attributes
	 * @return object      Model instance
	 */
	public static function make($data)
	{
		$instance = new static($data);
		$instance->_original_data = $data;
		return $instance;
	}

	###########################################################################
	###   Finder Methods   ####################################################
	###########################################################################

	/**
	 * Find a register in the database for this model
	 * 
	 * @param  mixed  $id ID
	 * @return object
	 */
	public static function find($id)
	{
		return static::make(Database::instance()
			->select()
			->from(static::resolveTable())
			->where(static::$_primary_key, $id)
			->getOne());
	}

	/**
	 * Finds all registers in the database for this model
	 * 
	 * @return object
	 */
	public static function all()
	{
		$data = Database::instance()
			->select()
			->from(static::resolveTable())
			->get();
		
		return array_map(function($d) {
			return static::make($d);
		}, $data);
	}

	###########################################################################
	###   Helper Methods   ####################################################
	###########################################################################

	/**
	 * Recovers the respective table for this model
	 * 
	 * @return string
	 */
	protected static function resolveTable()
	{
		# If a table name is previously configured
		if (static::$_table) return static::$_table;

		# Otherwise, use convention over configuration
		return str_replace('\\', '', Str::snake(Str::plural(get_called_class())));
	}

	###########################################################################
	###   Getter & Setter via magic methods   #################################
	###########################################################################

	/**
	 * Magic method mapped to model attributes
	 * 
	 * @param  string $parameter
	 * @return mixed
	 */
	public function __get($parameter)
	{
		if (isset($this->_data[$parameter])) return $this->_data[$parameter];
	}

	/**
	 * Magic method mapped to model attributes
	 * 
	 * @param  string $parameter
	 * @param  mixed  $value
	 * @return mixed
	 */
	public function __set($parameter, $value)
	{
		$this->_data[$parameter] = $value;
	}

	/**
	 * Magic method mapped to model attributes
	 * 
	 * @param  string $parameter
	 * @return mixed
	 */
	public function __isset($parameter)
	{
		return isset($this->_data[$parameter]);
	}

	/**
	 * Magic method mapped to model attributes
	 * 
	 * @param  string $parameter
	 * @return mixed
	 */
	public function __unset($parameter)
	{
		unset($this->_data[$parameter]);
	}

	###########################################################################
	###   Implementation of ArrayAccess   #####################################
	###########################################################################

	public function offsetSet($offset, $value)
	{
		$this->__set($offset, $value);
	}

	public function offsetExists($offset)
	{
		return $this->__isset($offset);
	}

	public function offsetUnset($offset)
	{
		$this->__unset($offset);
	}

	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	###########################################################################
	###   Implementation of Iterable   ########################################
	###########################################################################

	public function rewind()
	{
		reset($this->_data);
	}

	public function current()
	{
		return current($this->_data);
	}

	public function key()
	{
		return key($this->_data);
	}

	public function next()
	{
		return next($this->_data);
	}

	public function valid()
	{
		return key($this->_data) !== null;
	}

}