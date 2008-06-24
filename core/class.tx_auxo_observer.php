<?php
/**
 * @package 	auxo
 * @subpackage 	util
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 	2007
 * @version 	$WCREV$
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
 * Observer
 *
 * This interface is used to implement and observer/observable pattern.
 * It is used mainly in model classes.
 *
 * @package auxo
 * @subpackage util
 * @author Andreas Horn
 * @access public
 */
 
interface tx_auxo_observer {
 	public function listen($event, $object);	
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_observer.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_observer.php']);
}
?>