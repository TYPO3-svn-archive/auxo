<?php
/**
 * @package 	auxo
 * @subpackage 	core
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 	2007
 * @version 	$Id$
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
 * A class implementing this interface will be triggered by an observed instance
 * via a listen method about an event.
 *
 * @package auxo
 * @subpackage core
 * @author Andreas Horn
 * @access public
 */
interface tx_auxo_observer {
	/**
	 * Listen to events of an observed instance
	 *
	 * @param string $event name of an event
	 * @param object $object observed instance 
	 */
 	public function listen($event, $object);	
}
?>