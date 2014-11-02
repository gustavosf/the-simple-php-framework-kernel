<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

use Framework\Application;
use Framework\Request;

/**
 * Application test cases.
 */
class ApplicationTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers Framework\Application::get
	 */
	public function testGet()
	{
		$app = new Application();
		$app->get('/', function() {
			return 'ok';
		});
		$this->assertEquals('ok', $app->handle(Request::create('/')));
	}

	/**
	 * @covers Framework\Application::post
	 */
	public function testPost()
	{
		$app = new Application();
		$app->post('/', function() {
			return 'ok';
		});
		$this->assertEquals('ok', $app->handle(Request::create('/', 'POST')));
	}

	/**
	 * @covers Framework\Application::handle
	 * @covers Framework\Application::matchRoute
	 */
	public function testHandle()
	{
		$app = new Application();
		$app->get('/test/(\d+)', function($d) { return $d; });
		$app->post('/test/(\d+)', function($d) { return $d; });
		
		$this->assertEquals('15', $app->handle(Request::create('/test/15')));
		$this->assertEquals('15', $app->handle(Request::create('/test/15', 'POST')));

		try {
			$app->handle(Request::create('/'));
			$this->assertTrue(false);
		} catch (\Framework\HttpNotFoundException $e) {
			$this->assertTrue(true);
		}

		try {
			$app->handle(Request::create('/', 'PUT'));
			$this->assertTrue(false);
		} catch (\Framework\HttpNotFoundException $e) {
			$this->assertTrue(true);
		}

	}

}
