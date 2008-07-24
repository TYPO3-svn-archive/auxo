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
 * @subpackage core
 * @version $Id$
 */

/**	
 * Configuration 
 * 
 * This class represents a TYPO3 configuration.
 * 
 * @package auxo
 * @subpackage core	
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
 class tx_auxo_Configuration extends tx_lib_configurations {

	public function __construct($configuration) {
		parent::__construct();
		$this->setTypoScriptConfiguration($configuration);
	}
	
	public function getPropertyDefault($property, $default='') {
		if (!$this->get($property)) {
			return $default;
		}
		
		return $this->get($property);
	}
}

?>