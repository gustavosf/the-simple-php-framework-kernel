<?php

/**
 * This file is part of The Simple PHP Framework
 *
 * @package Framework
 * @author  Gustavo Seganfredo
 */

namespace Framework;

/**
 * View class
 * 
 * @example
 *   $view = new View('index', ['foo' => 'bar'])
 *   $view->render();
 * @example
 *   View::forge('index', ['foo' => 'bar'])->render();
 * 
 * @todo Handle template engines (twig, etc)
 */
class View {

	/**
	 * View's file path
	 * @var string
	 */
	protected $file;

	/**
	 * Data to be included in view
	 * @var array
	 */
	protected $data;

	/**
	 * Component configuration
	 * 
	 * @var array
	 */
	protected static $config = ['path' => '.'];

	###########################################################################
	###   Constructor   #######################################################
	###########################################################################

	/**
	 * Constructor
	 * 
	 * @param  string $file View's file path
	 * @param  array  $data Data to be included in view
	 * @return $this
	 */
	public function __construct($file, array $data = [])
	{
		$this->file = $file;
		$this->data = $data;
	}

	/**
	 * Static constructor for method chaining
	 * 
	 * @return View
	 * @see View::__construct
	 */
	public static function forge($file, array $data = [])
	{
		return new self($file, $data);
	}

	###########################################################################
	###   Renderers   #########################################################
	###########################################################################

	/**
	 * Render the view
	 * 
	 * @return string
	 * @throws \Exception
	 * @throws \ViewNotFoundException
	 */
	public function render()
	{
		# Simple closure to avoid scope contamination
		$sterilized_room = function($__file__, $__data__)
		{
			extract($__data__, EXTR_REFS);
			ob_start();
			try
			{
				include $__file__;
			}
			catch (\Exception $e)
			{
				ob_end_clean();
				throw $e;
			}
			return ob_get_clean();
		};

		$file = $this->fileResolver($this->file);
		return $sterilized_room($file, $this->data);
	}

	/**
	 * Textual representation of the view.
	 * 
	 * @return string Rendered view
	 * @todo Handle exceptions
	 */
	public function __toString()
	{
		try
		{
			return $this->render();
		}
		catch (\Exception $e)
		{
			return '';
		}
	}

	###########################################################################
	###   Getters   ###########################################################
	###########################################################################

	/**
	 * Getter for $this->file
	 * 
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * Getter for $this->data
	 * 
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	###########################################################################
	###   Setter   #$$$$$######################################################
	###########################################################################

	/**
	 * Setter for $this->data
	 * 
	 * @return void
	 */
	
	public function set($parameter, $data)
	{
		$this->data[$parameter] = $data;
	}

	/**
	 * Sets configuration for the view component
	 * 
	 * @param  array $config
	 * @return void
	 */
	public static function configure($config)
	{
		static::$config = $config;
	}

	###########################################################################
	###   Helper Methods   ####################################################
	###########################################################################

	/**
	 * Checks if a file exists.
	 * 
	 * Handles relative paths
	 * 
	 * @return string real file path
	 * @todo Handle template engines (twig, etc)
	 */
	protected function fileResolver()
	{
		$path = static::$config['path'].'/'.$this->file;
		if (file_exists($path)) return $path;
		if (file_exists($path.'.php')) return "{$path}.php";
		throw new ViewNotFoundException;
	}
}