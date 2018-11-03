<?php
namespace core_view;

spl_autoload_register(function ($class_name) {
	$ns = (string)__NAMESPACE__;
	if( strrpos($class_name, __NAMESPACE__ . '\\', -strlen($class_name)) !== false ) {
		
		$class_path = __DIR__ . '/' . substr($class_name, strlen($ns)+1 ) . '.php';
		if ( file_exists($class_path) ) {
			include_once $class_path;
		}
	}
});

class View {
	protected $placement = array();
	protected $vars = array();
	protected $layout = null;
	protected $output_collapse;
	protected $magic_replace;
	protected $title_separator;
	protected $base_path_symbol;
	protected $keyword = array();
	protected $title = array();
	protected $view_path;

	public $script;
	public $meta;
	
	function __construct($config) 
	{
		$this->base_path = $config['base_path'];
		$this->output_collapse = $config['output_collapse'];
		$this->magic_replace = $config['magic_replace'];
		$this->title_separator = $config['title_separator'];
		$this->base_path_symbol = $config['base_path_symbol'];
		$this->view_path = $config['view_path'];

		$this->script = new Script_manager();
		$this->meta = new Meta_manager();
	}
	
	public function render($view = NULL, $vars = array())
	{
		if ($view === NULL) {
			$router = $this->router;
			$view = strtolower($router->class) .'/'. $router->method;
			if ( $router->directory !== NULL ) {
				$view = strtolower($router->directory) . $view;
			}
		}
		
		$content = $this->load($view, $vars, TRUE);
		
		if ($this->layout) {
			do
			{
				$layout = $this->layout;
				$this->layout = null;
				$layout_content = $this->load($layout['name'], $layout['vars'], true);
				$this->placement['body'] = $layout_content;
			}
			while ($this->layout);
			
			$body = $content;
			$content = $this->placement['body'];
			$this->placement['body'] = $body;
		}
		
		foreach ($this->placement as $replace_key => $replace_value) {
			//Assure all callable is array
			if(is_array($replace_value)) {
				if(isset($replace_value['params'])) {
					$replace_value = call_user_func_array($replace_value['callable'], $replace_value['params']);
				} else if(isset($replace_value['callable'])){
					$replace_value = call_user_func($replace_value['callable']);
				} else {
					$replace_value = call_user_func($replace_value);
				}
			}
			$content = str_replace($this->get_anchor($replace_key), $replace_value, $content);
		}
		
		foreach ($this->magic_replace as $search => $replace) {
			$content = str_replace($search, $replace, $content);
		}
		
		if ($this->base_path_symbol !== NULL){
			$content = str_replace($this->base_path_symbol, $this->base_path, $content);
		}
		
		if( $this->output_collapse ) {
			$content = str_replace("\t", '', $content);
			$content = str_replace(array(">\r\n", ">\n", ">\r"), '>', $content);
		}
		
		return $content;
	}
	
	public function set_layout($layout_name, $vars = array())
	{
		$this->layout = array('name' => $layout_name, 'vars' => $vars);
	}

	public function clear_layout()
	{
		$this->layout = NULL;
	}
	
	private function assign($name, $value)
	{
		$this->placement[$name] = $value;
	}
	
	public function get_anchor($name)
	{
		return "<!--View::anchor::{$name}-->";
	}
	
	public function assign_view($name, $view_name, $var = array())
	{
		$this->assign($name, array( 'callable' => array($this, 'load'), 'params' => array($view_name, $var, TRUE) ));
	}
	
	public function place($name)
	{
		echo $this->get_anchor($name);
		
		if( !isset($this->placement[$name]) )
		{
			$this->placement[$name] = '';
		}
	}
	
	public function body()
	{
		if(isset($this->placement['body']) && is_string($this->placement['body'])){
			echo $this->placement['body'];
		} else {
			$this->place('body', '');
		}
	}
	
	
	public function load($view_name, $var = array(), $is_return = FALSE)
	{
		$view_path = $this->view_path . $view_name;
		
		if ( file_exists( $view_path )) {
			
			ob_start();
			
			if (is_array($var))
			{
				extract($var);
			}
			
			include $view_path;
			
			$buffer = ob_get_contents();
			@ob_end_clean();
			
			if ($is_return) {
				return $buffer;
			} else {
				echo $buffer;
				return TRUE;
			}
			
		} else {
			throw new \Exception('Unable to load the requested file: ' . $view_name);
		}
		
	}

	public function throwError($code){
		@ob_end_clean();
		switch( $code ){
			case '404':
				header('HTTP/1.1 404 Page Not Found');
				echo '<h1>404</h1><p>Please go to homepage and try again later.</p>';
			break;
			case '500':
				header('HTTP/1.1 500 Internal Server Error');
				echo '<h1>Server Error</h1><p>Please go to homepage and try again later.</p>';
			case '503':
				header('HTTP/1.1 500 Temporary Unavailable');
				echo '<h1>Temporary Unavailable</h1><p>Retry later.</p>';
			break;
		}
		exit();
	}
	
	public function set_var($name, $value)
	{
		$this->vars[$name] = $value;
	}
	
	public function get_var($name)
	{
		if( isset($this->vars[$name]) ) {
			return $this->vars[$name];
		}
		return null;
	}

	public function add_title_segment($segment)
	{
		if(func_num_args() > 1){
			$segments = func_get_args();
			foreach($segments as $seg){
				array_unshift($this->title, $seg);
			}
		} else {
			array_unshift($this->title, $segment);
		}
	}
	
	public function set_title($title)
	{
		$this->title = array($title);
	}
	
	public function get_title(){
		return implode($this->title_separator, $this->title);
	}
}