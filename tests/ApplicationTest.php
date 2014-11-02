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
	 * @covers Framework\Application::__construct
	 */
	public function testConstruct()
	{
		$app = new Application();
		$this->assertEquals('development', $app->environment);

		$app = new Application('test');
		$this->assertEquals('test', $app->environment);
	}

	/**
	 * @covers Framework\Application::registerPaths
	 * @covers Framework\Application::getPath
	 */
	public function testRegisterAndGetPaths()
	{
		$paths = [
			'views'  => 'resources',
			'config' => 'resources/config'
		];

		$app = new Application();
		$app->registerPaths($paths);
		$this->assertEquals('resources', $app->getPath('views'));
		$this->assertEquals('resources/config', $app->getPath('config'));
	}

	/**
	 * @covers Framework\Application::getConfig
	 */
	public function testGetConfig()
	{
		$paths = ['config' => __DIR__.'/resources/config'];

		$app = new Application(); $app->registerPaths($paths);
		$expected = ['app' => 'The Simple Framework', 'env' => 'default'];
		$this->assertEquals($expected, $app->getConfig('test'));

		$app = new Application('dev'); $app->registerPaths($paths);
		$expected = ['app' => 'The Simple Framework', 'env' => 'dev'];
		$this->assertEquals($expected, $app->getConfig('test'));

		$app = new Application('prod'); $app->registerPaths($paths);
		$expected = ['app' => 'The Simple Framework', 'env' => 'prod', 'additional' => 'secure'];
		$this->assertEquals($expected, $app->getConfig('test'));
	}

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

	protected function emulateServerRequest($app, $uri)
	{
		$_SERVER = (array)Request::create($uri)->server;
		ob_start();
		$app->run(); 
		return ob_get_clean();
	}

	/**
	 * @covers Framework\Application::run
	 */
	public function testRun()
	{
		$app = new Application();
		$app->get('/', function() { return 'ok'; });
		$this->assertEquals('ok', $this->emulateServerRequest($app, '/'));
	}

	/**
	 * @covers Framework\Application::run
	 * @covers Framework\Application::handle404
	 * @covers Framework\Application::error
	 */
	public function testHandle404()
	{
		$app = new Application();
		$this->assertEquals('404!', $this->emulateServerRequest($app, '/error'));

		$app->error(404, function() { return 'custom 404!'; });
		$this->assertEquals('custom 404!', $this->emulateServerRequest($app, '/error'));
	}

}
