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
class tx_auxo_loader {
	
	const CLASS_PREFIX = 'class.';
	
	protected $classes = array();
	
	/**
	 * Initials a Auto Loader
	 */
	public function __construct() {	
		/* 
		 * @todo this list should be placed in an configuration file or something 
		 * to be more flexible.
		 */
		$this->add('EXT:div/.', false);
		$this->add('EXT:lib/.', false);
		// libraries
		$this->add('EXT:auxo/core', 'php');
		$this->add('EXT:auxo/controller', 'php');
		$this->add('EXT:auxo/domain', 'php');
		$this->add('EXT:auxo/presentation', 'php');	
		$this->add('EXT:auxo/exceptions', 'php');
		
		// register an auto load method
		spl_autoload_register(array($this, 'load'));				
	}
	
	/**
	 * Adds a path for auto loading of class files considering a comma separated list 
	 * of file extensions. Please note, only class.* files are included and their classname
	 * is determined based on its filename.
	 *
	 * @param string $path adds a path to php include path
	 * @param boolean $recursively traverse recursively $path and add all sub directories
	 */
	public function add($path, $recursively=true) {
		$fullpath = t3lib_div::getFileAbsFileName($this->sanitizePath($path));
		if (!is_readable($fullpath)) {
			throw new tx_auxo_IOException(sprintf('directory %s can not be read', $fullpath));	
		}
		
		$includePath = get_include_path() . $fullpath . ';';
		
		if ($recursively) {
			$iterator = new DirectoryIterator($fullpath);
			for($iterator->rewind(); $iterator->valid(); $iterator->next()) {
	        	if ($iterator->isDir() && strncmp($iterator->getFilename(),'.', 1) != 0) {
					if (!$iterator->isReadable()) {
						throw new tx_auxo_IOException(sprintf('directory %s can not be read', $iterator->getPath()));
					}	            
					$directory = $this->sanitizePath($iterator->getPathname());
					$includePath .= $directory . ';';            
					$this->add($directory, true);
	        	}
	        }
		}        

        set_include_path($includePath);
	}
	
	/**
	 * Cleans a path to unix-style
	 *
	 * @param string $path
	 * @return string $unixPath
	 */
	private function sanitizePath($path) {
		return str_replace('//', '/', str_replace('\\', '/', $path));
	}
	
	/**
	 * Autoloader that loads a specific class
	 *
	 * @param string name of a class that has to be loaded
	 */	
	public function load($classname) {
		if (substr($classname, 0, 3)=== 'tx_') {
			$filename = 'class.' . $classname . '.php';
			require_once($filename);
		}
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