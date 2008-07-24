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
 * AUI Abstract Component 
 * 
 * This class represents an abstract UI component. All components have to extend this
 * class to implement a concrete component e.g. input fields, etc.
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
abstract class tx_auxo_aui_HTMLcomponent {

	const	AUXO_AUI_PREFIX = 'tx-auxo-aui';

	/**
	 * all kinds of controls
	 *
	 */
	const	INPUT_FIELD = 'input-field';
	const	HIDDEN_FIELD = 'hidden-field';
	const	PASSWORD_FIELD = 'password-field';
	const	SELECTBOX = 'selectbox';
	const	RADIOBUTTON = 'radiobutton';
	const   RADIOBUTTON_GROUP = 'radiobutton-group';		
	const	TEXTAREA = 'textarea';
	const	UPLOAD = 'upload';
	const	TEXT = 'text';
	const	CHECKBOX = 'checkbox';
	const	LINK_BUTTON = 'link-button';
	const	FORM_BUTTON = 'form-button';
	const	IMAGE = 'image';
	const   GEOMAP = 'geomap';
	const	SIMPLE_EDITOR = 'simple-editor';
	const	TOOLTIP = 'tooltip';
			
	protected $id = NULL;
	protected $class = NULL;
	protected $name = NULL;
	protected $type;
	protected $container;
	protected $tooltip = NULL;
	
	public function __construct($name='') {
		$this->name = $name;
		$this->id   = uniqid();
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function setId($id) {
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}
	
	public function setName($name) {
		$this->name = $name;
	}
	
	public function getName() {
		return $this->name;
	}
	
	public function setClass($class) {
		$this->class = $class;
	}
	
	 
	/**
	 * Returns the current tooltip of this widget
	 *
	 * @return tx_auxo_aui_tooltip $tooltip tooltip object
	 */
	public function getTooltip() {
		return $this->tooltip;
	}
	
	/**
	 * Defines a tooltip for this widget either as tooltip object 
	 * or as string
	 *
	 * @param mixed $tooltip tooltip object or string
	 * @throws tx_auxo_aui_unexpectedElement
	 */
	public function setTooltip($tooltip) {
		if ($tooltip instanceof tx_auxo_aui_tooltip) {
 		    $this->tooltip = $tooltip;		
		}
		elseif (is_string($tooltip)) {
			$this->tooltip = new tx_auxo_aui_tooltip($tooltip);
		}
		else {
			throw new tx_auxo_aui_unexpectedElementException('setTooltip: expects either string or a tooltip instance');
		}
		$this->tooltip->attachTo($this);		
	}	
	
	/**
	 * Returns classname of this component
	 *
	 * @param boolean $standard if true returns a default if no class has been defined 
	 * @return string $class name of css class
	 */
	public function getClass($standard=false) {
		return $this->class ? $this->class : $standard ? $this->getDefaultClass() : false;
	}
	
	public function setContainer($container) {
		$this->container = $container;
	}
	
	public function getContainer() {
		return $this->container;
	}
	
	public function getDefaultClass() {
		return self::AUXO_AUI_PREFIX . '-' . $this->getType();
	}
	
	abstract function render(tx_auxo_aui_renderer $renderer);
}

?>