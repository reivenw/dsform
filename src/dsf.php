<?php

namespace dsf;

use dsf\Core\Builder;
use dsf\Render\Render;

class Dsf
{
	private $builder;
	private $render;

	protected function __construct()
	{
		$this->builder = new Builder();
	}

	protected function prepare()
	{
		die('This method must be implemented by the child class');
	}

	public function render()
	{
		$this->prepare();
		$this->render = new Render($this->builder);
		return
			sprintf('%s%s', $this->render->render(), $this->loadScripts());
	}

	public function loadScripts()
	{
		return '<script type=\'module\' src=\'../src/assets/js/index.js\'></script>';
	}

	protected function getBuilder()
	{
		return $this->builder;
	}

	public function display()
	{
		echo $this->render();
	}
}
