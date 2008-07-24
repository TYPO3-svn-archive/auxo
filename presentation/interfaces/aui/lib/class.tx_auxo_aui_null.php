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
 * AUI Null Element
 * 
 * This class represents an null element that could be used as invisible placeholder 
 * in e.g. certain layouts. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_null extends tx_auxo_aui_HTMLcomponent {

	const		NULL = 'null';
	
	protected	$name;

	/**
	 * Creates a dummy (null) UI element
	 *
	 * @param string $name
	 */
	public function __construct($name='') {
		parent::__construct();		
		$this->name = $name;
		$this->type = self::NULL;
	}
		
	/**
	 * Renders a dummy UI element
	 *
	 * @param tx_auxo_aui_renderer $renderer
	 * @return string rendered output
	 */
	public function	render(tx_auxo_aui_renderer $renderer) {
		return '';
	}
}

?>