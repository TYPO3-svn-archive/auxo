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
 
class tx_auxo_aui_demoButton extends tx_auxo_aui_text {
	protected	$name;
	protected	$content;

	public function __construct($name, $content) {
		parent::__construct($name, $text);		
		$this->name = $name;
		
		if (is_object($content)) {
			$this->image = $content;
		}
		else {
			$this->text = $content;
		}
		
		$this->yuiPath = t3lib_extmgm::extRelPath('auxo') . 'vendors/yui/build/';
		$this->type = self::LINK_BUTTON;
	}
	
	public function setText($text) {
		$this->text = $text;
	}
	
	public function getText() {
		return $this->text;
	}

	public function setImage($image) {
		$this->image = $image;
	}
	 
	public function getImage() {
		return $this->image;
	}
		
	public function	render() {
		$options['name'] = $this->name;
		
		if ($this->image) $content = $this->image->render();
		if ($this->text)  $content.= $this->text;

		// needed stylesheets
		tx_auxo_aui_toolbox::addStyleSheet($this->yuiPath . "button/assets/skins/sam/button.css");
		// dependencies
		tx_auxo_aui_toolbox::addHeaderScript($this->yuiPath . "yahoo-dom-event/yahoo-dom-event.js");
		tx_auxo_aui_toolbox::addHeaderScript($this->yuiPath . "element/element-beta-min.js");
		tx_auxo_aui_toolbox::addHeaderScript($this->yuiPath . "button/button-min.js");
		// java scripting
		$script = sprintf('function onButtonReady() { oButton = new YAHOO.widget.Button("%s"); }', $this->getId());
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onButtonReady);', $this->getId() );
		tx_auxo_aui_toolbox::addJavaScript($script);		
		
		$widget = tx_auxo_aui_toolbox::renderTag($this, 'button', array(), $content);
        return tx_auxo_aui_toolbox::getTag('div', array('class'=>'yui-skin-sam'), $widget);
	}
}

?>