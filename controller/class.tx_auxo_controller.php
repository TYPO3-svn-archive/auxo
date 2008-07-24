<?php

declare(ENCODING = 'UTF-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *	
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */	

/**
 * @package auxo
 * @subpackage core
 * @version $Id$
 */

/**
 * Controller
 *
 * This class represents the main controller of an application. 
 * It loads the auxo environment so that all needed components 
 * are created and available. Extensions have to extend this 
 * class and implement method 'getExtension' which should return
 * the name of the user extension. Moreover, it is necessary to
 * setup a constructor which calls parent __construct method
 * respectively. 
 *
 * Example:
 *
 * class tx_myext_controller extends tx_auxo_controller { 
 *    
 *      private $extension = 'myext';
 *
 * 		public function __construct($input, $config) {
 *	        parent::__construct($input, $config);
 *      }
 *
 *      public function getExtension() {
 *          return $this->extension;
 *      }
 * }
 *
 * @package auxo
 * @subpackage controller
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

abstract class tx_auxo_controller {
  /**
   *
   */
   const standardModule = 'standard';
   
   const errorAction = 'error';
   const welcomeAction = 'welcome';
   
  /**
   *   
   */	
   const CONFIGURATION_CLASS_NAME  = 'tx_auxo_configuration';
   const REQUEST_CLASS_NAME        = 'tx_auxo_request';
   const RESPONSE_CLASS_NAME       = 'tx_auxo_response';
   const ACTION_MANAGER_CLASS_NAME = 'tx_auxo_actionManager';
   const RESOLVER_CLASS_NAME       = 'tx_auxo_filePathResolver';
   
   const MAX_FORWARDS = 20;
   
  /**
   *   
   */   
   private $domain;
   private $response;
   private $configuration;
   private $request;
   private $context;
   private $actionManager;
  
   private $stack = array();
   
   /**
    * Constructor
    * 
    * Initialize the auxo framework.
    *
    */
	public function __construct() {
		/* initialize framework */
		tx_auxo::bootstrap($this);		
	}
	
  /**
   * main
   *
   * This method is triggered by typoscript automatically 
   * if content type 'plugin' is detected. It setups a context
   * with instances of all needed classes.
   *
   * @return void
   */
	public function main($input, $config) {
		/* initialize framework */
		tx_auxo::bootstrap($this);		
		
		/* build context */
		$this->context = tx_auxo_context::getInstance();
		$this->context->setExtension($this->extension);
		$this->context->register('controller', $this);
		
	    /* build default services: domain, request, configuration, response and runner */
		$this->configuration = $this->buildConfiguration($config);
		$this->pathResolver = $this->buildPathResolver();
		$this->domain = $this->buildDomain();
		$this->request = $this->buildRequest($input);
		$this->response = $this->buildResponse();
		$this->actionManager = $this->buildActionManager();

		/* customize framework */
		$this->customizeCaching();
		$this->customizeDebugging();
			
	   /* dispatch action */
		$this->dispatch();	
		
		/* return answer */ 
		if ($this->response->getContent()) {
			if ($this->configuration->get('logPanel')) {
				return $this->response->getContent() . tx_auxo_panelLogger::render();
			}
			else {
			    return $this->response->getContent();
			}
		}
		
		return void;
	}
	
  /**
   * tx_auxo_frontController::dispatch()
   *
   * @params $module name of module, parameter is optional 
   * @params $action name of action, parameter is optional 
   * @return void
   */
	public function dispatch($module='', $action='') {
		$forwards = 0;
		
		/* prevent any endless loops */
		do {
			/* dispatch action */	
			try {		
				$this->response->setView($this->actionManager->execute($module, $action));
				
				if ($this->response->getView() != tx_auxo_view::NO_VIEW) {
	               return $this->buildAndRenderView();
				}	
				return false;	
			}
			catch (tx_auxo_actionException $e) {
				$this->request->set('_error', $e);
				$module = $this->standardModule;
				$action = $this->errorAction;
			}
			catch (tx_auxo_forwardException $e) {
				$module = $e->getModule();
				$action = $e->getAction();
			}
		}
		while (++$forwards <= self::MAX_FORWARDS);
		
		throw new tx_auxo_controllerException(sprintf('Too many forwards %d', $forwards));
	}
			
	
  /**
   * tx_auxo_frontController::getExtension()
   *
   * Method has to be implemented by application using auxo framework.
   *
   * @return string name of user extension 
   */
	abstract function getExtension();
	
  /**
   * tx_auxo_frontController::buildConfiguration()
   *
   * @param mixed $config
   * @return
   */
	private function buildConfiguration($config) {
		$object = tx_auxo_loader::makeInstance(self::CONFIGURATION_CLASS_NAME);
		$object->setTypoScriptConfiguration($config);
		if(is_object($this->cObj)) $object->setFlexFormConfiguration($this->cObj->data['pi_flexform']); 
		$this->context->register('configuration', $object);
		return $object;
	}
	
	
  /**
   * tx_auxo_frontController::buildDomain()
   *
   * @return object tx_auxo_schema
   */
	private function buildDomain() {
		// get instance of model schema
		if (($name = $this->configuration->get('schema'))) {		    	
			$object = tx_auxo_schemabase::getInstance($this->getExtension(), $name);
			$this->context->register('schema', $object);	
		}			
		
		return $object;
	}
	
	
  /**
   * tx_auxo_frontController::buildRequest()
   *
   * @return object tx_auxo_request
   */
	private function buildRequest($input) {
		$object = tx_auxo_loader::makeInstance(self::REQUEST_CLASS_NAME);
		$object->set('input', $input);
		$this->context->register('request', $object);
		return $object;
	}
	
  /**
   * tx_auxo_frontController::buildResponse()
   *
   * @return object tx_auxo_response
   */
	private function buildResponse() {
		$object = tx_auxo_loader::makeInstance(self::RESPONSE_CLASS_NAME);
		$this->context->register('response', $object);
		return $object;
	}

  /**
   * buildActionManager()
   *
   * @return object tx_auxo_actionManager
   */
	private function buildActionManager() {
		$object = tx_auxo_loader::makeInstance(self::ACTION_MANAGER_CLASS_NAME);	
		$this->context->register('actionManager', $object);
		return $object;
	}
	
	/**
	 * Creates an Path Resolver
	 *
	 * @return unknown
	 */
	private function buildPathResolver() {
		$factory = new tx_auxo_ComponentFactory($this->context);
		$object = $factory->get('FilePathResolver');
		return $object;
	}
	
	/**
	 * Creates a view, passes values and renders results
	 *
	 * @throws tx_auxo_controllerException if view engine is not configured 
	 */
	private function buildAndRenderView() {
		$viewClass = $this->configuration->get('templateEngine');	
	    $view = new $viewClass($this->response->getModule(), $this->response->getAction(), $this->response->getView());
	    $view->setData($this->response->getArrayCopy($action));
	    $this->response->setContent($view->render());  		
	}
	
  /**
   * tx_auxo_controller::customizeCaching()
   *
   * @return void
   */
	private function customizeCaching() {
		if ($this->configuration->get('caching')) {
			// clean expired objects from cache
			tx_auxo_cache::clean(tx_auxo_cache::DEFAULT_NAMESPACE, 'expired');
		}
	}
	
  /**
   * tx_auxo_controller::customizeDebugging()
   *
   * @return void
   */
	private function customizeDebugging() {	
		if (($list = $this->configuration->get('debug'))) {
			$components = explode(',', $list);			
			foreach($components as $component) {
				tx_auxo_debug::enable($component);
			}
		}
	}		
}
?>