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
 
class tx_auxo_aui_formButton extends tx_auxo_aui_pushButton {

	protected	$dependencies = array (
					'button/assets/skins/sam/button.css',
					'yahoo/yahoo.js',
					'dom/dom.js',
					'event/event.js',
				    'element/element-beta.js',
					'button/button.js' 
				);
					
	
	public function __construct($name, $label='', $value='') {
		parent::__construct($name, $label, $value);		
		$this->type = self::FORM_BUTTON;
	}
	
	/**
	 * Renders a form button
	 *
	 * @param tx_auxo_aui_renderer $renderer 
 	 * @return string $output
	 */
	public function	render(tx_auxo_aui_renderer $renderer) {				
		// fill option array
		$options['name'] = $this->name;
		$options['type'] = 'submit';			
		if ($this->value) $options['value'] = $this->value;
		
		// render label
		if ($this->image) $content = $this->image->render($renderer);
		if ($this->text) $content.= $this->text;
        
		// render tooltip if used
		if ($this->tooltip) {
        	$this->tooltip->setParent($this->getId());
        	$this->tooltip->renderer($renderer);	
        }
        
		// register dependencies
		$renderer->addDependencies($this->dependencies);
		
		// render java script
		$script = sprintf('function onButtonReady_%s() { oButton_%s = new YAHOO.widget.Button("%s"); }', 
					$this->getId(),	
					$this->getId(),
					$this->getId());
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onButtonReady_%s);', 
					$this->getId(),
					$this->getId());

		// renderer HTML markup
		$renderer->addJavaSnippetsToBuffer($script);							
		return $renderer->renderTag($this, 'button', $options, $content);
	}
}

?>