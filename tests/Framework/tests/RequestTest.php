<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework\Tests;

use Framework\Request;

/**
 * Application test cases.
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

	/**
	 * @covers Framework\Request::__construct
	 */
	public function testConstructor()
	{
		$request = new Request(['foo' => 'bar']);
		$this->assertEquals('bar', $request->get->foo);

		$request = new Request([], ['foo' => 'bar']);
		$this->assertEquals('bar', $request->post->foo);

		$request = new Request([], [], ['foo' => 'bar']);
		$this->assertEquals('bar', $request->cookies->foo);

		$files = ['foo' => ['name' => 'bar'], 'fooo' => ['name' => 'baz']];
		$request = new Request([], [], [], $files);
		$this->assertEquals('bar', $request->files->foo['name']);
		$this->assertEquals('baz', $request->files->fooo['name']);

		# $_SERVER will be tested shortly

		$request = new Request([], [], [], [], [], 'foo! bar!');
		$this->assertEquals('foo! bar!', $request->content);
	}

	/**
	 * @covers Framework\Request::create
	 * @covers Framework\Request::getUri
	 */
	public function testCreate()
	{
		$request = Request::create('http://test.com/foo?bar=baz');
		$this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('bar=baz', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('http://test.com/foo', 'GET', ['bar' => 'baz']);
		$this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('bar=baz', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('http://test.com/foo?bar=foo', 'GET', ['bar' => 'baz']);
		$this->assertEquals('http://test.com/foo?bar=baz', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('bar=baz', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('https://test.com/foo?bar=baz');
		$this->assertEquals('https://test.com/foo?bar=baz', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('bar=baz', $request->server->QUERY_STRING);
		$this->assertEquals(443, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('test.com:90/foo');
		$this->assertEquals('http://test.com:90/foo', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('test.com', $request->server->SERVER_NAME);
		$this->assertEquals('test.com:90', $request->server->HTTP_HOST);
		$this->assertEquals(90, $request->server->SERVER_PORT);

		$request = Request::create('https://test.com:90/foo');
		$this->assertEquals('https://test.com:90/foo', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('test.com', $request->server->SERVER_NAME);
		$this->assertEquals('test.com:90', $request->server->HTTP_HOST);
		$this->assertEquals(90, $request->server->SERVER_PORT);

		$request = Request::create('https://127.0.0.1:90/foo');
		$this->assertEquals('https://127.0.0.1:90/foo', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('127.0.0.1', $request->server->SERVER_NAME);
		$this->assertEquals('127.0.0.1:90', $request->server->HTTP_HOST);
		$this->assertEquals(90, $request->server->SERVER_PORT);

		$request = Request::create('https://[::1]:90/foo');
		$this->assertEquals('https://[::1]:90/foo', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('[::1]', $request->server->SERVER_NAME);
		$this->assertEquals('[::1]:90', $request->server->HTTP_HOST);
		$this->assertEquals(90, $request->server->SERVER_PORT);

		$request = Request::create('https://[::1]/foo');
		$this->assertEquals('https://[::1]/foo', $request->getUri());
		$this->assertEquals('/foo', $request->server->PATH_INFO);
		$this->assertEquals('[::1]', $request->server->SERVER_NAME);
		$this->assertEquals('[::1]', $request->server->HTTP_HOST);
		$this->assertEquals(443, $request->server->SERVER_PORT);

		$json = '{"jsonrpc":"2.0","method":"echo","id":7,"params":["Hello World"]}';
		$request = Request::create('http://example.com/jsonrpc', 'POST', [], [], [], [], $json);
		$this->assertEquals($json, $request->content);

		$request = Request::create('http://test.com');
		$this->assertEquals('http://test.com/', $request->getUri());
		$this->assertEquals('/', $request->server->PATH_INFO);
		$this->assertEquals('', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('http://test.com?test=1');
		$this->assertEquals('http://test.com/?test=1', $request->getUri());
		$this->assertEquals('/', $request->server->PATH_INFO);
		$this->assertEquals('test=1', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);

		$request = Request::create('http://test.com:90/?test=1');
		$this->assertEquals('http://test.com:90/?test=1', $request->getUri());
		$this->assertEquals('/', $request->server->PATH_INFO);
		$this->assertEquals('test=1', $request->server->QUERY_STRING);
		$this->assertEquals(90, $request->server->SERVER_PORT);
		$this->assertEquals('test.com:90', $request->server->HTTP_HOST);

		$request = Request::create('http://username:password@test.com');
		$this->assertEquals('http://test.com/', $request->getUri());
		$this->assertEquals('/', $request->server->PATH_INFO);
		$this->assertEquals('', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);
		$this->assertEquals('username', $request->server->PHP_AUTH_USER);
		$this->assertEquals('password', $request->server->PHP_AUTH_PW);

		$request = Request::create('http://username@test.com');
		$this->assertEquals('http://test.com/', $request->getUri());
		$this->assertEquals('/', $request->server->PATH_INFO);
		$this->assertEquals('', $request->server->QUERY_STRING);
		$this->assertEquals(80, $request->server->SERVER_PORT);
		$this->assertEquals('test.com', $request->server->HTTP_HOST);
		$this->assertEquals('username', $request->server->PHP_AUTH_USER);
		$this->assertEquals('', $request->server->PHP_AUTH_PW);

		$request = Request::create('http://test.com/?foo');
		$this->assertEquals('/?foo', $request->server->REQUEST_URI);
	}

	/**
	 * @covers Framework\Request::createFromServer
	 */
	public function testCreateFromServer()
	{
		$_GET['foo1'] = 'bar1';
		$_POST['foo2'] = 'bar2';
		$_COOKIE['foo3'] = 'bar3';
		$_FILES['foo4'] = ['bar4'];
		$_SERVER['foo5'] = 'bar5';

		$request = Request::createFromServer();
		$this->assertEquals('bar1', $request->get->foo1, '::fromGlobals() uses values from $_GET');
		$this->assertEquals('bar2', $request->post->foo2, '::fromGlobals() uses values from $_POST');
		$this->assertEquals('bar3', $request->cookies->foo3, '::fromGlobals() uses values from $_COOKIE');
		$this->assertEquals(['bar4'], $request->files->foo4, '::fromGlobals() uses values from $_FILES');
		$this->assertEquals('bar5', $request->server->foo5, '::fromGlobals() uses values from $_SERVER');
	}
}
