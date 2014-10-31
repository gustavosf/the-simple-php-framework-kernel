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
 * Example usage:
 *   $view = new View('index', ['foo' => 'bar'])
 *   $view->render();
 * 
 * Example with method chaining:
 *   View::forge('index', ['foo' => 'bar'])->render();
 */
class View {

	protected $file;
	protected $data;

	public function __construct($file, array $data = [])
	{
		$this->file = $file;
		$this->data = $data;
	}

	public static function forge($file, array $data = [])
	{
		return new self($file, $data);
	}

	public function render()
	{
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
	 * Renders and returns the view
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

	public function getFile()
	{
		return $this->file;
	}

	public function getData()
	{
		return $this->data;
	}

	protected function file_resolver()
	{
		if (file_exists($this->file)) return $this->file;
		throw new ViewNotFoundException;
	}
}

class ViewNotFoundException extends \Exception {}