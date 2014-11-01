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
 * Database Driver for XML
 * 
 * It simply loads xml files into an SQLite database in memory and reuses
 * PDO Driver methods to retrieve data
 */
class Xml extends Database\Driver\Pdo {
	
	/**
	 * Connect to the database
	 *
	 * @return void
	 * @throws DatabaseException
	 */
	public function connect()
	{
		if ($this->_connection) return;

		if (!isset($this->_config['path']))
			throw new DatabaseException('No path defined for XML database');

		if (!is_dir($this->_config['path']))
			throw new DatabaseException('Invalid directory for XML database');

		# Creates a sqlite database in memory
		$this->_connection = new \PDO('sqlite::memory:', null, null, [
			\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		]);
		
		# Load files into sqlite db
		$this->loadFiles($this->_config['path']);
	}

	/**
	 * Load files from the path into sqlite database
	 * 
	 * @param  string $path Path with xml files to be loaded
	 * @return void
	 */
	private function loadFiles($path)
	{
		$parse_table_name = function($path) { 
			preg_match('/\/([^\/]+?)\.xml/', $path, $match);
			return $match[1];
		};
		
		# Foreach xml file in the given path we will load it into the database
		foreach (glob($path.'/*.xml') as $file)
		{ 
			# Parse xml
			$xml_data = simplexml_load_file($file, null, LIBXML_NOCDATA);

			# Find table name and fields
			$table = $parse_table_name($file);
			$fields = array_reduce(current($xml_data), function($fields, $el) {
				$keys = array_keys(current($el->attributes()));
				return array_unique(array_merge($keys, $fields ?: []));
			});
			$fields = implode(',', $fields);

			# Create table schema
			$sql = "CREATE TABLE {$table} ({$fields})";
			$this->_connection->query($sql);

			# Load data using PDO connection
			foreach ($xml_data as $index => $element)
			{
				$attributes = current($element->attributes());
				$fields = implode(',', array_keys($attributes));
				$attribute_fields = ':'.implode(',:', array_keys($attributes));
				
				$sql = "INSERT INTO {$table} ({$fields}) VALUES ($attribute_fields)";
				$sth = $this->_connection->prepare($sql);
				$sth->execute($attributes);
			}
		}
	}
}