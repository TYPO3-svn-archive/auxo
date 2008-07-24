<?php
/**
 * @package auxo
 * @subpackage 
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
 
class tx_auxo_panelLogger {
	
	static protected $format = '%m/%d/%y %H:%M:%S';
	static protected $messages = array();
	
	static public function add($message) {
		self::$messages[] = strftime(self::$format, time()). ':' . $message; 
	}
	
	/**
	 * Returns true if messages have been added to logger
	 *
	 * @return boolean $active messages have been logged
	 */
	static public function isActive() {
		return count(self::$messages) ? true : false;
	}
	
	static public function render() {
		$theme = new tx_auxo_aui_theme('aui');
		$fullpath = $theme->getStyleSheetPath('tx-auxo-aui-theme.css');
	    tx_auxo_aui_helper::addStyleSheet($fullpath, '', 'text/css');
	    // generate panel
		$content = tx_auxo_aui_helper::getTag('h3', array('class' => 'tx-auxo-aui-log-panel-title'), 'Developer Panel');
		foreach(self::$messages as $text) {
			$content .= tx_auxo_aui_helper::getTag('div', array('class' => 'tx-auxo-aui-log-panel-entry'), $text );
		}
		
		$panel = tx_auxo_aui_helper::getTag('div', array('class' => 'tx-auxo-aui-log-panel'), $content);
		return tx_auxo_aui_helper::getTag('div', array('class' => 'aui'), $panel);	
	}
}

?>