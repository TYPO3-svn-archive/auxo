<?php

declare(ENCODING = 'UTF-8');

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
 * AUI Hidden Field Element
 * 
 * This class represents an hidden field element. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_hiddenField extends tx_auxo_aui_HTMLcomponent {

	const		HIDDEN_FIELD = 'hidden-field';
	
	protected	$name;
	protected	$value;
	
	public function __construct($name,  $value='') {
		parent::__construct($name);
		$this->type = self::HIDDEN_FIELD;
		$this->value = $value;		
	}
	
	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		$options['value'] = $this->value;
		$options['type'] = 'hidden';
		return $renderer->renderTag($this, 'input', $options);
	}
}
?>