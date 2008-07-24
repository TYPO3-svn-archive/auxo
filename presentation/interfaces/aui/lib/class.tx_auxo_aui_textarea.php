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
 
class tx_auxo_aui_textarea extends tx_auxo_aui_HTMLcomponent {

	protected	$name;
	protected	$value;
	protected   $rows;
	protected	$columns;
	
	public function __construct($name, $rows, $columns, $value='') {
		parent::__construct();		
		$this->name = $name;
		$this->rows = $rows;
		$this->columns = $columns;
		$this->value = $value;
		$this->type = self::TEXTAREA;
	}

	public function setRows($rows) {
		$this->rows = $rows;
	}
	
	public function setColumns($columns) {
		$this->columns = $columns;
	}
	
 	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		$options['rows'] = $this->rows;
		$options['cols'] = $this->columns;
		return $renderer->renderTag($this, 'textarea', $options, $this->value);
	}
}

?>