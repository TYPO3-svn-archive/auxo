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
 * AUI Tooltip Element
 * 
 * This class might be used to define a tooltip for another AUI element.
 *
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_tooltip extends tx_auxo_aui_HTMLcomponent {	
	/**
	 * Title for a tooltip
	 *
	 * @var string $title
	 */
	protected $title;
	
	/**
	 * Tooltip text
	 *
	 * @var string $tooltip
	 */
	protected $tooltip = '';
	
	/**
	 * Defines which includes are needed to render this widget
	 *
	 * @var array $dependencies
	 */
	protected $dependencies = array(
								'container/assets/skins/sam/container.css',
								'yahoo/yahoo.js',
								'dom/dom.js',									
								'event/event.js',
								'container/container_core.js'
	                          );
	
	/**
	 * Create a new tooltip element
	 *
	 * @param string $title title of an tooltip (optional)
	 * @param string $content tooltip text 
	 */	                          
	public function __construct($content, $title='') {
		$this->title = $title;
		$this->content = $content;
	}
	
	/**
	 * Attaches this tooltip to the given parent element
	 *
	 * @param string $parent id of parent element
	 * @return tx_auxo_aui_tooltip $tooltip tooltip attached to parent
	 */
	public function attachTo(tx_auxo_aui_HTMLcomponent $parent) {
		$this->parent = $parent;
		return $this;
	}
	
	/**
	 * Renders a tooltip widget. Tooltips are always attached to a parent 
	 * component are not used standalone. Therefore is render method returns
	 * a code snippet.
	 *
	 * @param tx_auxo_aui_renderer $renderer
	 * @return $script rendered script 
	 * @throws tx_auxo_aui_noParentElementGivenException
	 * @throws tx_auxo_aui_noContentGivenException
	 */
	public function render(tx_auxo_aui_renderer $renderer) {
		if (!$this->parent) {
			throw new tx_auxo_aui_noParentElementGivenException('Widget: Tooltip');
		}
		if (!$this->content) {
			throw new tx_auxo_aui_noContentGivenException('Widget: Tooltip');
		}
		$renderer->addDependencies($this->dependencies);
		$script = sprintf('oTooltip_%s = new YAHOO.widget.Tooltip("oTooltip_%s", { context:"%s", text:"%s" } );', 
							$this->getId(), $this->getId(), $this->parent->getId(), 
							$this->content);
		return $script; 
	}
}
?>