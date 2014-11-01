<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework\Tests\Database\Driver;

use Framework\Database;
use PHPUnit_Framework_Assert as Assert;

/**
 * Database test cases.
 */
class XmlTest extends \PHPUnit_Framework_TestCase
{

	protected static $config = ['driver' => 'xml', 'path' => '/home/gust/Desktop/xmldb'];
	
	public static function setUpBeforeClass()
	{
        Database::configure(static::$config);
	}

	/**
	 * @covers Framework\Database\Driver\Xml::connect
	 * @expectedException Framework\DatabaseException
	 */
	public function testConnectionExceptionNoPath()
	{
		Database::configure(['driver' => 'xml']); # no path
		$instance = Database::instance();
		$instance->connect();
	}

	/**
	 * @covers Framework\Database\Driver\Xml::connect
	 * @expectedException Framework\DatabaseException
	 */
	public function testConnectionExceptionInvalidPath()
	{
		Database::configure(['driver' => 'xml', 'path' => 'invalid_path']); # no path
		$instance = Database::instance();
		$instance->connect();
	}

	/**
	 * @covers Framework\Database\Driver\Xml::connect
	 * @covers Framework\Database\Driver\Xml::loadFiles
	 */
	public function testConnection()
	{
		Database::configure(['driver' => 'xml', 'path' => __DIR__.'/../../resources/xmldb']); # no path
		$instance = Database::instance();
		$instance->connect();
		$this->assertTrue(Assert::readAttribute($instance, '_connection') instanceof \PDO);
	}

	/**
	 * @covers Framework\Database\Driver\Xml::connect
	 * @covers Framework\Database\Driver\Xml::loadFiles
	 */
	public function testInheritance()
	{
		Database::configure(['driver' => 'xml', 'path' => __DIR__.'/../../resources/xmldb']); # no path
		$instance = Database::instance();
		$instance->connect();
		$this->assertTrue(Assert::readAttribute($instance, '_connection') instanceof \PDO);

		$data = $instance->select('id', 'name')->from('users')->where('name', 'like', 'B%')->get();
		$this->assertEquals([['id' => '2', 'name' => 'Bauhar'], ['id' => '6', 'name' => 'Bertrand']], $data);

		$data = $instance->select('revenue')->from('accounts')->where('owner_id', '>=', '7')->get();
		$this->assertEquals([['revenue' => '0'], ['revenue' => '15']], $data);
	}

}
