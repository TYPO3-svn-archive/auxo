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
 * AUI Container
 * 
 * This class represents an abstract container in that components could be placed. Following
 * concrete container implementation exist currently form, panel, tabview.
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class tx_auxo_aui_HTMLcontainer extends tx_auxo_aui_HTMLdynamicComponent {
	
	/**
	 * all kinds of container
	 */
	
	const FORM = 'form';
	const PANEL = 'panel';
	const MODULE = 'module';
	
	/**
	 *
	 */
	protected	$items = array();
	protected	$layout = NULL;
	protected 	$theme =  NULL;
	
	/**
	 * Create a new container widget
	 *
	 * @param string $name
	 * @param tx_auxo_aui_layout $layout
	 * @param string $theme
	 */
	public function __construct($name, tx_auxo_aui_layout $layout=NULL, $theme='aui') {	
		parent::__construct($name);	
		$this->layout = !$layout ? new tx_auxo_aui_flowLayout() : $layout;
		$this->theme = new tx_auxo_aui_theme($theme);
	}
		
	/**
	 * Sets a layout manager
	 *
	 * @param tx_auxo_aui_layout $layout
	 */
	public function setLayout($layout) {
		$this->layout = $layout;
	}	
	
	/**
	 * Returns current layout manager
	 *
	 * @return tx_auxo_aui_layout $layout
	 */
	public function getLayout() {
		return $this->layout;
	}

	/**
	 * Adds a new UI element to this container
	 *
	 * @param mixed $item
	 * @param array $options
	 */
	public function add($item, $options=array()) {
		if (!is_object($item)) {
			throw new tx_auxo_presentationException('only add objects to container');
		}		
		$this->items[] = $item;
		$item->setContainer($this);
	}
	
	/**
	 * Removes an UI element from this container
	 *
	 * @param string $item name of UI element
	 */
	public function remove($item) {
		unset($this->items[$item]);
	}
	
	/**
	 * Renders based on a given layout all items registered in this container
	 *
	 * @return $content rendered string
	 */
	public function renderItems(tx_auxo_aui_renderer $renderer) {
	    if (!$this->layout) {
	    	throw new tx_auxo_presentationException('no layout manager defined');
	    }
	    return $this->layout->render($renderer, $this->items);				
	}

	
	/**
	 * This is an default implementation of how to render an container with controls
	 *
	 * @return string $output rendered output
	 */
	public function render(tx_auxo_aui_renderer $renderer, $content='') {
	 	if (!$this->getContainer()) {
		    $options['class'] = $this->theme->getName();		
		    return $renderer->renderTag($this, 'div', $options, $content);	
		}
		
		return $content;
	}	
}
?>