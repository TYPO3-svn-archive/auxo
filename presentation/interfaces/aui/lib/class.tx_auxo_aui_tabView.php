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
 
class tx_auxo_aui_tabView extends tx_auxo_aui_HTMLcontainer {
	/**
	 *
	 */
	const		TAB_VIEW = 'tabView';
	
	const		TOP	 	= 'top';
	const		LEFT	= 'left';
	const		RIGHT	= 'right';
	const		BOTTOM  = 'bottom';
	
	protected	$data = NULL;
	protected	$orientation;
	
	/**
	 * List of dependencies to javascript and CCS files 
	 *
	 * @var array list of files
	 */
	protected 	$dependencies = array(
						'tabview/assets/skins/sam/tabview.css',
						'yahoo/yahoo.js',
						'dom/dom.js',
						'event/event.js',
						'connection/connection.js',
						'element/element-beta.js',
						'tabview/tabview.js'						
				);
	
 	/**
 	 * Creates an panel Widget
 	 *
 	 * @param string $name
 	 * @param string $orientation
 	 */				
	public function __construct($name='', $orientation='top') {
		parent::__construct($name, $layout);
		$this->type = self::TAB_VIEW;
		$this->orientation = $orientation;
	}
		
	/**
	 * Renders items as tabs
	 *
	 * @param tx_auxo_aui_renderer $renderer renderer instance
	 * @param array $items array of tabs (container) 
	 * @return string $content rendered content
	 * @throws tx_auxo_aui_exception items is not a container
	 * 
	 */
	public function renderItems(tx_auxo_aui_renderer $renderer, $items) {
		// render tab titles
		$content = '<ul class="yui-nav">';
		foreach ($items as $item) {
			if (!$item instanceof tx_auxo_aui_HTMLcontainer) {
				throw new tx_auxo_aui_exception('TabView detected a non container objects');
			}
			$content.= '<li><a href="#"><em>' . $item->getTitle() . '</em></a></li>';
			$item->getTitle();
		}
		$content.= '</ul>';
		// render tab body
		$content.= '<div class="yui-content">';
		foreach ($items as $item) {
			if (!$item instanceof tx_auxo_aui_HTMLcontainer) {
				throw new tx_auxo_aui_exception('TabView detected a non container objects');
			}
			$content.= $item->render($renderer);
		}
		
		return $content;
	}
	
	/**
	 * Renders a tab view widget
	 *
	 * @param  tx_auxo_aui_renderer $renderer
	 * @return string $content 
	 * @throws tx_auxo_aui_exception TabView without any tabs
	 */
	public function render(tx_auxo_aui_renderer $renderer) {
		if (count($this->items) == 0) {
			throw new tx_auxo_aui_exception('TabView without any tabs');
		}
		
	    $script = sprintf('function onTabViewReady_%s() { tabView_%s = new YAHOO.widget.TabView("%s", {orientation: %s} ); }', 
						  $this->getId(), $this->getId(), $this->getId(), $this->orientation);
		$script.= sprintf('YAHOO.util.Event.onContentReady("%s", onTabViewReady_%s);', $this->getId(), $this->getId());						  
		$renderer->addJavaSnippetToBuffer($script);
		// add javascript and CSS file dependencies 
		$renderer->addDependencies($this->dependencies);
		// render markup
	    return $renderer->renderTag($this, 'div', array('class' => 'yui-navset'), $this->renderItems($renderer, $this->items));
	}
}
?>