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

		$file = $this->file_resolver($this->file);
		return $sterilized_room($this->file, $this->data);
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
	 * Checks if a file exists.
	 * 
	 * Handles relative paths
	 * 
	 * @return string real file path
	 * @todo Handle template engines (twig, etc)
	 */
	protected function file_resolver()
	{
		if (file_exists($this->file)) return $this->file;
		if (file_exists($this->file.'.php')) return "{$this->file}.php";
		if (file_exists(__DIR__.'/'.$this->file)) return __DIR__."/{$this->file}";
		if (file_exists(__DIR__.'/'.$this->file.'.php')) return __DIR__."/{$this->file}.php";

		throw new ViewNotFoundException;
	}
}

/**
 * Exception for not found views
 */
class ViewNotFoundException extends \Exception {}