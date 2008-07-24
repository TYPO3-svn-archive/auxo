<?php
/**
 * @package auxo
 * @subpackage ui
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
 
class tx_auxo_aui_form extends tx_auxo_aui_HTMLcontainer {
	/**
	 * @var $title
	 */	
	protected	$title;
	protected	$titleStyle;
	protected 	$method;
	protected	$action;
	
	protected	$decorator = NULL;
	protected	$errors = array();
	
	public function __construct($layout=NULL, $title='') {
		parent::__construct($layout);
		$this->type = self::FORM;
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

	public function setCharset($charset) {
		$this->charset = $charset;
	}
	
	public function add($item, $options=array()) {
		if ($item->getType() <> tx_auxo_aui_HTMLcomponent::HIDDEN_FIELD &&
		    $item->getType() <> tx_auxo_aui_HTMLcomponent::TEXT &&
		    $item->getType() <> tx_auxo_aui_HTMLcomponent::IMAGE) {
			if (isset($this->errors[$item->getName()])) {
		    	return parent::add(new $this->decorator($item, $this->errors[$item->getName()]), $options);		
		   }
		}
		parent::add($item, $options);
	}
	
	public function setErrors($errors, $decorator='tx_auxo_aui_errorDecorator') {
		$this->errors = $errors;
		$this->decorator = $decorator;
	}
	
	/**
	 * Renders a presentation of this UI element
	 *
	 * @param tx_auxo_aui_renderer $renderer
	 * @return string $rendered output
	 */
	public function render(tx_auxo_aui_renderer $renderer) {
		// add title bar if required
		if ($this->title) {
	    	$options['class'] = $this->getDefaultClass() . '-title';
	        $content = $renderer->renderTag($this, $this->titleStyle, $options, $this->title);
	    	unset($options);
		}
		
		// set action & module, etc. as hidden fields
		foreach($this->events[key($this->events)] as $key => $value) {
		   $this->add(new tx_auxo_aui_hiddenField($key, $value));			
		}

		// extract hidden fields
		foreach ($this->items as $item) {
			if ($item->getType() == tx_auxo_aui_hiddenField::HIDDEN_FIELD) {
			   $hiddens[] = $item;		
			   unset($item);
			}
		}
		// render container items
		$content .= parent::renderItems($renderer, $this->items);
		
		// render hidden fields
  		if (isset($hiddens)) {
  		    foreach($hiddens as $hidden) {
  		    	$content .= $hidden->render($renderer);
  		    }
  		}
		
  		$options['method'] = key($this->events);
		$options['action'] = tx_auxo_aui_helper::generateURL();
		$options['name'] = $this->name;
		
		if ($this->charset) $options['accept-charset'] = $this->charset;

		return parent::render($renderer, $renderer->renderTag($this, 'form', $options, $content));
	}
}
?>