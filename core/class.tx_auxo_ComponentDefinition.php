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
 * The Component Definition class represents all definition made for a component. It
 * is built by ComponentConfigurationParser as a runtime presentation. Components options 
 * are available via setter/getter methods.
 * 
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_auxo_ComponentDefinition {
	/**
	 * eager loading of components means loading, parsing, building before a component
	 * will be requested
	 */
	const	EAGER_LOADING = 'eager';
	
	/**
	 * lazy loading of components means loading, parsing, building when a components
	 * is being requested
	 */
	const	LAZY_LOADING = 'lazy';

	/**
	 * @var string component scope singleton 
	 */
	const	SCOPE_SINGLETON = 'singleton';

	/**
	 * @var string component scope prototype
	 */
	const	SCOPE_PROTOTYPE = 'prototype';
	
	private $name = '';
	private $classname = '';
	private $id = '';
	
	private $autowire = true;

	
	
	private $loading = self::LAZY_LOADING;
	private $priority = 9;
	private $scope = self::SCOPE_SINGLETON;

	private $parameters = NULL;
	
	/**
	 * Represents a component definition at runtime which has been built
	 * by the component configuration parser.
	 *
	 * @param string $component
	 */
	public function __construct($component) {
		$this->name = $component;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setId($id) {
		$this->id = $id;		
	}
	
	public function getId() {
		return $this->id;
	}		
	
	public function setClass($classname) {
		$this->classname = $classname;		
	}
	
	public function getClass() {
		return $this->classname;
	}		
	
	public function setAutowire($option) {
		$this->autowire = $option;		
	}
	
	public function getAutowire() {
		return $this->autowire;
	}		
	
	public function setLoading($option) {
		$this->loading = $option;
	}
	
	public function getLoading() {
		return $this->autowrite;
	}		
		
	public function setPriority($option) {
		$this->priority = $option;
	}
	
	public function getPriority() {
		return $this->priority;
	}
	
	public function setScope($option) {
		$this->scope = $option;
	}
	
	public function getScope() {
		return $this->scope;
	}
	
	public function setParameters($parameters) {
		$this->parameters = $parameters;
	}
	
	public function getParameters() {
		return $this->parameters;
	}
}
?>