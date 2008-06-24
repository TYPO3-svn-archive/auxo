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
 
class tx_auxo_presentationManager {
	
	const	NATIVE = 'EXT:tx_auxo/native';
	
	protected	$interfaceName;
	protected	$interfaceLibraryPath;
	protected	$interfaceExtension;
	
	/**
	 * setUserInterface
	 * 
	 * Sets a specific user interface which is used within the presentation layer. Name of interfaces
	 * have to be composed as: EXT:<name of extension>/<name of interface>
	 *
	 * @param	string $interface
	 * @return	void
	 */
	public function setUserInterface($interface) {
		if (strcmp($interface, 'EXT:')!= 0) {
			throw new tx_auxo_presentationException(sprintf('Interface %s with wrong name', $interface));
		}
		
		$this->interfaceExtension = substr($interface, 4, strpos($interface, '/')-4);
		$this->interfaceName = substr($interface, strrpos($interface, '/')+1); 
		$this->interfaceLibraryPath = t3lib_extMgm::extPath($this->interfaceExtension) . 'presentation/interfaces/'. $this->interfaceName . '/lib';
		
		if (!is_readable($this->interfaceLibraryPath)) {
			throw new tx_auxo_presentationException(sprintf('Interface path %s not readable', $this->interfaceLibraryPath));
		}
	}
	
	/**
	 * Returns current user interface
	 *
	 * @return string name of interface
	 */
	public function getUserInterface() {
		return $this->interface;
	}
	
	/**
	 * getComponent
	 * 
	 * Creates an required presentation component and returns it
	 *
	 * @param	$component name of a component
	 * @param	$arguments various number of arguments passed to components constructor
	 * @return	$object component
	 */
	public function getComponent() {
		$parameters = func_get_args();
		if (count($paramters) < 1) {
			throw new tx_auxo_exception('component parameter is missing');
		}
		
		$path = sprintf('%s/class.tx_%s.php', $this->interfaceLibraryPath, $this->interfaceExtension, $parameters[0]);
		if (!is_readable($path)) {
			throw new tx_auxo_presentationException(sprintf('presentation component %s not supported', $parameters[0]));
		}
		
		$className = 'tx_' . $this->interfaceExtension . '_' . $parameters[0];
		unset($parameters[0]);
		
		require_once($path);
		
		if (!class_exists($className)) {
			throw new tx_auxo_presentationException(sprintf('presentation class %s is missing', $className));
		}
		
		$object = new $className();
		
		if (method_exists($object, '__construct')) {
			return call_user_func_array($object, '__construct', $parameters);
		}
		
		return $object;
	}
}

?>