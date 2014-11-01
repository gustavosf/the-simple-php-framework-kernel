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
abstract class Model {

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
}