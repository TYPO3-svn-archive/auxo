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
 * AUI Input Element
 * 
 * This class represents an data input element. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_inputField extends tx_auxo_aui_HTMLcomponent {

	protected	$name;
	protected	$value;
	protected	$size;
	
	/**
	 * Creates a new  form input field
	 *
	 * @param string $name
	 * @param int	$size
	 * @param string $value
	 */
	public function __construct($name, $size=10, $value='') {
		parent::__construct();		
		$this->type = self::INPUT_FIELD;
		$this->name = $name;
		$this->size = $size;
		$this->value = $value;
	}
	
	/**
	 * Helper method for all child classes to simplify tooltip
	 * rendering.
	 *
	 * @param tx_auxo_aui_renderer $renderer Renderer instance
	 */
	protected function renderTooltip(tx_auxo_aui_renderer $renderer) {		
		// render tooltip if used
		if ($this->tooltip) {
			// render java script for tooltip
			$script = sprintf('function onFieldReady_%s() { %s }', 
						$this->getId(),
						$this->tooltip->render($renderer));
			$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onFieldReady_%s);', 
						$this->getId(),
						$this->getId());
	
			$renderer->addJavaSnippetToBuffer($script);										
		}		
	}
	
	/**
	 * Renders an input field
	 *
	 * @return unknown
	 */
	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		$options['value'] = $this->value;
		$options['size'] = $this->size;
		$options['type'] = 'input';
		
		$this->renderTooltip($renderer);
		
		return $renderer->renderTag($this, 'input', $options);
	}
}

?>