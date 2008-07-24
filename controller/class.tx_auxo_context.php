<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $Id$
 * @scope singleton
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
 
 class tx_auxo_context {
 	/**
 	 * @var services array with all registered services
 	 */
	protected 		$services  = array(); 
	protected		$extension = '';
	
	/**
 	 * @var instance singleton of this class
 	 */	
	static private 	$instance = NULL;	
	
  /**
   * tx_auxo_context::getInstance()
   *
   * @return
   */
	public function getInstance() {
		if (!self::$instance) {
			self::$instance = new tx_auxo_context();
		}
		
		return self::$instance;
	}

  /**
   * tx_auxo_context::register()
   *
   * @param mixed $services
   * @param mixed $instance
   * @return
   */
 	public function register($service, $instance) {
		$this->services[$service] = $instance;
	}
	
	/**
	 * Checks if an service has been registered
	 *
	 * @param string $service name of a service
	 * @return boolean $exit true if this service has been registered
	 */
	public function hasService($service) {
		return $this->services[$service];
	}
	
  /**
   * tx_auxo_context::getService()
   *
   * @param mixed $service
   * @return
   */
	public function getService($service) {
		if (!isset($this->services[$service])) { 
			throw new tx_auxo_exception(sprintf('service %s not available', $service));
		}
		return $this->services[$service];
	}
	
	/**
	 * Returns name of current running extension
	 *
	 * @return string $extension name of an extension
	 */
	public function getExtension() {
		return $this->extension;
	}
	
  /**
   * tx_auxo_context::setExtension()
   *
   * @param mixed $extension
   * @return void
   */
	public function setExtension($extension) {
		$this->extension = $extension;
	}	
 }
?>