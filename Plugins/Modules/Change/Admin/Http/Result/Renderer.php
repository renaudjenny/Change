<?php
namespace Change\Admin\Http\Result;

use Change\Http\Result;
use Zend\Http\Response as HttpResponse;


/**
* @name \Change\Admin\Http\Result\Renderer
*/
class Renderer extends Result
{
	/**
	 * @var \Callable
	 */
	protected $renderer;


	function __construct()
	{
		$this->setHttpStatusCode(HttpResponse::STATUS_CODE_200);
		$this->setHeaderContentType('text/html;charset=utf-8');
	}

	/**
	 * @param Callable $renderer
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * @return Callable
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * @return boolean
	 */
	public function hasRenderer()
	{
		return ($this->renderer && is_callable($this->renderer));
	}

	/**
	 * Used for generate response
	 * @return string
	 * @throws \RuntimeException
	 */
	public function toHtml()
	{
		if ($this->hasRenderer())
		{
			return call_user_func($this->renderer);
		}
		else
		{
			throw new \RuntimeException('Renderer not set', 999999);
		}
	}
}