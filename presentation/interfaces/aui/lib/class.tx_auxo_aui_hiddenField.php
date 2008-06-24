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
 
class tx_auxo_aui_hiddenField extends tx_auxo_aui_HTMLcomponent {

	const		HIDDEN_FIELD = 'hidden-field';
	
	protected	$name;
	protected	$value;
	
	public function __construct($name,  $value='') {
		parent::__construct($name);
		$this->type = self::HIDDEN_FIELD;
		$this->value = $value;		
	}
	
	public function	render() {
		$options['name'] = $this->name;
		$options['value'] = $this->value;
		$options['type'] = 'hidden';
		return tx_auxo_aui_toolbox::renderTag($this, 'input', $options);
	}
}

?>