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
	 * 
	 * @var array
	 */
	protected $routes = ['get' => [], 'post' => []];

	/**
	 * List of paths for this apps
	 * 
	 * @var array
	 */
	protected $paths = [];

	/**
	 * Cache for configuration files
	 * 
	 * @var array
	 */
	protected $config_cache = [];

	/**
	 * Environment definition
	 * 
	 * @var string
	 */
	public $environment;

	###########################################################################
	###   Constructor   #######################################################
	###########################################################################
	
	/**
	 * Instance to a new application
	 * 
	 * We can specify an environment for this application. The environment
	 * will determine which configuration files are loaded, debug options and
	 * so on.
	 * 
	 * @param string $environment
	 */
	public function __construct($environment = 'development')
	{
		$this->environment = $environment;
	}

	###########################################################################
	###   Route mappers   #####################################################
	###########################################################################
	
	/**
	 * Maps a GET route
	 * 
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
	 * 
	 * @param  string   $route    Matched route pattern
	 * @param  function $function Controller
	 * @return $this
	 */
	public function post($route, $function)
	{
		$this->routes['post'][$route] = $function;
		return $this;
	}

	/**
	 * Maps a ERROr route (404 for example)
	 * 
	 * @param  integer  $error    Errro code
	 * @param  function $function Controller
	 * @return $this
	 */
	public function error($error, $function)
	{
		$this->routes['error'][$error] = $function;
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

	protected function handle404()
	{
		http_response_code(404);
		$route = $this->matchRoute('404', $this->routes['error'] ?: []);
		if ($route === null) return '404!';
		return call_user_func_array($route[0], $route[1]);
	}

	###########################################################################
	###   Getters and Setters   ###############################################
	###########################################################################
	
	/**
	 * Register application paths
	 * 
	 * @param  array $paths
	 * @return void
	 */
	public function registerPaths($paths)
	{
		$this->paths = $paths;
	}

	/**
	 * Returns a path for a given resource
	 * @param  string $resource
	 * @return string
	 */
	public function getPath($resource)
	{
		return isset($this->paths[$resource]) ? $this->paths[$resource] : null;
	}

	/**
	 * Resolve configuration files, keeping track of which environment the
	 * application is running.
	 * 
	 * @param  string $file
	 * @return mixed
	 */
	public function getConfig($file)
	{
		if (!isset($this->config_cache[$file]))
		{
			$config = [];
			$config_path = $this->paths['config'];

			# Get the configuration
			$path = "{$config_path}/{$file}.php";
			if (file_exists($path))
				$config = array_merge($config, (include $path));

			# Merge with environment-specific configuration files
			$path = "{$config_path}/{$this->environment}/{$file}.php";
			if (file_exists($path))
				$config = array_merge($config, (include $path));

			# Cache configuration
			$this->config_cache[$file] = $config;
		}

		return $this->config_cache[$file];
	}
	
	###########################################################################
	###   Application boot   ##################################################
	###########################################################################

	/**
	 * Runs the application on a server
	 * 
	 * @return void
	 */
	public function run()
	{
		$request = Request::createFromServer();
		try
		{
			$response = $this->handle($request);
		}
		catch (HttpNotFoundException $e)
		{
			$response = $this->handle404();
		}

		echo $response;
	}

}