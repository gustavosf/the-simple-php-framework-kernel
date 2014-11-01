<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework\Tests;

use Framework\Database;
use PHPUnit_Framework_Assert as Assert;

/**
 * Database test cases.
 */
class DatabaseTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		Database::configure(['dsn' => 'sqlite::memory:']);
	}

	/**
	 * @covers Framework\Database::configure
	 */
	public function testConfigure()
	{
		$this->assertEquals(null, Assert::readAttribute('Framework\Database', 'instance'));
		$this->assertEquals(['dsn' => 'sqlite::memory:'], Assert::readAttribute('Framework\Database', 'config'));
	}

	/**
	 * @covers Framework\Database::instance
	 * @expectedException Framework\DatabaseException
	 */
	public function testInstanceException()
	{
		$instance = Database::instance(); # throws DatabaseException because
										  # there is no driver configured
	}

}
