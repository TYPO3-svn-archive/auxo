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
 **/

/**
 * extendable
 *
 * This interfaces is used to implement an Extender/extendable pattern.
 * It is used mainly in model classes to register behaviour plugins but
 * could be also used for classes that implement this interface. 
 * Extensions have to be registered using class tx_auxo_extender::register 
 * and extentable classes have to implement __call as following:
 *
 * function ___call($method, $parameters) {
 *    return tx_auxo_extender::callPlugin(__CLASS__, $this, $method,s $parameters);	
 * } 
 *
 * @package auxo
 * @subpackage core
 * @author Andreas Horn
 * @access public
 */
 
interface tx_auxo_extendable {
	public function __call($method, $parameters);
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_extendable.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_extendable.php']);
}
?>