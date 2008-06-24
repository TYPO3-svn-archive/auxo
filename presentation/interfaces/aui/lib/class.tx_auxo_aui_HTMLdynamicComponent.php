<?php
/**
 * @package auxo
 * @subpackage presentation
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

abstract class tx_auxo_aui_HTMLdynamicComponent extends tx_auxo_aui_HTMLcomponent {
	
	const	MOUSE_CLICK = 'onclick';
	const	MOUSE_OVER =  'onmouseover';
	
	const	FORM_GET = 'get';
	const	FORM_POST = 'post';
	
	protected $events = array();
	
	/**
	 * Defines an action for an event
	 *
	 * @param unknown_type $event
	 * @param unknown_type $action
	 */
	public function setEventAction($event, $action) {
		$this->events[$event] = $action;
	}
	
	/**
	 * Resets a defined action for an event
	 *
	 * @param unknown_type $event
	 */
	public function resetEventAction($event) {
		unset($this->events[$event]);
	}
	
	/**
	 * Returns a defined action for an given event
	 *
	 * @param unknown_type $event
	 * @return unknown
	 */
	public function getEventAction($event) {
		return $this->events[$event];
	}
}
?>