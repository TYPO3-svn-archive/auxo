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
 * Error Decorator
 * 
 * This class represens an decorator pattern. It is a wrapper class that enclose HTML  
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_errorDecorator {

	protected $origin = NULL;
	protected $errors;
	
	public function __construct($origin, $errors) {
		$this->origin = $origin;
		$this->errors = $errors;	
	}
	
	/**
	 * Renders and decorates a control with errors messages
	 *
	 * @return string $content decorated content
	 */
	public function render($renderer) {
		$errorTags = '';
		foreach ($this->errors as $error) {
			$errorTags .= $renderer->renderTag($this->origin, 'div', array('class' => 'tx-auxo-aui-input-error'), $error);
		}
		return $errorTags . $this->origin->render($renderer);
	}
	
	public function __call($method, $parameters) {
		return call_user_func_array(array($this->origin, $method), $parameters);
	}
}
?>