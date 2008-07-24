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
	

	protected	$title;
	protected   $footer;
	protected	$data = NULL;
	protected	$width = '320';
	protected	$closeable = true;
	protected	$draggable = false;
	
	/**
	 * List of dependencies to javascript and CCS files 
	 *
	 * @var array list of files
	 */
	protected 	$dependencies = array(
						'container/assets/skins/sam/container.css',
						'yahoo/yahoo.js',
						'dom/dom.js',
						'event/event.js',
						'container/container.js'						
				);
	
 	/**
 	 * Creates an panel Widget
 	 *
 	 * @param string $name
 	 * @param tx_auxo_aui_layout $layout
 	 * @param string $title
 	 * @param string $footer
 	 */				
	public function __construct($name='', tx_auxo_aui_layout $layout=NULL, $title='', $footer='') {
		parent::__construct($name, $layout);
		$this->type   = self::PANEL;
		$this->title  = $title;
		$this->footer = $footer;
	}
	
	/**
	 * Defines panel width
	 *
	 * @param int $width width of panel
	 */
	public function setWidth($width) {
		$this->width = $width;
	}
	
	/**
	 * Defines a title 
	 *
	 * @param string $title title content
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Returns the current title of this panel
	 *
	 * @return string $title title of this panel
	 */

	public function getTitle() {
		return $this->title;
	}
	
	/**
	 * Defines a footer
	 *
	 * @param string $footer footer content
	 */
	public function setFooter($footer) {
		$this->footer = $footer;
	}
	
	public function getFooter() {
		return $this->footer;
	}

	/**
	 * Enables/disables panel's drag/drop feature. Panels could be move around
	 * by clicking its title.
	 *
	 * @param boolean $value
	 */
	public function setDragDrop($value) {
		$this->draggable = $value;
	}
	
	/**
	 * Enables/disables panel's close features 
	 *
	 * @param boolean $value
	 */
	public function setClose($value) {
		$this->closeable = $value;
	}
	
	/**
	 * Returns true if a panel could be closed by user interaction
	 *
	 * @return boolean $closeable
	 */
	public function isCloseable() {
		return $this->closeable;
	}
	
	/**
	 * Renders a panel widget
	 *
	 * @param tx_auxo_aui_renderer $renderer
	 * @return string $content 
	 */
	public function render(tx_auxo_aui_renderer $renderer) {
		// render java script to instansiate this panel
		$option = sprintf('close: %s, draggable: %s, visible: true, width:"%spx", constraintoviewport:true', 
		                  $this->closeable ? 'true' : 'false',
		                  $this->draggable ? 'true' : 'false',
						  $this->width
				  );
	    $script = sprintf('function onPanelReady_%s() { panel_%s = new YAHOO.widget.Panel("%s", { %s } ); panel_%s.render(); }', 
						  $this->getId(), $this->getId(), $this->getId(), $option, $this->getId());
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onPanelReady_%s);', $this->getId(), $this->getId());						  
		$renderer->addJavaSnippetToBuffer($script);
		// add javascript and CSS file dependencies 
		$renderer->addDependencies($this->dependencies);
		if ($this->draggable) {
			$renderer->addDependencies(array('dragdrop/dragdrop.js'));
		}
		// render markup
		$content = '';
	    if ($this->title) {
	        $content.= $renderer->renderTag($this, 'div', array('class' => 'hd', 'id' => $this->getId() . '_hd'), $this->title);
	    }
		
	    $content.= $renderer->renderTag($this, 'div', array('class' => 'bd', 'id' => $this->getId() . '_bd'), parent::renderItems($renderer, $this->items));

		if ($this->footer) {
	        $content.= $renderer->renderTag($this, 'div', array('class' => 'ft', 'id' => $this->getId() . '_ft'), $this->footer);			
		}		
		return $renderer->renderTag($this, 'div', array('id' => $this->getId()), $content);
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