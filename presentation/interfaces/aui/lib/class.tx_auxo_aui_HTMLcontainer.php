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
 
abstract class tx_auxo_aui_HTMLcontainer extends tx_auxo_aui_HTMLdynamicComponent {
	
	/**
	 * all kinds of container
	 */
	
	const FORM = 'form';
	const PANEL = 'panel';
	
	/**
	 *
	 */
	protected	$items = array();
	protected	$layout = NULL;
	protected 	$theme =  NULL;
	
	public function __construct($layout=NULL, $theme='aui') {		
		$this->layout = !$layout ? new tx_auxo_aui_flowLayout() : $layout;
		$this->theme = new tx_auxo_aui_theme($theme);
	}
	
	public function setLayout($layout) {
		$this->layout = $layout;
	}

	public function getLayout() {
		return $this->layout;
	}

	public function add($item, $options=array()) {
		if (!is_object($item)) {
			throw new tx_auxo_presentationException('only add objects to container');
		}		
		$this->items[] = $item;
		$item->setContainer($this);
	}
	
	public function delete($item) {
		unset($this->items[$item]);
	}
	
	/**
	 * Renders based on a given layout all items registered in this container
	 *
	 * @return $content rendered string
	 */
	public function renderItems() {
	    if (!$this->layout) {
	    	throw new tx_auxo_presentationException('no layout manager defined');
	    }
	    return $this->layout->render($this->items);				
	}
	
	public function getIterator() {

	}
	/**
	 * This is an default implementation of how to render an container with controls
	 *
	 * @return string $output rendered output
	 */
	public function render($content='') {
	 	if (!$this->getContainer()) {
	 		$fullpath = $this->theme->getStyleSheetPath('tx-auxo-aui-theme.css');
	 		tx_auxo_aui_toolbox::addStyleSheet($fullpath, '', 'text/css');
		    $options['class'] = $this->theme->getName();		
		    return tx_auxo_aui_toolbox::renderTag($this, 'div', $options, $content);	
		}
		
		return $content;
	}	
}
?>