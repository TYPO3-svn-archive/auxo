<?php

/**
 * A Router
 * 
 * @package auxo
 * @subpackage controller
 * @author Andreas Horn
 * @copyright 2007
 * @version $id$
 * @access public
 */

class tx_auxo_router {
	
	static private $instance = NULL;
	
	private	$routes = array();
	
	public function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_auxo_routing();
		}
		
		return self::$instance;
	}
	
	public function addRoute($name, $path, $defaults, $options=array()) {
		$this->routes[$name] = array('path' => $path, 'defaults' => $defaults, 'options' => $options);
	}
	
	public function getRoute($module, $action) {
		return $path;
	}
}

?>