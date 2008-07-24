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
 
class tx_auxo_aui_scrollPanel extends tx_auxo_aui_panel {
	/**
	 *
	 */
	const		SCROLL_PANEL	= 'scroll-panel';
	
	protected	$width = 0;
	protected 	$height = 0;
	
	public function __constructor($name='', $layout=NULL, $title='', $width=0, $height=0) {
		parent::_construct($name, $layout, $title);
		$this->width = $width;
		$this->height = $height;
	}
	
	
	public function setWidth($width) {
		$this->width = $width;
	}
	
	public function setHeight($height) {
		$this->height = $height;
	}
	
	public function render(tx_auxo_aui_renderer $renderer) {
	    if ($this->$title) {
	       $content = $renderer->renderTag($this, $this->style, array(), $this->title);
	    }
	    else {
	       $content = '';
	    }
	    $options = array();
		$content.= parent::render($renderer);
		$style = 'overflow: scroll;';
		if ($this->width) $style .= 'width:'.$this->width . ';';
		if ($this->height) $style .= 'style:' . $this->height . ';';
		if ($style) $options['style'] = $style;
		return $renderer->renderTag($this, 'div', $options, $content);
	}	
}
?>