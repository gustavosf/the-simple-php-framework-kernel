<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework;

/**
 * Controller que gerencia assuntos relacionados às entrevistas de doutorado
 * 
 * @todo include other HTTP methods, as PUT, DELETE, etc, etc...
 */
class Application {

	/**
	 * Routes for this application
	 * @var array
	 */
	protected $routes = ['get' => [], 'post' => []];

	
	###########################################################################
	###   Constructor   #######################################################
	###########################################################################
	
	/**
	 * Instance to a new application
	 */
	public function __construct() {}


	###########################################################################
	###   Route mappers   #####################################################
	###########################################################################
	
	/**
	 * Maps a GET route
	 * @param  string   $route    Matched route pattern
	 * @param  function $function Controller
	 * @return $this
	 */
	public function get($route, $function)
	{
		$this->routes['get'][$route] = $function;
		return $this;
	}

	/**
	 * Maps a POST route
	 * @param  string   $route    Matched route pattern
	 * @param  function $function Controller
	 * @return $this
	 */
	public function post($route, $function)
	{
		$this->routes['post'][$route] = $function;
		return $this;
	}

	###########################################################################
	###   Request handler   ###################################################
	###########################################################################

	/**
	 * Handle a request
	 * 
	 * Find the related controller for a request, execute it or throw 404
	 * exception in case of no route found
	 * 
	 * @param  Request $req Request to be handled
	 * @return void
	 * @throws HttpNotFoundException
	 */
	public function handle(Request $req)
	{
		if ($req->server->REQUEST_METHOD == 'GET') $routes = $this->routes['get'];
		elseif ($req->server->REQUEST_METHOD == 'POST') $routes = $this->routes['post'];
		else $routes = [];

		$route = $this->matchRoute($req->server->PATH_INFO, $routes);

		if ($route === null) throw new HttpNotFoundException;
		return call_user_func_array($route[0], $route[1]);
	}

	/**
	 * Tries to find a route for a given pathpattern, given a list of routes
	 * 
	 * @param  string $pattern Regex pattern
	 * @param  array  $routes  List of available routes
	 * @return array           [route, parameters]
	 */
	protected function matchRoute($path, array $routes)
	{
		foreach ($routes as $route_pattern => $route)
		{
			if (preg_match('@^'.$route_pattern.'$@', $path, $matches))
			{
				array_shift($matches);
				return [$route, $matches];
			}
		}
		return null;
	}
	
	###########################################################################
	###   Application boot   ##################################################
	###########################################################################

	/**
	 * Runs the application on a server
	 * 
	 * @param  string $environment [description]
	 * @return void
	 */
	public function run()
	{
		$request = Request::createFromServer();
		return $this->handle($request);
	}

}