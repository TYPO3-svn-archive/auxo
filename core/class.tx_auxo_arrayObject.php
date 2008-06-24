<?php
/**
 * @package auxo
 * @subpackage 
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $Version$
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
 * arrayObject
 * 
 * General basic array class with same useful additional 
 * and shortcut methods.
 * 
 */
class tx_auxo_arrayObject extends ArrayObject {
	
	function set($key, $value) {
		$this->offsetSet($key, $value);
	}
	
	function get($key) {
		return $this->offsetGet($key);
	}
	
	function __set($key, $value) {
		$this->set($key, $value);
	}
	
	function __get($key) {
		return $this->get($key);	
	}
	
	/**
	 * Returns true if $key exists. This method is a short of 
	 * offsetExists. 
	 *
	 * @param mixed $key key value
	 * @return boolean $exists true if $key exists
	 */
	function has($key) {
        /*
         * @NOTE: seems to be a bug that entries which point to empty strings
         * are return as non-existing.
         * 
         */
		$data = $this->getArrayCopy();
		return isset($data[$key]);
	}
	
	/**
	 * Sets an array as values of this object
	 *
	 * @param array $array array of values
	 * @return void
	 */
	function setArray($array) {
		if (!is_array($array)) {
			throw new tx_auxo_coreException('no array provided');
		}
		$this->exchangeArray($array);
	}
	
	/**
	 * Returns an array of values
	 *
	 * @return array $values values of this object
	 */
	function getArray() {
		return $this->getArrayCopy();
	}
}

?>