<?php
/****************************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Andreas Horn
 *  Contact: Andreas.Horn@extronaut.de
 *  All rights reserved
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
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 ****************************************************************************/

/**
 * This interfaces is used to implement and observer/observable pattern. A class
 * implementing this interface is offers methods register or remove listener to events
 * triggered by this class.
 *
 * @package 	auxo
 * @subpackage	core
 * @author 		Andreas Horn
 * @copyright 	2007
 * @version 	$Id$
 * @access 		public
 */
 
interface tx_auxo_observable {
	/**
	 * Adds a listener to a observable class
	 *
	 * @param string $event event to listen to
	 * @param tx_auxo_observer $listener instance of a listener that implements tx_auxo_observer
	 */
	public function addListener($event, tx_auxo_observer $listener);
	
	/**
	 * Removes a listener for a given event from a obserable class
	 *
	 * @param tx_auxo_observer $listener instance of a listener that implements tx_auxo_observer
	 * @return boolen $removed true if removed
	 */
	public function removeListener($event, tx_auxo_observer $listener);
	
	/**
	 * Triggers an event and informed all registered listener that listen to 
	 *
	 * @param string $event
	 */
 	public function triggerEvent($event);	
}
?>