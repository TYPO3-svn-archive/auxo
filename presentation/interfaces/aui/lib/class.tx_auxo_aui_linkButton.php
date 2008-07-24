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
 
class tx_auxo_aui_linkButton extends tx_auxo_aui_text {
	protected	$content;
	protected	$dependencies = array();
	
	public function __construct($name, $content, $target='#') {
		parent::__construct($name);		
		$this->target = $target;
				
		if (is_object($content)) {
			$this->image = $content;
		}
		else {
			$this->text = $content;
		}

		$this->dependencies = array (
			'button/assets/skins/sam/button.css',
			'yahoo/yahoo.js',
			'dom/dom.js',
		    'event/event.js',		
		    'element/element-beta.js',
			'button/button.js' 
		);
								
		$this->type = self::LINK_BUTTON;
	}

	
	/**
	 * Sets a text for this button
	 *
	 * @param string $text
	 */
	public function setText($text) {
		$this->text = $text;
	}
	
	/**
	 * Gets current text of this button
	 *
	 * @return unknown
	 */
	public function getText() {
		return $this->text;
	}

	/**
	 * Sets an image (icon) for this button
	 *
	 * @param string $imagePath
	 */
	public function setImage($imagePath) {
		$this->image = $imagePath;
	}
	 
	/**
	 * get path of current set image (icon)
	 *
	 * @return string $imagePath
	 */
	public function getImage() {
		return $this->image;
	}
		
	/**
	 * Renders this UI elements
	 *
	 * @return	string	(X)HTML output
	 */
	public function	render(tx_auxo_aui_renderer $renderer) {
		if ($this->name) {
		    $options['name'] = $this->name;
		}
		if ($this->image) $content = $this->image->render();
		if ($this->text)  $content.= $this->text;

		if (!$content) {
			throw new tx_auxo_aui_noContentGivenException('Widget: Linkbutton');
		}
		
		// render tooltip if used
		if ($this->tooltip) {
        	$renderedTooltip = $this->tooltip->render($renderer);	
        }
        else {
        	$renderedTooltip = '';
        }		
		
		// register dependencies
		$renderer->addDependencies($this->dependencies);
		
		// java scripting
		$script = sprintf('function onButtonReady_%s() { oButton_%s = new YAHOO.widget.Button("%s"); %s}', 
					$this->getId(),
				    $this->getId(),	
					$this->getId(),
					$renderedTooltip);
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onButtonReady_%s);', 
					$this->getId(),
					$this->getId());

		$renderer->addJavaSnippetToBuffer($script);							
		return $renderer->renderTag($this, 'a', array('href' => $renderer->renderURL($this->target)), $content);
	}
}

?>