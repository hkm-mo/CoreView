<?php
namespace core_view;

/**
 * 
 */
class Meta_manager
{
	private $meta_tags;

	function __construct()
	{
		$this->meta_tags = array();
	}

	public function add($name, $content)
	{
		$tag = new Html_tag('meta', false);
		$tag->attr('name', $name)->attr('content', $content);
		$this->meta_tags[$name] = $tag;
		return $this;
	}

	public function add_property($name, $content)
	{
		$tag = new Html_tag('meta', false);
		$tag->attr('property', $name)->attr('content', $content);
		$this->meta_tags['prop_' . $name] = $tag;
		return $this;
	}

	public function canonical($value)
	{
		$tag = new Html_tag('link', false);
		$tag->attr('rel', 'canonical')->attr('href', $value);
		$this->meta_tags['canonical'] = $tag;
		return $this;
	}

	public function render()
	{
		echo $this->__toString();
	}

	public function __toString()
	{
		$str = '';
		foreach ($this->meta_tags as $tag) {
			$str .= $tag->__toString();
		}
		return $str;
	}
}
