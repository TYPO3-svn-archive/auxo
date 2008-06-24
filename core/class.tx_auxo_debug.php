<?php
/**
 * @package auxo
 * @subpackage core
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
 */

 
/**
 * Auxo
 * 
 * Class that enables extended debugging options.
 * 
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */


class tx_auxo_debug {

	const	CORE 		= 'core';
	const	MODELS		= 'models';
	const	CONTROLLER	= 'controller';
	const	SQL			= 'sql';
	const	VIEW		= 'viewer';
	const	HELPER		= 'helper';
	
	private static $components = array();
	
  /**
   * tx_auxo_debug::dumpIfEnabled()
   *
   * @param mixed  $object
   * @param string $component
   * @return void
   */
	public static function dumpIfEnabled($object, $component, $label='') {
		if (self::isEnabled($component)) {
			self::dump($object, $label);
		}		
	}
	
  /**
   * tx_auxo_debug::dump()
   *
   * @param mixed $object
   * @return void
   */
	public static function dump($object, $label='') {
		if ($object instanceof tx_auxo_modelbase) {
			self::modelDump($object);
		} else {
			debug($object, $label);
		}
	}
	
  /**
   * tx_auxo_debug::enable()
   *
   * @param mixed $component
   * @return void
   */
	public static function enable($component) {
		self::$components[$component] = true;
	}

  /**
   * tx_auxo_debug::disable()
   *
   * @param mixed $component
   * @return void
   */
	public static function disable($component) {
		self::$components[$component] = false;
	}
	
  /**
   * tx_auxo_debug::isEnabled()
   *
   * @param mixed $component
   * @return
   */
	public static function isEnabled($component){
		return self::$components[$component];
	}
	
  /**
   * modelDump
   *
   * This methods allows you to dump model contents without any
   * other stuff.
   *
   * @param  mixed $object instance of tx_auxo_modelbase other the current object
   * @return void
   */
    public static function modelDump($object, $level=1) {
    	$classname = get_class($object);
    	if ($level==1) printf('<b>%s</b>', $classname);		
		$object->rewind();
		while($object->valid()) {
			if ($object->current() instanceof tx_auxo_modelbase) {
				printf('<p>%s Object: %s</p>', self::printSpaces($level), $object->key());
				self::modelDump($object->current(), $level+1);				
			}
			elseif ($object->current() instanceof tx_lib_object) {
				printf('<p>%s Collection: %s</p>', self::printSpaces($level), $object->key());
				self::modelDump($object->current(), $level+1);
			}
			else {
				printf('<p>%s Member: "%s" = "%s"</p>', self::printSpaces($level), $object->key(), $object->current());
			}
			
			$object->next();
		}
	}	
	
  /**
   * tx_auxo_modelbase::printSpaces()
   *
   * @param mixed $level
   * @return
   */
	private function printSpaces($level) {
		for ($i=0; $i<$level*2;$i++) {
			$text.='-';
		} 
		return $text;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_debug.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_debug.php']);
}
?>