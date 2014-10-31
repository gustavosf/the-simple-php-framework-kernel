<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework\Tests;

use Framework\View;

/**
 * View test cases.
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{

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
	 * @covers Framework\View::render
	 * @covers Framework\View::__toString
	 * @covers Framework\View::file_resolver
	 */
	public function testRender()
	{
		$view = View::forge(__DIR__.'/resources/view.php', ['val' => '456']);
		$this->assertEquals('123456789', $view->render());
		$this->assertEquals('123456789', (string)$view);
	}

	/**
	 * @covers Framework\View::render
	 * @covers Framework\View::__toString
	 * @covers Framework\View::file_resolver
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
	 * @covers Framework\View::file_resolver
	 * @expectedException \Exception
	 */
	public function testRenderException()
	{
		$view = View::forge(__DIR__.'/resources/exceptional_view.php');
		$view->render(); # Should throw an Exception
	}

}
