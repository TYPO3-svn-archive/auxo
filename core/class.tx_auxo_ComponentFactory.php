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
 * A small component factory
 * 
 * This class is responsible for creating a component instance. The building process is
 * done using an xml configuration file which declares scope, dependencies and parameters
 * which should be passed or injected.
 *  
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_ComponentFactory {
	
	/**
	 * @var string Namespace for cache usage
	 */
	const	CACHE_NAMESPACE	= 'AUXO-CORE-COMPONENT';
	
	/**
	 * Option for enabling/disabling caching
	 * 
	 * @var boolean $caching 
	 */
	private	$caching = true;
	
	/**
	 * Cache for scanned and parsed configurations
	 *
	 * @var tx_auxo_Cache $cache
	 */
	private	$cache = NULL;
	
	/**
	 * Array of already initialized and built components
	 *
	 * @var array $components
	 */
	private $components = array();
	
	/**
	 * Array of already read component configurations
	 *
	 * @var array $configurations
	 */	
	private $configurations = array();
	
	/**
	 * A class inspector
	 * 
	 * @var tx_auxo_inspector
	 */
	private $inspector = NULL;
	
	/**
	 * Builds a new Component Factory
	 *
	 * @param boolean $caching enables or disables configuration file caching
	 */
	public function __construct($caching=true) {		
		$this->setCaching($caching);
		$this->inspector = new tx_auxo_ClassInspector($caching);
	}
	
	/**
	 * Enables or disables caching of parsed configuration files
	 * 
	 * @param boolean $caching enables if true otherwise disable caching mechanism
	 */
	public function setCaching($option) {
		$this->caching = $caching;
		if ($this->caching && ! $this->cache) {
			$this->cache = new tx_auxo_Cache(self::CACHE_NAMESPACE);
		}
	}
	
	/**
	 * Loads components from a given resources. Component definitions are cached if
	 * enabled and singleton components with option eager autoloading will be 
	 * instansiated at once.
	 *
	 * @param string $resource
	 * @return int $count number of components loaded
	 */
	public function load($resource) {
		$parser = new tx_auxo_ComponentConfigurationParser();
		$definitions = $parser->parseConfiguration($resource);

		/* add all definitions to a register */
		foreach($definitions as $definition) {
			$this->configurations[$definition->getName()] = $definition;
		}
		
		/* add all definitions to a cache */
		if ($this->caching) {
			foreach($definitions as $definition) {
				$this->cache->add($definition->getName(), $definition);					
			}
		}				
		
		/* components that have to be eager loaded are built here */
		foreach($definitions as $definition) {
			debug($definition);
			if ($definition->getLoading() == tx_auxo_ComponentDefinition::EAGER_LOADING) {
				$this->register($definition->getName(), $this->build($definition));
			}
		}
		
		return count($defintions);
	}
	
	/**
	 * Get a component and pass optionally named parameters 
	 *
	 * @param string $component name of a component
	 * @param array  $arguments array of dynamic parameters which have to be passed
	 * @param mixed  $instance built instance with injected parameters
	 */
	public function get($component, $arguments=array()) {
		// components of scope singleton are kept in a registry if already built
		if (isset($this->components[$component])) {
			return $this->components[$component];
		}	

		$definition = $this->getComponentDefinition($component);
		
		// get properties from component definition
		$parameters = array_merge(array(), $definition->getParameters()->getArray());
		
		// overwrite static properties with dynamic passed arguments 
		$arguments = array_merge($parameters, $arguments);
		
		// builds an object
		$instance = $this->build($definition, $arguments);	
		
		// components of scope singleton are kept in a registry
		if ($definition->getScope() == tx_auxo_ComponentDefinition::SCOPE_SINGLETON) {
			$this->register($component, $instance);
		}
		return $instance;
	}
	
	/**
	 * Returns true if an component is already loaded or registered in the factory
	 *
	 * @param string $component name of a component
	 * @return boolean $exist true if already is loaded or registered
	 */
	public function has($component) {
		return isset($this->components[$component]);
	}
	
	/**
	 * Gets an component configuration either from XML file or from auxo's registry or
	 * internal cache if already loaded and parsed and caching has been enabled
	 * 	 
	 * @param string $component name of a component
	 * @return tx_auxo_ComponentDefinition $configuration array with configuration values
	 */
	protected function getComponentDefinition($component) {	
		if (isset($this->configurations[$component])) {
			return $this->configurations[$component];
		}
			
		if ($this->caching) {
			if ($this->cache->has($component)) {
				return $this->cache->get($component);	
			}
		}
	
		$parser = new tx_auxo_ComponentConfigurationParser();
		
		$definition = $parser->parse($component);
		if ($this->caching) $this->cache->add($component, $definition);
		
		return $definition;
	}
	
	/**
	 * Create a new instance of an object using a simple autowiring pattern
	 *
	 * @param string $component name of requested class
	 * @param $definition an component definition class
	 * @param $arguments an array of dynamic arguments that have to be injected
	 * @return object $instance object instance
	 */
	private function build(tx_auxo_ComponentDefinition $definition, $arguments=array()) {
		$injectArguments = array();
		
		/*
		 *	preapare all arguments for injection
		 */
		foreach($arguments as $name => $value) {
			if ($value instanceof tx_auxo_ComponentDefinitionParameter) {
				if ($value->has('value')) {
					$injectArguments[$name] = $value->value;
				}
				elseif ($value->has('ref')) {
					$injectArguments[$name] = $this->get($value->ref);
				}
				else {
					$injectArguments[$name] = $value;
				}
			}
		}
		/*
		 *	build a new instance and inject constructor arguments
		 */
		$instance = $this->constructInstance($definition, $injectArguments);
		    
	    /*
	     *	arguments could also be inject into an instance using setter methods
	     */
	    foreach($injectArguments as $name => $value) {
	    	if (method_exists($instance, 'set' . ucfirst($name))) {
	    		call_user_func(array($instance, 'set' . ucfirst($name), $value));
	    	}
	    }		    	

		return $instance;
	}

	/**
	 * constructInstance
	 *
	 * @param tx_auxo_ComponentDefinition $definition
	 * @param array $arguments
	 * @return object $instance
	 */
	private function constructInstance(tx_auxo_ComponentDefinition $definition, $arguments) {
		if ($definition->getFactoryMethod()) {
			$constructMethod = $definition->getFactoryMethod();
		}
		elseif ($this->inspector->hasMethod($definition->getClass(), '__construct')) {
			$constructMethod = '__construct';
		}	

		$parameters = $this->inspector->getMethodParameters($definition->getClass(), $constructMethod);
		
		if (! isset($constructMethod) || ($constructMethod === '__construct' && $count($parameters) == 0)) {
			/*
			 * Instance with a constructor without any arguments and classes or
			 * without a constructor or factory method are created straight forward
			 * here. 
			 */
			return new $definition->getClass();
		}
		
	    /*
	     * build an array with all parameters and 
	     * instansiate depending objects if necessary 
	     */ 		    
		foreach($parameters as $parameter) {
			/*
			 * check wether an argument could be passed to this parameter
			 */
			if (array_keys($arguments, $parameter->getName())) {
				$constructorArguments[$parameter->getName()] = $arguments[$parameter->getName()];
			}
			elseif ($definition->getAutowire() === true) {
				/*
				 * Parameter with typing hinting will be used supplied with an components of
				 * the corresponding type.
				 */	    	
				if ($parameter->getClass()) {
					$constructArguments[$parameter->getName()] = $this->get($parameter->getClass());
				}
			}
			/* non optional parameters without any value raise an exception */
			if(! $parameter->isOptional() && ! isset($constructArguments[$this->parameter->GetName()])) {
				throw new tx_auxo_ComponentException('Constructor of %s could not be supplied with all arguments', $definition->getClass());
			}
		}
		    
	    /*
	     * build a new instance either via new or an factory method and 
	     * inject all arguments 
	     */	    
	    if ($constructMethod === '__construct') {
			if (! $reflection->isInstantiable()) {
				throw new tx_auxo_ComponentException('Class %s is not instantiable', $definition->getClass());
			}
	    	return $this->inspector->newInstance($defintion->getClass(), $constructorArguments);
	    }
	    else {
		    // create an instance using a factory method
		    if (! $reflection->getMethod($constructMethod)->isStatic()) {
				throw new tx_auxo_ComponentException('Factory method %s of class %s is not static', $contructMethod, $definition->getClass());
		    }
	    	return call_user_func(array($defintion->getClass(), $constructMethod));
	    }
	}
	
	/**
	 * Registers a new component
	 *
	 * @param string $component name of component
	 * @param object $instance instance of component
	 * @return void
	 */
	public function register($component, $instance) {
		$this->components[$component] = $instance;
	}
}
?>