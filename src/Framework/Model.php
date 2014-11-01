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

	protected static $_table = null;
	protected static $_primary_key = 'id';

	protected $_data;
	protected $_original_data;

	public function __construct($data)
	{
		$this->_data = $data;
	}

	public static function find($id)
	{
		return static::make(Database::instance()
			->select()
			->from(static::resolveTable())
			->where(static::$_primary_key, $id)
			->getOne());
	}

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

	public static function make($data)
	{
		$instance = new static($data);
		$instance->_original_data = $data;
		return $instance;
	}

	protected static function resolveTable()
	{
		# If a table name is previously configured
		if (static::$_table) return static::$_table;

		# Otherwise, use convention over configuration
		return str_replace('\\', '', Str::snake(Str::plural(get_called_class())));
	}

	public function __get($parameter)
	{
		if (isset($this->_data[$parameter])) return $this->_data[$parameter];
	}
}