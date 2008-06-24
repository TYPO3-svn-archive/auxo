<?php
/**
 * @package auxo
 * @subpackage controllers
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

/**
 * Action Manager
 *
 * This class manages all incoming actions requests. It determines which 
 * actions has to be excuted next and some other stuff.
 * 
 * @package 	auxo
 * @subpackage	controller
 * @author 		Andreas Horn
 * @copyright 	2007
 * @version 	$Id$
 * @access 		public
 */

class tx_auxo_actionManager {	
	
	const DEFAULT_MODULE_NAME = 'start';
   	const DEFAULT_ACTION_NAME = 'index';

	private	$context;
	private $response;
	private $request;
	private	$configuration;
	private $extension;
	
	private $modules = array();
	private $stack   = array();
   
	private $module  = '';
	private $action  = '';
	
  /**
   * __construct()
   *
   * @param mixed $context
   * @return void
   */
	public function __construct() {
		$this->context = tx_auxo_context::getInstance();	
		$this->request = $this->context->getService('request');
		$this->response = $this->context->getService('response');
		$this->configuration = $this->context->getService('configuration');
		$this->extension = $this->context->getService('controller')->getExtension();
								
		// register modules
		$this->registerModules();		
	}
	
  /**
   * execute()
   *
   * @params $module module name
   * @params $action action name
   * @throw  tx_auxo_actionException 
   * @return void
   */
    public function execute($module='', $action='') {	
		/* determine module and action */
    	$this->findModuleAndAction($module, $action);

   		/* set determined module/action */
		$this->response->setModuleAction($this->module, $this->action);

		/* log to panel */
		tx_auxo_panelLogger::add(sprintf('module:%s/action:%s', $this->module, $this->action));
		
   	 	/* determine class and method based on module and action */
		$action = $this->createActionInstance($this->module, $this->action);
			
		/* intialize */
		$action->initialize($this->context);
		
		/* run action */
		$result = $this->runAction($action);
		
   		/* passed data to response */
		$this->response->exchangeArray($action->getArrayCopy());
    
    	return $result;
    }
   
   /**
    * Determines module and action name 
    *
    * @param string $module
    * @param string $action
    * @return boolean $found
    */
	private function findModuleAndAction($module='', $action='') {
		/* 1. module and/or action given as parameter */
		if ($module) $this->module = $module;
		if ($action) $this->action = $action;
		
		/* 2. module and/or action defined by request parameters */
   	    if (!$this->module && $this->request->get('module')) {
			$this->module = $this->request->get('module');
		}
		if (!$this->action && $this->request->get('action')) {
			$this->action = $this->request->get('action');
		}
		
		/* 3. module and/or action defined by configuration */
		if (!$this->module) {
		    $this->module = $this->configuration->getPropertyDefault('defaultModule', self::DEFAULT_MODULE_NAME);
		}
		if (!$this->action) {
		    $this->action = $this->configuration->getPropertyDefault('defaultAction', self::DEFAULT_ACTION_NAME);
   		}
   		
   		/* module exist ? */
   		if (!in_array($this->module, $this->modules)) {
			throw new tx_auxo_actionException(sprintf('module %s not found', $this->module));
		}
		
   		/* module disabled ? */
   		if (strstr($this->module, $this->configuration->get('disabledModules')) === true) {
			throw new tx_auxo_actionException('module %s is disabled', $this->module);
		} 

		return true;		  	
 	}
   
   /**
   * registerModules
   *
   * @throw		tx_auxo_actionException no modules found
   * @return 	void
   */
	private function registerModules() {
		$this->modules = array();		
		$pathes = tx_auxo_loader::getModulePathes($this->extension);

  		foreach ($pathes as $path) {
			$modules = t3lib_div::get_dirs($path);
			if (is_array($modules)) {
				$this->modules = array_unique(array_merge($this->modules, $modules));		
			}
  		}

  		if (count($this->modules) == 0) {
			throw new tx_auxo_actionException('no modules found');
		}
	}
	 
  /**
   * Locates and requires a classfiles belonging to parameter $module and $action.
   * 
   * @param  string 	$module	Name of a module
   * @param  string 	$action	Name of an action
   * @return object		$action action object
   * @throws object		tx_auxo_actionException
   * */
    private function createActionInstance($module, $action) {
		$directory = t3lib_extmgm::extPath($this->extension) . 'modules/' . $module . '/actions/';
		$prefix   = 'tx_' . str_replace('_', '', $this->extension);
		/**
		 * Actions are either located in a central action file per module or are defined
		 * as separated class file. Actions could be either related to modules or to
		 * a kind of extension-wide library.
		 */
		for($tries=1; $tries<=3; $tries++) {
			switch($tries) {
				case 1;
        			$filename =	$directory . 'class.' . $prefix . '_' . $module . '_actions.php';
        			$classname = $prefix . '_'. $module . '_actions';
        			$method = 'execute'. ucfirst($action);
					break;
				case 2;
					$filename = $directory . 'class.' . $prefix . '_' .$action . '_action.php';
					$classname = $prefix . '_action_' . $action;
					$method = 'execute';
					break;
				case 3;
					$filename = t3lib_extmgm::extPath($this->extension) . 'lib/actions/class.' . $prefix . '_' .$action . '_action.php';
					$classname = $prefix . '_action_' . $action;
					$method = 'execute';									
			}

			if (is_readable($filename)) {
	        	require_once($filename);
	        	if (tx_auxo_inspector::hasMethod($classname, $method)) {
	        		$this->request->set('classname', $classname);
	        		$this->request->set('method', $method);
	        		/**
	        		 * include also all classes of the library directory for this module 
	        		 */
	        		tx_auxo_loader::add(t3lib_extmgm::extPath($this->extension) . 'modules/' . $module . '/lib');
    				return tx_auxo_loader::makeInstance($classname);	        			
	        	}		
		    }
		}

		throw new tx_auxo_actionException(sprintf('no class for module (%s) and action (%s) not found', $module, $action));
   }


  /**
   * Executes an action method with an before and after method if available
   * 
   * @param	 object $action instance of an action
   * @return mixed	$result result of this action
   */
    private function runAction($action) {
    	$this->stack[] = $action;
		$this->executeMethod($action, 'beforeExecute', true);
		$result = $this->executeMethod($action, $this->request->get('method'), false);
		$this->executeMethod($action, 'afterExecute', true);	
		return $result;
	} 
	
	
  /**
   * tx_auxo_runner::executeMethod()
   *
   * @param object  $action
   * @param string  $method
   * @param boolean $optional
   * @return void
   * @throw tx_auxo_actionException 
   */
	private function executeMethod($action, $method, $optional) {
		if (tx_auxo_inspector::hasMethod(get_class($action), $method)) {
			return call_user_func(array($action, $method));
		}
		else {
			if (!$optional) {
				throw new tx_auxo_actionException(sprintf('Method %s is missing in class %s', $method, get_class($action)));
			}
		}
		
		return void;
	}
	
	
  /**  
   * doPreActionProcessings
   *
   * @return void
   */
	public function doPreActionProcessings() {
	}
	
	
  /**
   * doPostActionProcessings
   *
   * @return void
   */
	public function doPostActionProcessings() {
		// execute output filters
		if (($filters = tx_auxo::config()->get('filters'))) {
		    $chain = explode(',', $filters);
			foreach($chain as $filter) {
				$classname = 'tx_auxo_' . $filter;
				$input = $response->output;
				$instance = new $classname;
				$response->output = $instance->execute($input);
			}
		}
	}
}
?>