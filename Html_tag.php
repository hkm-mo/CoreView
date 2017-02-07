<?php
namespace core_view;

/**
 * 
 */
class Html_tag
{
	protected $name;
	protected $has_close_tag;
	protected $attrs = array();
	protected $content = '';

	function __construct($name, $has_close_tag = TRUE)
	{
		$this->name = $name;
		$this->has_close_tag = $has_close_tag;
	}

	public function attr($name, $value = NULL)
	{
		if ( $value !== NULL ) {
			$this->attrs[$name] = $value;
			return $this;
		} else {
			return isset( $this->attrs[$name] ) ? $this->attrs[$name] : NULL;
		}
	}

	public function content($value)
	{
		$this->content = $value;
		return $this;
	}

	public function __toString()
	{
		$attr_str = '';

		foreach ($this->attrs as $attr_name => $attr_value) {
			$attr_str .= ' ' . $attr_name . '="'. htmlspecialchars($attr_value) . '"';
		}

		if ($this->has_close_tag) {
			$content = '';
			if( $this->content instanceof \core_view\Html_tag ) {
				$content = $this->content->__toString();
			} else {
				$content = htmlspecialchars($this->content);
			}
			
			return "<{$this->name}{$attr_str}>{$content}</{$this->name}>";
		} else {
			return "<{$this->name}{$attr_str} />";
		}
	}
}
