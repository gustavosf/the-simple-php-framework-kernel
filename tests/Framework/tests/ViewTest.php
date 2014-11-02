<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

use \ReflectionClass;
use Framework\View;
use Framework\ViewNotFoundException;
use PHPUnit_Framework_Assert as Assert;

/**
 * View test cases.
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers Framework\View::configure
	 */
	public function testConfigure()
	{
		View::configure(['path' => __DIR__.'/resources']);
		$this->assertEquals(['path' => __DIR__.'/resources'],
			Assert::readAttribute('Framework\View', 'config'));
	}

	/**
	 * @covers Framework\View::__construct
	 * @covers Framework\View::forge
	 */
	public function testConstructor()
	{
		$view = new View('file');
		$this->assertEquals('file', $view->getFile());
		$this->assertEquals([], $view->getData());

		$view = new View('file', ['foo' => 'bar']);
		$this->assertEquals(['foo' => 'bar'], $view->getData());

		$view = View::forge('file');
		$this->assertEquals('file', $view->getFile());
		$this->assertEquals([], $view->getData());

		$view = View::forge('file', ['foo' => 'bar']);
		$this->assertEquals(['foo' => 'bar'], $view->getData());
	}

	/**
	 * @covers Framework\View::getFile
	 * @covers Framework\View::getData
	 */
	public function testGetters()
	{
		$view = View::forge('file', ['foo' => 'bar']);
		$this->assertEquals('file', $view->getFile());
		$this->assertEquals(['foo' => 'bar'], $view->getData());
	}

	/**
	 * @covers Framework\View::set
	 */
	public function testSetter()
	{
		$view = View::forge('file');
		$this->assertEquals([], $view->getData());

		$view->set('foo', 'bar');
		$this->assertEquals(['foo' => 'bar'], $view->getData());
	}

	/**
	 * @covers Framework\View::fileResolver
	 */
	public function testFileResolver()
	{
		$reflection = new ReflectionClass('Framework\View');
		$method = $reflection->getMethod('fileResolver');
		$method->setAccessible(true);

		$this->assertEquals(__DIR__.'/resources/view.php',
			$method->invokeArgs(View::forge('view.php'), []));

		$this->assertEquals(__DIR__.'/resources/view.php',
			$method->invokeArgs(View::forge('view'), []));

		try
		{
			$method->invokeArgs(View::forge('invalid_file'), []);
			$this->assertTrue(false);
		}
		catch (ViewNotFoundException $e)
		{
			$this->assertTrue(true);
		}

	}

	/**
	 * @covers Framework\View::render
	 * @covers Framework\View::__toString
	 */
	public function testRender()
	{
		$view = View::forge('view.php', ['val' => '456']);
		$this->assertEquals('123456789', $view->render());
		$this->assertEquals('123456789', (string)$view);
	}

	/**
	 * @covers Framework\View::render
	 * @covers Framework\View::__toString
	 * @expectedException Framework\ViewNotFoundException
	 */
	public function testRenderViewNotFound()
	{

		$view = View::forge('invalid_file', ['val' => '456']);
		$this->assertEquals('', (string)$view);
		
		$view->render(); # Should throw an ViewNotFoundException
	}

	/**
	 * @covers Framework\View::render
	 * @covers Framework\View::__toString
	 * @expectedException \Exception
	 */
	public function testRenderException()
	{
		$view = View::forge('exceptional_view.php');
		$view->render(); # Should throw an Exception
	}

}
