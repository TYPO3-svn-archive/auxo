<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$ $WCDATE$ $WCNOW$ $WCRANGE$
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
 * Inspector
 *
 * This core class offers easy access to certain reflection methods
 *
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */
 
class tx_auxo_inspector {
  /**
   * tx_auxo_inspector::getPropertyIfexist()
   *
   * @param mixed $classname
   * @param mixed $attribute
   * @param mixed $default
   * @return
   */
	public static function getPropertyIfexist($classname, $attribute, $default=NULL) {
		return self::getProperty($classname, $attribute, $default, false);	
	}
	
  /**
   * tx_auxo_inspector::getPropertyOrDefault()
   *
   * @param mixed $classname
   * @param mixed $attribute
   * @param mixed $default
   * @return
   */
	public static function getPropertyOrDefault($classname, $attribute, $default) {
		return self::getProperty($classname, $attribute, $default, true);			
	}
	
  /**
   * tx_auxo_inspector::requireProperty()
   *
   * @param mixed $classname
   * @param mixed $attribute
   * @return
   */
	public static function requireProperty($classname, $attribute) {
		return self::getProperty($classname, $attribute, NULL, true);					
	}
	
  /**
   * Get property
   *
   * Inspects a given class and returns a value of static properties or a default value
   *
   * @param  string $classname
   * @param  string $attribute
   * @param  mixed  $default
   * @param  boolean $required
   * @return mixed  $value
   */
	public static function getProperty($classname, $attribute, $default=NULL, $required=true) {
		if (!($reflection = new reflectionClass($classname))) {
			throw new tx_auxo_coreException(sprintf('Class %s could not be found', $classname));
		}
		if (!$reflection->hasProperty($attribute)) {
			if (isset($default)) {
				return $default;
			}
			if (!$required) {
				return NULL;
			}
				
			throw new tx_auxo_coreException(sprintf('Class %s has no property %s', $classname, $attribute));
		}	
		
		$property = $reflection->getProperty($attribute);
		if (! $property->isStatic()) {
			throw new tx_auxo_coreException(sprintf('Class %s property %s is not static', $classname, $attribute));
		}
		
		return $reflection->getStaticPropertyValue($attribute);
	}	
	
  /**
   * tx_auxo_inspector::hasMethod()
   *
   * @param string $classname
   * @param string $method
   * @return boolean
   */
	public static function hasMethod($classname, $method) {
		if (!($reflection = new reflectionClass($classname))) {
			throw new tx_auxo_exception(sprintf('Class %s could not be found', $classname));
		}
		
		return $reflection->hasMethod($method);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_inspector.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_inspector.php']);
}
?>