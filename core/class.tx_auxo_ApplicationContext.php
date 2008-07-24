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

require_once(t3lib_extMgm::extPath('auxo') . 'core/class.tx_auxo_loader.php');
require_once(t3lib_extMgm::extPath('auxo') . 'core/class.tx_auxo_observer.php');
require_once(t3lib_extMgm::extPath('auxo') . 'core/class.tx_auxo_observable.php');

/**
 * @package auxo
 * @subpackage core
 * @version $Id$
 */

/**	
 * The Application Context
 * 
 * An application context is a container in which live all components of an applications. 
 * Components are classes built based on an context configuration file in which all components 
 * are defined and configured in a way they work together. This class makes use of an 
 * component factory to built components and to set components parameters either 
 * using constructor injection or setter methods. Herewith, it implements the 
 * inversion of control principle (IoC).
 * 
 * Moreover, this class implements an observer patterns that allows components to register
 * a listeners to this context.  Listeners are triggered by this context events. Such events 
 * could be used to perform user specific activities.
 * 
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author AHN
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
 abstract class tx_auxo_ApplicationContext implements tx_auxo_observable {
 	
 	/**
 	 * A component factory
 	 *
 	 * @var tx_auxo_ComponentFactory $factory
 	 */
 	protected $factory;
 	
 	/**
 	 * Registered Event Listener
 	 *
 	 * @var array $eventListener
 	 */
 	protected $eventListener = array();
 	
 	/**
 	 * A class loader
 	 *
 	 * @var tx_auxo_loader $loader
 	 */
 	protected $loader = NULL;
 	
 	/**
 	 * Builds a Context
 	 */
 	public function __construct() {
 		$this->bootstrap($this);	
 	}
 	
	/**
	 * Boots an application context and sets a default exception handler as
	 * well as an default shutdown function. Moreover, a class loader is intialized
	 * with auxo's directory structure.
	 */ 	
 	protected function bootstrap() {
 		// register error handler
		set_exception_handler(array($this, 'exception_handler'));

		if (! $this->getExtension()) {
			throw new tx_auxo_ContextException('No Extension has been defined ->getExtension');
		}
		
		// build a class loader 
		$this->loader = new tx_auxo_loader();	
		
		// register pathes of user extension
		$this->loader->add('EXT:'.$this->getExtension() . '/.');	
		$this->loader->add('EXT:'.$this->getExtension() . '/lib');	
		$this->loader->add('EXT:'.$this->getExtension() . '/schema');		
		
		// register shutdown 
		register_shutdown_function(array($this, 'shutdown'));		
 	}
 	
	/**
	 * This method is triggered by TYPO3 automatically 
	 * if content type 'plugin' is detected. It setups an application context
	 * with instances of all needed components and classes.
	 *
	 * @param $input input data
	 * @param $config extension configuration
	 * @return string $rendererContent renderered output
	 */
	public function main($input, $config) {
 		$this->bootstrap($this);
		/* build a component factory */
		$this->factory = new tx_auxo_ComponentFactory();		
		$this->triggerEvent('ComponentFactoryBuilt');
		
		/* load all other components */
		$this->triggerEvent('BeforeApplicationContextLoad');
		$this->factory->load('ApplicationContext');
		$this->triggerEvent('ApplicationContextLoaded');

		/* initialize some basic components */
		$this->configuration = $this->get('Configuration', array('configuration' => $config));
		
		/* get appliction context components */
		//$this->resourceResolver = $this->factory->get('ResourceResolver');
		//$this->MessageSourceResolver = $this->factory->get('MessageSourceResolver');
		//$this->RequestHandlerResolver = $this->factory->get('RequestHandlerResolver');		
		
		/* determine a request handler which can handle this input */ 
		//$requestHandler = $this->getRequestHandlerResolver->resolve($input);

		/* trigger handler to handle input */
		$this->triggerEvent('BeforeRequestHandler');		
		//$result = $requestHandler->handle();
		
		$this->triggerEvent('BeforeShutdown');
		
		return $result;
	}
	
	/**
	 * Get a component by delgating this task to the component factory
	 *
	 * @param string $component
	 * @param array $arguments
	 * @return mixed $instance
	 */
	public function get($component, $arguments=array()) {
		if ($this->factory->has($component)) {
			return $this->factory->get($component, $arguments);
		}
		$resource = $this->resourceResolver->resolve($component);
		return $this->factory->get($resource, $arguments);
	}
 	
	/**
	 * Enables to listen to context events by registering as listener
	 *
	 * @param string $event event listen to
	 * @param tx_auxo_observer $listener listener
	 */
	public function addListener($event, tx_auxo_observer $listener) {
		$this->eventListener[$event][spl_object_hash($listener)] = $listener;
	}
	
	/**
	 * Removes a listener for a given event
	 * 
	 * @param string $event name of an event to listen to
	 * @param tx_auxo_observer $listener instance that implements interface tx_auxo_observer and acts as listener
	 * @return boolean $removed true if listener has been removed
	 */
	public function removeListener($event, tx_auxo_observer $listener) {
		if (! isset($this->eventListener[$event][spl_object_hash($listener)])) {
			return false;
		}
		
		unset($this->eventListener[$event][spl_object_hash($listener)]);
		return true;
	}
	
	/**
	 * Triggers an application context event and informs all listeners
	 *
	 * @param string $event
	 * @return void
	 */
	public function triggerEvent($event) {
		if (!isset($this->eventListener[$event])) return void;
		
		foreach($this->eventListener[$event] as $listener) {
			$listener->listen($event);
		}
	}
	
	/**
	 * A Default Exception Handler
	 * 
	 * @param tx_auxo_exception $exception exception instance
	 */
	public function exception_handler(exception $exception) {
		echo '<div style="border: 2px solid grey; padding: 10px;"';
		echo '<h1 >Auxo Runtime Exception</h1>';
		echo '<h2 style="color: red;">'. $exception->getMessage() . '</h2>';
		echo '<h3 style="color: blue;font-size:12px;">Uncaught Exception ' . get_class($exception) . ' ';
		echo 'in file ' . $exception->getFile() . ' ';
		echo 'at line ' . $exception->getLine() . ', ';
		echo 'with Code (' . $exception->getCode() . ')</h3>';
        foreach($exception->getTrace() as $trace) {
			echo '<p style="font-size: small;">' . $trace['file'] . '(' . $trace['line']. ') ' .
			     $trace['class'] . $trace['type'] . $trace['function'] . '(';
			
			echo ') </p>';
        }
        echo '</div>';
	}
	
	/**
	 * A default shutdown Handler
	 */
	public function shutdown() {
		tx_auxo_cache::shutdown();	
		$this->triggerEvent('shutdown');
	}	
	
	/**
	 * Returns the name of the current TYPO3 extension. This Method has to
	 * be overwritten in each user application context.
	 *
	 * @abstract
	 * @return string $extension name of extension implementing this application context
	 */
 	public abstract function getExtension();
 }
?>