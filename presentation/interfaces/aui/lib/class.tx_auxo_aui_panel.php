<?php
/**
 * @package auxo
 * @subpackage presentation
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
 
class tx_auxo_aui_panel extends tx_auxo_aui_HTMLcontainer {
	/**
	 *
	 */
	const		PANEL	= 'panel';
	
	/**
	 *
	 */	
	protected	$title;
	protected	$titleStyle;
	protected	$data = NULL;
	
	public function __construct($layout=NULL, $title='') {
		parent::__construct($layout);
		$this->type  = self::PANEL;
		$this->title = $title;
	}
	
	public function setTitle($title) {
		$this->title = $title;
	}

	public function getTitle() {
		return $this->title;
	}

	public function setTitleStyle($style) {
		$this->titleStyle = $style;
	}

	public function getTitleStyle() {
		return $this->titleStyle;
	}
	
	public function render() {
		$content = '';
	    if ($this->title) {
	    	$options['class'] = $this->getDefaultClass() . '-title';
	        $content.= tx_auxo_aui_toolbox::renderTag($this, $this->titleStyle, $options, $this->title);
	    }
		$content.= parent::renderItems($this->items);
		return parent::render(tx_auxo_aui_toolbox::renderTag($this, 'div', array(), $content));
	}
	
	public function setData($data) {
		$this->data = $data;
	}
	
	public function getData() {
		return $this->data;
	}

	public function set($key, $value) {
		$this->data->set($key, $value);
	}
	
	public function get($key) {
		return $this->data->get($key);
	}
	
	public function __set($key, $value) {
		$this->data->set($key, $value);
	}
	
	public function __get($key) {
		return $this->data->get($key);	
	}	
}
?>