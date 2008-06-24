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
 * Extender
 *
 * This core class enables to register plugins to all classes which implements
 * interface extendable. It is used mainly in model classes to register behaviour 
 * plugins but could be also used for classes that implement the interface
 * tx_auxo_extendable. 
 * Extensions have to be registered using method:
 * 
 * tx_auxo_extender::register('tx_auxo_modelbase', 'actsSortable', 'tx_auxo_acts_sortable');
 *
 * and extentable classes have to implement __call as following:
 *
 * function ___call($method, $parameters) {
 *    return tx_auxo_extender::callPlugin(__CLASS__, $this, $method, $parameters);	
 * } 
 *
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */
 
class tx_auxo_extender {
	
	private static $plugins = array();
		
  /**
   * Registers for a class an named plugin with all public methods and its object. 
   * Objects might be instances or classnames.
   *
   * @param string $classname name of the class that should be extended
   * @param string $plugin name of the plugin that extends the class
   * @param mixed  $object plugin class name or object
   * @return int $installed number of installed methods
   */
	static public function register($classname, $plugin, $callable, $args=NULL) {
		// class extentsion allowed?
		$extendable = new ReflectionClass($classname);
		if (!$extendable->implementsInterface('tx_auxo_extendable')) {
			throw new tx_auxo_exception('Class %s can not be extended', $classname);
		}
		
        $reflection = is_object($callable) ? 
						new ReflectionObject($callable) : 
						new ReflectionClass($callable);
		
		// register all public methods
		foreach ($reflection->getMethods() as $method) {
			if ($method->isPublic()) {
				$methodName = $method->getName();
				if (isset(self::$plugins[$classname][$methodName])) {
					if (self::$plugins[$classname][$methodName]['plugin'] <> $plugin) {
						throw new tx_auxo_exception('Plugin conflict %s', $methodName);
					}
				}
				self::$plugins[$classname][$methodName] = array(
                       'plugin'   => $plugin,
				       'callable' => $callable, 
				       'method'   => $method,
					   'args'     => $args
				);
				$installed++;
			}
		}
		
		return $installed;
	}
	
  /**
   * Calls a plugin method for an given class and passes given parameters. If no suitable method
   * is registered it throws and exceptions
   *
   * @param object  $object
   * @param string  $method
   * @param boolean $inherit
   * @param array   $parameters
   * @return mixed 	$result
   * @throws tx_auxo_exception method is not registered
   * @throws tx_auxo_exception no plugin registered
   */
	static public function callPlugin($object, $method, $inherit=true, $parameters=array()) {
		if (!($classname = get_class($object))) {
			throw new tx_auxo_exceptions('Classname can not be determined.');
		}
		
		if ($inherit) {
			while(!isset(self::$plugins[$classname])) {
				if (!($parent = get_parent_class($classname))) {
					break;
				}
				$classname = $parent;
			}
		}
				
		if (isset(self::$plugins[$classname])) {
			if (isset(self::$plugins[$classname][$method])) {
				$arguments = array_merge(array($object), $parameters); 
				if (isset(self::$plugins[$classname][$method]['args'])) {
					$arguments = array_merge($arguments, self::$plugins[$classname][$method]['args']);
				}
				$reflection = self::$plugins[$classname][$method]['method']; 
				if ($reflection->isStatic()) {
					return $reflection->invokeArgs(NULL, $arguments);
				}
				else {
					return $reflection->invokeArgs(self::$plugins[$classname][$method]['callable'], $arguments);
				}						
			}
			else {
				throw new tx_auxo_exception(sprintf('Method %s for class %s not registered', $method, $classname));
			}
		}
		else {
			throw new tx_auxo_exception(sprintf('No plugin registered for class %s', $classname));		
		}
	}
	
  /**
   * Removes all methods of an registered plugin from all classes
   *
   * @param mixed $plugin
   * @return void
   */
	static public function remove($plugin) {
		foreach (self::$plugins as $key => $plugin) {
			if ($plugin['plugin'] == $plugin) {
				unset(self::$plugins[$key]);
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_extender.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_extender.php']);
}
?>