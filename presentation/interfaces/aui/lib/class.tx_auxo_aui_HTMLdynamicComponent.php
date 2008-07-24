<?php
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
 * @subpackage presentation
 * @version $Id$
 */

/**	
 * AUI Abstract Dynamic Component
 * 
 * This class represents an abstract dynamic component.
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class tx_auxo_aui_HTMLdynamicComponent extends tx_auxo_aui_HTMLcomponent {
	
	const	MOUSE_CLICK = 'onclick';
	const	MOUSE_OVER =  'onmouseover';
	
	const	FORM_GET = 'get';
	const	FORM_POST = 'post';
	
	protected $events = array();
	
	public function __construct($name='') {
		parent::__construct($name);
	}
	
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