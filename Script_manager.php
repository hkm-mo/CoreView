<?php
namespace core_view;

/**
 * 
 */
class Script_manager
{
	private $scripts = array();

	function __construct()
	{
	}

	public function add_js($src, $group = 'default')
	{
		if(isset($this->scripts[$src])) {
			$this->scripts[$src]->group($group);
		} else {
			$this->scripts[$src] = new Script_manager_js_tag($src);
			$this->scripts[$src]->group($group);
			return $this->scripts[$src];
		}
	}

	public function add_css($src, $group = 'default')
	{
		if(isset($this->scripts[$src])) {
			$this->scripts[$src]->group($group);
		} else {
			$this->scripts[$src] = new Script_manager_css_tag($src);
			$this->scripts[$src]->group($group);
			return $this->scripts[$src];
		}
	}

	public function render($group = 'default')
	{
		foreach ($this->scripts as $value) {
			if($value->group() == $group){
				echo $value->__toString();
			}
		}
	}
}

class Script_manager_js_tag extends Html_tag
{
	protected $group;
	
	function __construct($src)
	{
		parent::__construct('script');
		$this->attr('src', $src);
	}

	public function group($value = NULL)
	{
		if($value === NULL) {
			return $this->group;
		} else {
			$this->group = $value;
			return $this;
		}
	}
}

class Script_manager_css_tag extends Html_tag
{
	protected $group;

	function __construct($src)
	{
		parent::__construct('link', FALSE);
		$this->attr('rel', 'stylesheet')->attr('href', $src);
	}
	
	public function group($value = NULL)
	{
		if($value === NULL) {
			return $this->group;
		} else {
			$this->group = $value;
			return $this;
		}
	}
}