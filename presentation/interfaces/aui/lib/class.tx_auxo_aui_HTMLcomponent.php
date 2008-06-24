<?php
/**
 * @package auxo
 * @subpackage 
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $Version$
 *
 * LICENSE:
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/
 
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
			
	protected $id = NULL;
	protected $class = NULL;
	protected $name = NULL;
	protected $type;
	protected $container;
	
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
	
	abstract function render();
}

?>