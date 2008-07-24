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
 * The Component Configuration Parser 
 * 
 * This parser gets and parsed a given file and builds an configuaration objects.
 *	
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */

class tx_auxo_ComponentConfigurationParser {

	/**
	 * Reads and parses a component configuration from a XML file
	 *
	 * @param string $component name of a component
	 * @return array $configuration array with configuration values
	 */
	public function parse($component) {
		$fullpath = $this->getConfigurationPath($component);
		
		if(!($xml = @simplexml_load_file($fullpath))) {
			throw new tx_auxo_ComponentException(sprintf('XML File %s is not valid', $fullpath));
		}

		$componentXML = $xml->xpath('component[@id="' . $component . '"]');
		if (! count($componentXML)) {
			throw new tx_auxo_ComponentException(sprintf('Component %s not found in file %s', $component, $fullpath));			
		}		

		return $this->parseComponent($componentXML[0], $component);
	}	

	
	/**
	 * Parses and validates an configurations file and returns a collection 
	 * of configurations objects as array.
	 *
	 * @param string $configuration filename of an configuration without suffix
	 * @return array $collection an array of component definitions
	 */
	public function parseConfiguration($configuration) {
		$fullpath = $this->getConfigurationPath($configuration);		
		
		if(!($xml = @simplexml_load_file($fullpath))) {
			throw new tx_auxo_ComponentException(sprintf('XML File %s is not valid', $fullpath));
		}

		$componentsXML = $xml->xpath('//component');
		if (! count($componentsXML)) {
			throw new tx_auxo_ComponentException(sprintf('No Components found in file %s', $fullpath));			
		}		
	
		$collection = new tx_auxo_arrayObject();
		
		foreach($componentsXML as $componentXML) {
			if (! isset($componentXML['id'])) {
				throw new tx_auxoComponentException(sprintf('Component without an ID'));
			}
			$definition = $this->parseComponent($componentXML, (string) $componentXML['id']);
			$collection->set($definition->getId(), $definition);
		}		
		
		return $collection;
	}
	
	/**
	 * Parses and validates a single component based on a XML resource and returns
	 * a new component definition object
	 *
	 * @param SimpleXMLElement $xml
	 * @param string $component
	 * @return tx_auxo_ComponentDefinition $definition
	 */
	private function parseComponent(SimpleXMLElement $xml, $component) {
		$definition = new tx_auxo_ComponentDefinition($component);
		$definition = $this->parseComponentOptions($xml, $component, $definition);
		$definition->setParameters($this->parseArguments($xml, $component));
		return $definition;		
	}
	
	/**
	 * Determines a component configuration filename and path 
	 *
	 * @todo refactoring into a class like ComponentConfigurationResolver
	 * 	 
	 * @param string $component name of a component
	 * @return string $fullpath path to component configuration file
	 */
	private function getConfigurationPath($component) {
		$filename = $component . '.xml';
		$pathes = explode(';', get_include_path());
		
		foreach($pathes as $path) {
			if (file_exists($path . '/' . $filename)) {
               	$fullpath = $path . '/' . $filename;
								
				if (!is_readable($fullpath)) {
					throw new tx_auxo_IOException(sprintf('File %s could not be opened', $fullpath));			
				}		
				return $fullpath;
			}	
		}		

		throw new tx_auxo_IOException(sprintf('File %s does not exist', $filename));
	}
		
	/**
	 * Parses and validates component options
	 *
	 * @param SimpleXMLelement $xml A Simple XML Element 
	 * @param string $component
	 * @param tx_auxo_ComponentDefinition $definition
	 * @return tx_auxo_ComponentDefinition $definition
	 */
	private function parseComponentOptions(SimpleXMLelement $xml, $component, $definition) {			
		// check if all mandatory options are available
		foreach(array('class', 'id') as $mandatory) {
			if (! isset($xml[$mandatory])) {
				throw new tx_auxo_ComponentException(sprintf('Component %s Attribute %s is missing', $component, $mandatory));
			}
		}
		
		// assign and validate given options
		foreach($xml->attributes() as $attribute => $value) {
			switch($attribute) {
				case 'class':
					if (! class_exists((string) $value)) {
						throw new tx_auxo_ComponentException(sprintf('Class %s of component %s does not exists', (string) value, $component));
					}
				
					$definition->setClass((string) $value);
					break;				
				case 'id':
					$definition->setId((string) $value);
					break;									
				case 'scope':
					if (! in_array((string) $value, array('singleton', 'prototype'))) {
						throw new tx_auxo_ComponentException(sprintf('Component %s with unsupported scope %s', $component, (string) $value));
					}
					$definition->setScope((string) $value);
					break;		
				case 'autowrite':
					if (! in_array((string) $value, array('true', 'false'))) {
						throw new tx_auxo_ComponentException(sprintf('Component %s invalid autowire option %s', $component, (string) $value));			
					}
					$definition->setAutowrite((string) $attribute, 'true' ? true : false);
				case 'loading':
					if (! in_array((string) $value, array('eager', 'lazy'))) {
						throw new tx_auxo_ComponentException(sprintf('Component %s with unsupported loading %s', $component, (string) $value));
					}
					if ($value == 'eager') {
						$definition->setLoading(tx_auxo_ComponentDefinition::EAGER_LOADING);
					}
					break;
				case 'priority':
					if (!is_numeric((string)$value)) {
						throw new tx_auxo_ComponentException(sprintf('Component %s priority is not numeric %s', $component, (string) $value));						
					}
					$definition->setPriority((int) priority);
					break;
				default:
					throw new tx_auxoComponentException(sprintf('Attribute %s for component %s not supported', $attribute, $component));
			}		
		}
		
		return $definition;
	}
	
	/**
	 * Parses and validates component arguments
	 *
	 * @param SimpleXMLelement $xml
	 * @param string $component
	 * @return array $parameters
	 */
	private function parseArguments(SimpleXMLelement $xml, $component) {		
		$parameters = new tx_auxo_arrayObject();
		
		if (! count($xml->argument)) {
			return $parameters;
		}
		
		foreach($xml->argument as $argument) {		
			// process attributes of an argument
			$parameter = new tx_auxo_arrayObject();
			foreach($argument[0]->attributes() as $attribute => $value) {
				switch($attribute) {
					case 'name':
					case 'type':
					case 'ref':
					case 'classref':
					case 'value':
						$parameter->set((string) $attribute, $this->mapValueToData((string) $value));	
						break;					
					default:
						throw new tx_auxo_ComponentException(sprintf('Component %s unknown attribute %s in argument', $component, (string) $attribute));
				}
			}
			
			$parameter->set('value', $this->parseValue($argument, $component));
			
			$parameters->set($parameter->name, $parameter);
		}	
		
		return $parameters;
	}

	/**
	 * Parses and validates a value of an argument
	 *
	 * @param SimpleXMLelement $xml
	 * @param string $component
	 * @return mixed $value
	 */
	private function parseValue(SimpleXMLelement $xml, $component) {			
		foreach($xml->children() as $child) {
			switch ($child->getName()) {
				case 'value':
					return $this->mapValueToData((string)$child->value);
				case 'sets':						
					return $this->parseSets($child, $component);
					break;
				case 'hash':
					return $this->parseHashEntries($child, $component);						
					break;
				default:
					throw new tx_auxo_ComponentException(sprintf('Argument %s of Component %s uses unknown property %s', (string) $argument, $component, $child->getName()));
			}
		}		
	}
	
	/**
	 * Parses and validates a Sets Property. Sets are implemented as 
	 * associated arrays using class tx_auxo_arrayObject.
	 *
	 * @param SimpleXMLelement $xml A Simple XML resource
	 * @param string $component name of current component
	 * @return tx_auxo_arrayObject $sets set as arrayObject
	 */
	private function parseSets(SimpleXMLelement $xml, $component) {
		$sets = new tx_auxo_arrayObject();		
		foreach ($xml as $element) {
			$sets->set((string) $element['key'], $this->mapValueToData((string) $element['value']));
		}
		
		if (! count($sets)) {
			throw new tx_auxo_ComponentException(sprintf('Set without any values in Component %s', $component));				
		}
		
		return $sets;
	} 

	/**
	 * Parses and validates an "Hash" Property
	 *
	 * @param SimpleXMLelement $xml A Simple XML resource
	 * @param string $component name of current component
	 * @return object $entries entries as an array
	 */
	private function parseHashEntries(SimpleXMLelement $xml, $component) {
		$entries = array();
		foreach ($xml as $element) {
			$entries[] = (string) $element->entry;
		}
		
		if (! count($entries)) {
			throw new tx_auxo_ComponentException(sprintf('Hash without any entries in Component %s', $component));				
		}
		
		return $entries;
	} 
	
	/**
	 * Maps configuration value to typed data. Following data types are guessed:
	 * boolean, string
	 *
	 * @param string $value value as string
	 * @return mixed $data value as typed data
	 */
	private function mapValueToData($value) {
		if ($value == 'true' or $value == 'false') {
			return $value == 'true' ? true : false;
		}
		if (is_numeric($value)) {
			if (strpos($value, '.')) {
				return (float) $value;
			}
			else {
				return (int) $value;
			}
		}
		return $value;		
	}
}
 
?>