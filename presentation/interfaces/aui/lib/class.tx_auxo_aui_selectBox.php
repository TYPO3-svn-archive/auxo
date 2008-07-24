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
 * AUI Selection Element
 * 
 * This class represents an select element. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_selectBox extends tx_auxo_aui_HTMLcomponent {
	
	protected	$name;
	protected	$value;
	protected	$size;
	protected	$multiple;
	protected	$items = array();
	
	public function __construct($name, $size=10, $multiple=false) {
		parent::__construct();		
		$this->size = $size;
		$this->multiple = $multiple;
		$this->type = self::SELECTBOX;
	}
	
	public function addOption($label, $value, $selected=false) {
		$items[] = array($label, $value, $selected);	
	}
	
	public function	render(tx_auxo_aui_renderer $renderer) {
		$content = '';
		$options['name'] = $this->name;
		$options['value'] = $this->value;
		$options['size'] = $this->size;
		
		if ($this->multiple) $options['multiple'] = 'multiple';
		
		foreach($this->items as $item) {
			list($label, $value, $selected) = $item;			
			if ($selected) {
			    $content.= $renderer->renderTag($this, 'option', array('value' => $value, 'selected' => 'selected' ), $label);
			}
			else {
				$content.= $renderer->renderTag($this, 'option', array('value' => $value), $label);
			}
		}
		return $renderer->renderTag($this, 'select', $options, $content);
	}
}

?>