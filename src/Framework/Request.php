<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package	Framework
 * @author	Gustavo Seganfredo
 */

namespace Framework;

/**
 * Class representing an HTTP Request
 * 
 * An simplification of Fabien Potencier's Symfony\HttpFoundation\Request
 * @see https://github.com/symfony/HttpFoundation/blob/master/Request.php
 */
class Request
{

	/**
	 * GET parameters ($_GET)
	 * 
	 * @var object
	 */
	public $get;

	/**
	 * POST parameters ($_POST)
	 * 
	 * @var object
	 */
	public $post;

	/**
	 * Cookies set ($_COOKIES)
	 * 
	 * @var object
	 */
	public $cookies;

	/**
	 * Uploaded files ($_FILES)
	 * 
	 * @var object
	 */
	public $files;

	/**
	 * Server info ($_SERVER)
	 * 
	 * @var object
	 */
	public $server;

	/**
	 * Raw body content
	 * 
	 * @var string
	 */
	public $content;


	 /**
     * Constructor.
     *
     * @param array  $query   The GET parameters
     * @param array  $request The POST parameters
     * @param array  $cookies The COOKIE parameters
     * @param array  $files   The FILES parameters
     * @param array  $server  The SERVER parameters
     * @param string $content The raw body data
     */
	public function __construct(array $get = [], array $post = [], array $cookies = [], $files = [], $server = [], $content = null)
	{
		$this->get     = (object)$get;
		$this->post    = (object)$post;
		$this->cookies = (object)$cookies;
		$this->files   = (object)$files;
		$this->server  = (object)$server;
		$this->content = $content;
	}

	/**
	 * Creates a request from the server runtime
	 * 
	 * @return Request
	 */
	public static function createFromServer()
	{
		return new self($_GET, $_POST, $_COOKIE, $_FILES, $_SERVER);
	}

	
	/**
     * Creates a Request based on a given URI and configuration.
     *
     * The information contained in the URI always take precedence
     * over the other information (server and parameters).
     *
     * @param string $uri        The URI
     * @param string $method     The HTTP method
     * @param array  $parameters The query (GET) or request (POST) parameters
     * @param array  $cookies    The request cookies ($_COOKIE)
     * @param array  $files      The request files ($_FILES)
     * @param array  $server     The server parameters ($_SERVER)
     * @param string $content    The raw body data
     *
     * @return Request A Request instance
     */
	public static function create($uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
	{
		$server = array_replace(array(
			'SERVER_NAME' => 'localhost',
			'SERVER_PORT' => 80,
			'HTTP_HOST' => 'localhost',
			'HTTP_USER_AGENT' => 'Symfony/2.X',
			'HTTP_ACCEPT' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
			'HTTP_ACCEPT_LANGUAGE' => 'en-us,en;q=0.5',
			'HTTP_ACCEPT_CHARSET' => 'ISO-8859-1,utf-8;q=0.7,*;q=0.7',
			'REMOTE_ADDR' => '127.0.0.1',
			'SCRIPT_NAME' => '',
			'SCRIPT_FILENAME' => '',
			'SERVER_PROTOCOL' => 'HTTP/1.1',
			'REQUEST_TIME' => time(),
		), $server);

		$server['PATH_INFO'] = '';
		$server['REQUEST_METHOD'] = strtoupper($method);

		$components = parse_url($uri);
		if (isset($components['host'])) {
			$server['SERVER_NAME'] = $components['host'];
			$server['HTTP_HOST'] = $components['host'];
		}

		if (isset($components['scheme'])) {
			if ('https' === $components['scheme']) {
				$server['HTTPS'] = 'on';
				$server['SERVER_PORT'] = 443;
			} else {
				unset($server['HTTPS']);
				$server['SERVER_PORT'] = 80;
			}
		}

		if (isset($components['port'])) {
			$server['SERVER_PORT'] = $components['port'];
			$server['HTTP_HOST'] = $server['HTTP_HOST'].':'.$components['port'];
		}

		if (isset($components['user'])) {
			$server['PHP_AUTH_USER'] = $components['user'];
		}

		if (isset($components['pass'])) {
			$server['PHP_AUTH_PW'] = $components['pass'];
		}

		if (!isset($components['path'])) {
			$components['path'] = '/';
		}

		switch (strtoupper($method)) {
			case 'POST':
			case 'PUT':
			case 'DELETE':
				if (!isset($server['CONTENT_TYPE'])) {
					$server['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
				}
				// no break
			case 'PATCH':
				$request = $parameters;
				$query = [];
				break;
			default:
				$request = [];
				$query = $parameters;
				break;
		}

		$queryString = '';
		if (isset($components['query'])) {
			parse_str(html_entity_decode($components['query']), $qs);

			if ($query) {
				$query = array_replace($qs, $query);
				$queryString = http_build_query($query, '', '&');
			} else {
				$query = $qs;
				$queryString = $components['query'];
			}
		} elseif ($query) {
			$queryString = http_build_query($query, '', '&');
		}

		$server['REQUEST_URI'] = $components['path'].('' !== $queryString ? '?'.$queryString : '');
		$server['QUERY_STRING'] = $queryString;

		$server['PATH_INFO'] = $components['path'];

		return new self($query, $request, $cookies, $files, $server, $content);
	}

	/**
	 * Generates a normalized URI (URL) for the Request.
	 * 
	 * @return string
	 */
	public function getUri()
	{
		$uri = $this->server->HTTPS ? "https" : "http";
		$uri = "{$uri}://{$this->server->HTTP_HOST}";
		$uri = "{$uri}{$this->server->REQUEST_URI}";
		return $uri;
	}

}