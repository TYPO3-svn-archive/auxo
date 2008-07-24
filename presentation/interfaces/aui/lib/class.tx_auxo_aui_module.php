<?php
/**
 * @package auxo
 * @subpackage ui
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
 
class tx_auxo_aui_module extends tx_auxo_aui_HTMLcontainer {
	
	/**
	 * A Module Title 
	 *
	 * @var string $title
	 */
	protected	$title = '';
	
	/**
	 * Create a new module widget
	 *
	 * @param string $name
	 * @param string $title
	 * @param tx_auxo_aui_layout $layout
	 */
	public function __construct($name, $title='', tx_auxo_aui_layout $layout=NULL) {	
		parent::__construct($name, $layout);	
		$this->title = $title;
		$this->type = self::MODULE;
	}
	
	/**
	 * Sets title of this module
	 *
	 * @param string $title
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Returns current module title
	 *
	 * @return string $title current module title 
	 */
	public function getTitle() {
	 	return $this->title;	
	}
	
	/**
	 * Renders a module widget
	 *
	 * @return string $output rendered output
	 */
	public function render(tx_auxo_aui_renderer $renderer) {
		return $renderer->renderTag($this, 'div', array(), $this->renderItems($renderer,$this->items));	
	}	
}
?>