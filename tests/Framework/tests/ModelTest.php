<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

use \ReflectionClass;
use Framework\Model;
use Framework\Database;
use PHPUnit_Framework_Assert as Assert;

/**
 * Mock models for testing
 */
class User extends Model {}
class Quiz extends Model {}
class UserAccount extends Model {}
class Usuario extends Model
{ 
	protected static $_table = 'users';
	protected static $_primary_key = 'name';
}

/**
 * Model test cases.
 */
class ModelTest extends \PHPUnit_Framework_TestCase
{

	protected static $config;

	public static function setUpBeforeClass()
	{
		static::$config = ['driver' => 'xml', 'path' => __DIR__.'/resources/xmldb'];
		Database::configure(static::$config);
	}

	/**
	 * @covers Framework\Model::__construct
	 */
	public function testConstruct()
	{
		$user_data = ['id' => 1, 'name' => 'Gustavo', 'surname' => 'Seganfredo'];
		$user = new User($user_data);

		$this->assertEquals(1, $user->id);
		$this->assertEquals('Gustavo', $user->name);
		$this->assertEquals('Seganfredo', $user->surname);
		$this->assertEquals($user_data, Assert::readAttribute($user, '_data'));
	}

	/**
	 * @covers Framework\Model::resolveTable
	 */
	public function testResolveTable()
	{
		$reflection = new ReflectionClass('User');
		$method = $reflection->getMethod('resolveTable');
		$method->setAccessible(true);
		$this->assertEquals('users', $method->invokeArgs((new User), []));

		$reflection = new ReflectionClass('Quiz');
		$method = $reflection->getMethod('resolveTable');
		$method->setAccessible(true);
		$this->assertEquals('quizzes', $method->invokeArgs((new Quiz), []));

		$reflection = new ReflectionClass('UserAccount');
		$method = $reflection->getMethod('resolveTable');
		$method->setAccessible(true);
		$this->assertEquals('user_accounts', $method->invokeArgs((new UserAccount), []));

		$reflection = new ReflectionClass('Usuario');
		$method = $reflection->getMethod('resolveTable');
		$method->setAccessible(true);
		$this->assertEquals('users', $method->invokeArgs((new Usuario), []));
	}

	/**
	 * @covers Framework\Model::find
	 * @covers Framework\Model::make
	 * @covers Framework\Model::__get
	 */
	public function testFind()
	{
		$user = User::find(1);
		$this->assertInstanceOf('User', $user);
		$this->assertEquals(1, $user->id);
		$this->assertEquals('Dalana', $user->name);
		$this->assertEquals('Jenkins', $user->lastName);
		$this->assertEquals('dalana', $user->username);
		$this->assertEquals('asdf', $user->password);
		$this->assertEquals('dalana@inmemorian.com', $user->email);
		$this->assertEquals(1, $user->status);
		$this->assertNull($user->not_a_parameter);

		# Testing with unusual primary key (name)
		$user = Usuario::find('Dalana');
		$this->assertInstanceOf('Usuario', $user);
		$this->assertEquals('Dalana', $user->name);
	}

	/**
	 * @covers Framework\Model::all
	 * @covers Framework\Model::make
	 */
	public function testAll()
	{
		$users = User::all();
		$this->assertEquals(9, count($users));
		$this->assertInstanceOf('User', current($users));
	}

	/**
	 * @covers Framework\Model::offsetGet
	 * @covers Framework\Model::__get
	 * @covers Framework\Model::offsetSet
	 * @covers Framework\Model::__set
	 * @covers Framework\Model::offsetExists
	 * @covers Framework\Model::__isset
	 * @covers Framework\Model::offsetUnset
	 * @covers Framework\Model::__unset
	 */
	public function testArrayAccess()
	{
		$user = User::find(1);
		
		# offsetGet
		$this->assertEquals('Dalana', $user['name']);
		$this->assertNull($user['invalid_property']);

		# offsetSet
		$user['name'] = 'Gustavo';
		$this->assertEquals('Gustavo', $user['name']);

		# offsetExists
		$this->assertEquals(true, isset($user['name']));
		$this->assertEquals(false, isset($user['invalid_property']));

		# offsetUnset
		unset($user['name']);
		$this->assertEquals(false, isset($user['name']));
	}

	/**
	 * @covers Framework\Model::current
	 * @covers Framework\Model::key
	 * @covers Framework\Model::next
	 * @covers Framework\Model::rewind
	 * @covers Framework\Model::valid
	 */
	public function testIterable()
	{
		$user = User::find(1);
		
		$this->assertEquals(1, $user->current());
		$this->assertEquals('id', $user->key());

		$user->next(); $this->assertEquals('name', $user->key());
		$user->rewind(); $this->assertEquals('id', $user->key());
		$this->assertTrue($user->valid());
	}

}
