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
	
	public function	render() {
		$options['name'] = $this->name;
		$content = tx_auxo_aui_toolbox::renderTag($this, 'p', $options, $this->text);
		
		if (isset($this->events[self::MOUSE_CLICK])) {		   	
			return tx_auxo_aui_toolbox::renderLink($this->text, $this->events[self::MOUSE_CLICK], 1);
		}
		else {
			return tx_auxo_aui_toolbox::renderTag($this, 'p', $options, $this->text);
		}
	}
}

?>