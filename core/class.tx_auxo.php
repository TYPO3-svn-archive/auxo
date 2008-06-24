<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $Version$
 *
 * Last changed at: $LastChangedDate: 2008-01-26 00:42:35 +0100 (Sa, 26 Jan 2008) $
 * Last changed by: $LastChangedBy: AHN $
 * Last changed id: $LastChangedRevision: 20 $
 *  * 
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
 */
require_once(t3lib_extMgm::extPath('auxo') . 'core/class.tx_auxo_loader.php');
require_once(t3lib_extMgm::extPath('auxo') . 'controller/class.tx_auxo_controller.php');

/**
 * Auxo
 * 
 * Class that enables autoloading of all auxo classes.
 * 
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */

/**
 * This code is needed to implement a standard loader which covers all kind
 * of extension classes following MVC naming rules.
 */
class tx_auxo {

	static public $controller = NULL;

	/**
     * bootstrap
     *
     * @param mixed $controller
     * @return void
     */
	static public function bootstrap($controller) {	
		// set main controller	
		self::$controller = &$controller;
		
		// configure loader with framework directory structure
		tx_auxo_loader::add('EXT:div/.', 'php');
		tx_auxo_loader::add('EXT:lib/.', 'php');
		// libraries
		tx_auxo_loader::add('EXT:auxo/core', 'php');
		tx_auxo_loader::add('EXT:auxo/controller', 'php');
		tx_auxo_loader::add('EXT:auxo/domain', 'php');
		tx_auxo_loader::add('EXT:auxo/modules', 'php');
		tx_auxo_loader::add('EXT:auxo/plugins', 'php');
		tx_auxo_loader::add('EXT:auxo/presentation', 'php');
		tx_auxo_loader::add('EXT:auxo/presentation/interfaces/aui/lib', 'php');	
		tx_auxo_loader::add('EXT:auxo/exceptions', 'php');
		tx_auxo_loader::add('EXT:auxo/vendors', 'php');
		tx_auxo_loader::initialize();
		
		// register classes of user extension
		tx_auxo_loader::add('EXT:'.$controller->getExtension().'/.');		
		tx_auxo_loader::add('EXT:'.$controller->getExtension().'/schema');		
		
		// register shutdown 
		register_shutdown_function(array(__CLASS__, 'shutdown'));
	}	
	
	
  /**
   * shutdown
   *
   * @return void
   */
	static public function shutdown() {
		tx_auxo_cache::shutdown();	
	}
	

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/core/class.tx_auxo.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/core/class.tx_auxo.php']);
}
?>