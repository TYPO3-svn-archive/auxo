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
 
class tx_auxo_aui_simpleEditor extends tx_auxo_aui_text {
	
	protected	$dependencies = array(
			                       	 'editor/assets/skins/sam/simpleeditor.css', 
		                             'yahoo/yahoo.js',
			                         'dom/dom.js',
									 'event/event.js',
		                             'element/element-beta.js',
									 'container/container-core.js',
		                             'editor/simpleeditor-beta.js'
	                            );
	protected	$name;
	protected	$content;
	protected 	$width;
	protected	$height;

	public function __construct($name, $content) {
		parent::__construct($name, $text);		
		$this->name = $name;
		$this->height = '300px';
		$this->width = '400px';
		$this->text = $content;		
		$this->type = self::SIMPLE_EDITOR;
	}
	
	public function setHeight($height) {
		$this->height = $height;
	}

	public function setWidth($width) {
		$this->width = $width;
	}
	
	public function setText($text) { 
		$this->text = $text;
	}
	
	public function getText() {
		return $this->text;
	}
	
	/**
	 * Renders UI element "SimpleEditor"
	 *
	 * @return string XHTML 
	 */
	public function	render(tx_auxo_aui_renderer $renderer) {
		$options['name'] = $this->name;
		if ($this->text)  $content.= $this->text;

		// java scripting
		$script = sprintf('function onEditorReady_%s() { 
		                        oEditor_%s = new YAHOO.widget.SimpleEditor("%s", { 
				                                 height: "%s", 
				                                 width: "%s", 
				                                 dompath: true 
	                                          }); 
		                        oEditor_%s.render(); 
	                       }', 
		                   $this->getId(), $this->getId(), $this->getId(), 
		                   $this->height, $this->width,
		                   $this->getId());
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onEditorReady_%s);', 
		                   $this->getId(),
		                   $this->getId());		                   
		
		$renderer->addJavaSnippetToBuffer($script);				
		$renderer->addDependencies($this->dependencies);
		// render widget
		return $renderer->renderTag($this, 'textarea', $options, $content);
	}
}

?>