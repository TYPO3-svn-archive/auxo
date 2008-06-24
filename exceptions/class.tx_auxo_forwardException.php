<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$
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
 
/**
 * Forward Exception
 * 
 * Represents a action exceptions that has been thrown. 
 * 
 * @package auxo
 * @subpackage controller
 * @author Andreas Horn
 * @copyright 2007
 * @version $Version$
 * @access public
 */
class tx_auxo_forwardException extends tx_auxo_exception {
	public $exceptionName = 'forwardException';
	
	protected	$module = '';
	protected 	$action = '';
	
	public function __construct($module, $action) {
		parent::__construct(sprintf('will be forwarded to %s/%s', $module, $action));
		$this->module = $module;
		$this->action = $action;
	}
	
	public function getModule() {
		return $this->module;
	}
	
	public function getAction() {
		return $this->action;
	}
}
?>