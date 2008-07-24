<?php
/**
 * FilePathResolver 
 *
 * A path to a file is resolved following specific rules which are:
 * 
 * <example>
 * <prefix>:<path>|<filename>	a specific predefined mount point and a path
 * /<path>						an absolute path 
 * <path>/<filename>			a relative filepath to site
 * <path>/{location}/<filename> a variable location within a path using an identifier
 * </example>
 * placeholder like e.g. {assets}, {resources}, {templates} will be translate 
 * into project's directory names.
 *
 * @package auxo
 * @subpackage core
 * @license blabla 
 * @author andreas.horn@extronaut.de
 **/
class tx_auxo_filePathResolver {
	/**
	 * An array of placeholder which represents a certain part of a URI
	 *
	 * @var array 
	 */
	private $placeholders = array();
	
	/**
	 * An array of prefixes which represents certain locations 
	 * in the filesystem
	 *
	 * @var array
	 */
	private $prefixes = array();

	/**
	 * Scope of this class
	 *
	 * @var string
	 */
	public static $scope = 'singleton';
	
	/**
	 * Builds a filepath resolver
	 *
	 * @param tx_auxo_controller $controller main controller
	 */
	public function __construct(tx_auxo_controller $controller = NULL) {		
		$this->addPrefix('auxo', 'EXT:auxo');
		$this->addPrefix('extension', 'EXT:' . $controller->getExtension());
		$this->addPrefix('modules', 'extension:modules');
		$this->addPrefix('lib', 'extension:lib');
		$this->addPrefix('vendors', 'auxo:vendors');		
	}
	
	/**
	 * Adds a file prefix and its according path
	 *
	 * @param string $prefix prefix e.g. 'module:'
	 * @param string $path path representing a prefix
	 */
	public function addPrefix($prefix, $path) {
        if (substr($path, 0, 4) == 'EXT:') {
		   $this->prefixes[$prefix] = t3lib_div::getFileAbsFileName($path, 1);
        }
        else {
		   $this->prefixes[$prefix] = $path;
        }
	}
	
	/**
	 * Adds a placeholder and its according path snippet
	 *
	 * @param string $placeholder
	 * @param string $pathSnippet
	 */
	public function addPlaceholder($placeholder, $pathSnippet) {
		$this->placeholders[$placeholder] = $pathSnippet;	
	}
	
	/**
	 * Resolves a path
	 *
	 * @param string $filename
	 * @return string $path
	 */
	public function resolve($filename) {
		// check for prefixes
		if (strpos($filename, ':')) {
			if (!array_key_exists(strpos($filename, ':'),$this->prefixes)) {
				throw new tx_auxo_pathPrefixUnknownException($filename);
			}
		}
		
		if (preg_match_all('{[a-z0-9.-_]+}', $filename, $matches)) {
			tx_auxo_debug::dump($matches, 'matches');
		}
		
		foreach($this->placeholders as $placeholder => $pathSnippet) {
			$filename = str_replace($filename, '{' . trim($placeholder) . '}', $pathSnippet);
		}
		
		if (!t3lib_div::validPathStr($filename)) {
			throw new tx_auxo_coreException('invalid path can not be resolved');
		}
		
		$path = t3lib_div::getFileAbsFileName($this->path, 1);
		$path = substr($this->path, strlen(PATH_site));				

		return $path;
	}
}
?>