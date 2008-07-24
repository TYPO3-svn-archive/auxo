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
 * AUI Template
 * 
 * This abstract class represents an AUI template which is part of the presentation 
 * layer. It offers a simple data interface to pass information as an array object 
 * which might be used in the template for output generation. 
 * 
 * User have to extend this class and to implement method build to generate their 
 * templates. 
 *
 * @abstract 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class tx_auxo_aui_template extends tx_auxo_arrayObject {
	/**
	 * Builds an container with all elements that should be rendered
	 * for presentation.
	 *
	 * @abstract
	 * @return tx_auxo_aui_container $container
	 */
	abstract public function build();
}
?>