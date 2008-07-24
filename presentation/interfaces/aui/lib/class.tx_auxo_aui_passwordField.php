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
 * AUI Input Password Element
 * 
 * This class represents an input password element. Data input is not displayed 
 * while typing.
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_passwordField extends tx_auxo_aui_inputField {
	
	protected	$name;
	protected	$value;
	protected	$size;
	
	public function __construct($name, $size=10, $value='') {
		parent::__construct($name, $size, $value);		
		$this->type = self::PASSWORD_FIELD;
	}
	
	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		$options['value'] = $this->value;
		$options['size'] = $this->size;
		$options['type'] = 'password';
		
		// render tooltip if used
		$this->renderTooltip($renderer);
		
		return $renderer->renderTag($this, 'input', $options);
	}
}

?>