<?php
/**
 * @package auxo
 * @subpackage core
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
 

spl_autoload_register(array('tx_auxo_loader', 'load'));

class tx_auxo_loader {
	
	const CLASS_PREFIX = 'class.';
	
	static protected $classes = array();
	
	/**
	 * Add
	 * 
	 * Adds a path for auto loading of class files considering a comma separated list 
	 * of file extensions. Please note, only class.* files are included and their classname
	 * is determined based on its filename.
	 *
	 * @param string $path
	 * @param string $extensionList
	 */
	static public function add($path, $extensionList='php') {	
		$fullpath = t3lib_div::getFileAbsFileName($path);
		// TODO currently only files in this given directory are extracted but no deep 
		//      search is performed.
		$files = t3lib_div::getFilesInDir($fullpath, $extensionList, true); 
		foreach ($files as $key => $file) {
			// consider only class files
			if (strncmp(basename($file), self::CLASS_PREFIX, 6) != 0) continue;
			
			if (!is_readable($file)) {
				throw new tx_auxo_coreException(sprintf('code file %s can not be read', $file));
			}

			$className = str_replace('class.', '', basename($file));
			$className = substr($className, 0, strrpos($className, '.'));
			self::$classes[$className] = $file;
		}
	}
	
	/**
	 * load a specific class
	 *
	 * @param boolean class has been loaded
	 */	
	static public function load($className) {
		if (isset(self::$classes[$className])) {
			require_once(self::$classes[$className]);
			return true;
		}
		
		return false;
	}
	
	/**
	 * Loads all registered classes
	 *
	 * @return void
	 */
	static public function initialize() {
		foreach (self::$classes as $className => $file) {
			self::load($className);
		}
	}
	
	/**
	 * Create an instance of $className
	 *
	 * @param string $className
	 * @return object $instance
	 */
	static public function makeInstance($className) {
		if (class_exists($className)) {
			return new $className;	
		}
		else {
			echo $className.'does not exist';
		}
		return NULL;
	}
	
	/**
	 * Returns an array of module directories
	 *
	 * @param string $extension name of an user typo3 extension
	 * @return array $pathes array with pathes to templates ordered by priority
	 */
	static public function getModulePathes($extension) {
	    // add extension module pathes 
  		$pathes[] = t3lib_extMgm::extPath($extension) . 'lib/modules'; 
		$pathes[] = t3lib_extMgm::extPath($extension) . 'modules'; 		
		
		// add framework module pathes
		$pathes[] = t3lib_extMgm::extPath('auxo') . 'lib/modules';	
		$pathes[] = t3lib_extMgm::extPath('auxo') . 'modules';	
		return $pathes;
	}
	
	/**
	 * Returns an array of template directories
	 *
	 * @param string $extension name of an user typo3 extension
	 * @param string $module name of current module
	 * @return array $pathes array with pathes to templates ordered by priority
	 */
	static public function getTemplatePathes($extension, $module) {			
	    // add extension template pathes
  		$pathes[] = t3lib_extMgm::extPath($extension) . 'lib/templates'; 
		$pathes[] = t3lib_extMgm::extPath($extension) . 'modules/' . $module .'/templates'; 
		
		// add framework template pathes
		$pathes[] = t3lib_extMgm::extPath('auxo') . 'lib/templates';	
		$pathes[] = t3lib_extMgm::extPath('auxo') . 'modules/' . $module .'/tempates';
		return $pathes;		
	}
}
?>