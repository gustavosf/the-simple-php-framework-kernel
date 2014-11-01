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
use ReflectionClass;

/**
 * Database test cases.
 */
class PdoTest extends \PHPUnit_Framework_TestCase
{

	protected static $config = ['driver' => 'pdo', 'dsn' => 'sqlite::memory:'];

	public static function setUpBeforeClass()
	{
		Database::configure(static::$config);
	}

	protected function setUpData()
	{
		$instance = Database::instance();
		$connection = Assert::readAttribute($instance, '_connection');
		
		# Sets up database
		$connection->query('CREATE TABLE users (id int(13), name varchar(255), surname varchar(255))');
		$connection->query('INSERT INTO users VALUES (1, "foo", "bar"), (2, "fee", "baz"), (3, "faz", "bee")');
	}

	/**
	 * @covers Framework\Database::instance
	 * @covers Framework\Database::__construct
	 * @covers Framework\Database\Driver\Pdo::connect
	 */
	public function testInstance()
	{
		$instance = Database::instance();
		$this->assertEquals($instance,
			Assert::readAttribute('Framework\Database', 'instance'));
		$this->assertEquals(Assert::readAttribute('Framework\Database', 'config'),
			Assert::readAttribute($instance, '_config'));
	}

	/**
	 * @covers Framework\Database::select
	 */
	public function testSelect()
	{
		$instance = Database::instance();

		$instance->select('a', 'b');
		$attr = Assert::readAttribute($instance, '_query_build');
		$this->assertEquals(['a', 'b'], $attr['select']);

		$instance->select();
		$attr = Assert::readAttribute($instance, '_query_build');
		$this->assertEquals([], $attr['select']);
	}

	/**
	 * @covers Framework\Database::from
	 */
	public function testFrom()
	{
		$instance = Database::instance();

		$instance->from('table');
		$attr = Assert::readAttribute($instance, '_query_build');
		$this->assertEquals('table', $attr['from']);
	}

	/**
	 * @covers Framework\Database::where
	 */
	public function testWhere()
	{
		$instance = Database::instance();

		$instance->where('id', 1);
		$attr = Assert::readAttribute($instance, '_query_build');
		$this->assertContains(['AND', 'id', 1], $attr['where']);

		$instance->where('id', '<>', 1);
		$attr = Assert::readAttribute($instance, '_query_build');
		$this->assertContains(['AND', 'id', '<>', 1], $attr['where']);
	}

	/**
	 * @covers Framework\Database::__destruct
	 * @covers Framework\Database\Driver\Pdo::disconnect
	 */
	public function testDestruct()
	{
		$instance = Database::instance();
		$instance->__destruct();

		$this->assertNull(Assert::readAttribute($instance, '_connection'));
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::connect
	 */
	public function testConnect()
	{
		$instance = Database::instance();
		$instance->connect();
		$this->assertInstanceOf('PDO', Assert::readAttribute($instance, '_connection'));
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::get
	 * @covers Framework\Database\Driver\Pdo::query
	 * @covers Framework\Database\Driver\Pdo::mountQuery
	 * @expectedException Framework\DatabaseException
	 */
	public function testGetException()
	{
		$instance = Database::instance();
		$instance->get();
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::get
	 * @covers Framework\Database\Driver\Pdo::mountQuery
	 * @covers Framework\Database\Driver\Pdo::query
	 */
	public function testGet()
	{
		$this->setUpData();

		# Set mountQuery as accessible
		$reflection = new ReflectionClass('Framework\Database\Driver\Pdo');
		$method = $reflection->getMethod('mountQuery');
		$method->setAccessible(true);

		$instance = Database::instance();
		
		$instance->select()->from('users');
		$this->assertEquals('SELECT * FROM users', $method->invokeArgs($instance, []));

		$instance->select('id', 'name')->from('users');
		$this->assertEquals('SELECT id,name FROM users', $method->invokeArgs($instance, []));

		$instance->select()->from('users')->where('id', 1);
		$this->assertEquals('SELECT * FROM users WHERE id = \'1\'', $method->invokeArgs($instance, []));

		$instance->select()->from('users')->where('id', '<>', 1);
		$this->assertEquals('SELECT * FROM users WHERE id <> \'1\'', $method->invokeArgs($instance, []));

		$instance->select()->from('users')->where('id', '>', 0)->where('id', '<', 2);
		$this->assertEquals('SELECT * FROM users WHERE id > \'0\' AND id < \'2\'', $method->invokeArgs($instance, []));

		$return = $instance->get();
		$this->assertEquals([['id' => '1', 'name' => 'foo', 'surname' => 'bar']], $return);
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::getOne
	 */
	public function testGetOne()
	{
		$instance = Database::instance();
		
		$return = $instance->select('id')->from('users')->where('id', '<=', 2)->getOne();
		$this->assertEquals(['id' => '1'], $return);
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::get
	 * @covers Framework\Database\Driver\Pdo::mountquery
	 * @expectedException Framework\DatabaseException
	 */
	public function testInvalidQuery()
	{
		Database::instance()->get();
	}

	/**
	 * @covers Framework\Database\Driver\Pdo::connect
	 * @expectedException Framework\DatabaseException
	 */
	public function testConnectException()
	{
		Database::configure(['driver' => 'pdo', 'dsn' => 'sqlite']);
		Database::instance()->connect();
	}

}
