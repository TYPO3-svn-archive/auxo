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
 * AUI Text Element
 * 
 * This class represents a simple text element. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_text extends tx_auxo_aui_HTMLdynamicComponent {

	protected	$name;
	protected	$text;

	public function __construct($name, $text='') {
		parent::__construct();		
		$this->name = $name;
		$this->text = $text;
		$this->type = self::TEXT;
	}
	
	public function setEventAction($event, $action) {
		$this->events[$event] = $action;
	}
	
	public function resetEventAction($event) {
		unset($this->events[$event]);
	}
	
	public function getEventAction($event) {
		return $this->events[$event];
	}
	
	public function setText($text) {
		$this->text = $text;
	}
	
	public function getText() {
		return $this->text;
	}
	
	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		$content = $renderer->renderTag($this, 'p', $options, $this->text);
		
		if (isset($this->events[self::MOUSE_CLICK])) {		   	
			return tx_auxo_aui_helper::renderLink($this->text, $this->events[self::MOUSE_CLICK], 1);
		}
		else {
			return $renderer->renderTag($this, 'p', $options, $this->text);
		}
	}
}

?>