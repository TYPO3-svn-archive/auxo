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
 * Class Inspector
 * 
 * This inspector offers a set of reflecting methods to inspect a class in order
 * to e.g. find out which metods have been implemented etc.
 *  
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_ClassInspector {

	/**
	 * Namespace for cached reflections
	 */
	const 	CACHE_NAMESPACE = 'AUXO-INSPECTOR';
		
	/**
	 * enable or disable caching of reflections 
	 *
	 * @var boolean $caching
	 */
	public	$caching = true;
	
	/**
	 * A Reflection Cache
	 * 
	 * @var tx_auxo_Cache $cache
	 */
	public	$cache = NULL;
	
	/**
	 * Registry of all reflections available currently
	 *
	 * @var array $reflections
	 */
	private $reflections = array();
	
	/**
	 * Creates a new Class Inspector
	 *
	 * @param boolean $caching enable or disable caching of 
	 */
	public function __construct($caching=true) {
		$this->caching = $caching;
		if ($this->caching) {
			$this->cache = new tx_auxo_Cache(self::CACHE_NAMESPACE);
		}
	}
 /**
   * Get a static attribute of a class or NULL if not found
   *
   * @param mixed $classname A class name
   * @param mixed $attribute A static attribute of this class
   * @return mixed $value value of NULL if not exist
   */
	public function getPropertyIfexist($classname, $attribute, $default=NULL) {
		return $this->getProperty($classname, $attribute, $default, false);	
	}
	
  /**
   * Get a static attribute of a class or if not found a 
   * given default value
   *
   * @param mixed $classname A class name
   * @param mixed $attribute A static attribute of this class
   * @param mixed $default default value if property is not defined
   * @return mixed $value value of attribute
   */
	public function getPropertyOrDefault($classname, $attribute, $default) {
		return $this->getProperty($classname, $attribute, $default, true);			
	}
	
  /**
   * Requires a static attribute of a class if not found an exceptions is thrown
   *
   * @param mixed $classname A class name
   * @param mixed $attribute A static attribute of this class
   * @return mixed $value value of class::attribute
   * @throws tx_auxo_coreException $exception property not found
   */
	public function requireProperty($classname, $attribute) {
		return $this->getProperty($classname, $attribute, NULL, true);					
	}
	
  /**
   * Get a static class property
   *
   * Inspects a given class and returns a value of static properties or if not found 
   * a default value
   *
   * @param  string $classname name of class
   * @param  string $attribute name of attribute
   * @param  mixed  $default default value if attribute is not found
   * @param  boolean $required value is required and not found throws an exception
   * @return mixed  $value value of attribute in class
   * @throws tx_auxo_coreException $exception requested property not found
   * @throws tx_auxo_coreException $exception requested property is not static
   */
	public function getProperty($classname, $attribute, $default=NULL, $required=true) {
		$reflection = $this->getRefelection($classname);
		if (!$reflection->class->hasProperty($attribute)) {
			if (isset($default)) {
				return $default;
			}
			if (!$required) {
				return NULL;
			}
				
			throw new tx_auxo_coreException(sprintf('Class %s has no property %s', $classname, $attribute));
		}	
		
		$property = $reflection->properties['$attribute'];
		if (! $property->isStatic()) {
			throw new tx_auxo_coreException(sprintf('Class %s property %s is not static', $classname, $attribute));
		}
		
		return $reflection->class->getStaticPropertyValue($attribute);
	}	
	
	/**
	 * Create a new instance optionally with parameters of a given class
	 *
	 * @param string $classname name of class
	 * @param array $arguments optional arguments to pass to 
	 */
	public function newInstance($classname, $arguments=array()) {
		$reflection = $this->getReflection($classname);
		return $reflection->class->newInstanceArgs($classname, $arguments);
	}
	
  /**
   * Checks if an method exits for a given class
   *
   * @param string $classname
   * @param string $method
   * @return boolean $exists true if this methods exits
   */
	public function hasMethod($classname, $method) {
		$reflection = $this->getRefelection($classname);
		return $reflection->hasMethod($method);
	}
	
	/**
	 * Returns the DocComment of a given class
	 *
	 * @param string $classname
	 * @return string $docComment 
	 */
	public function getClassDocComment($classname) {
		$reflection = $this->getRefelection($classname);
		return $reflection->class->getDocComment();
	}

	/**
	 * Determines all parameter of an given method for a class
	 *
	 * @param string $classname name of class
	 * @param string $methodname name of method
	 * @return array $parameters an array of ReflectionParameter objects
	 * @throws tx_auxo_coreException method in class not found
	 */
	public function getMethodParameters($classname, $methodname) {
		if (!$reflection->class->hasMethod($classname, $methodname)) {
			throw new tx_auxo_coreException(sprintf('Method %s in Class %s not found', $classname, $method));
		}
		
		$method = $reflection->methods[$methodname];
		return $method->getParameters();
	}
	
	/**
	 * Returns a composed reflection array object with class, methods and properties
	 *
	 * @param string $classname
	 * @return tx_auxo_arrayObject $reflection composed reflection information of a class
	 */
	public function getRefelection($classname) {
		if (isset($this->reflections[$classname])) {
			return $this->reflections[$classname];
		}
		
		if ($this->caching) {
			if ($this->cache->has($classname)) {
				return $this->cache->get($classname);
			}
		}
		
		if (! ($classReflection = new reflectionClass($classname))) {
		 	throw new tx_auxo_exception(sprintf('Class %s could not be found', $classname));
		}
		$methodReflectons = $classRelection->getMethods();
		$propertiesRelections = $classReflection->getProperties();
		
		$reflection = new tx_auxo_ArrayObject();		
		$reflection->add('class', $classReflection);
		$reflection->add('methods', $methodReflections);
		$reflection->add('properties', $propertiesRelections);
		
		if ($this->caching) {
			$this->cache->add($classname, $reflection);
		}
		$this->reflections[$classname] = $reflection;		
		return $reflection;
	}
}
?>