<?php
/**
 * FilePathResolver
 *
 * A path to a file is resolved following specific rules which are:
 * 
 * EXT:<extension>/<path>|<filename>	a specific extension and path
 * /<path>								an absolute path has been used already
 * <path>/<filename>
 * 
 * placeholder like e.g.  %assets%, %resources%, %templates%
 * will be translate into project's default/configured directory names
 */
class tx_auxo_filePathResolver {
	
	private $placeholders = array();
	
	public function addPlaceholder($placeholder, $pathSnippet) {
		$this->placeholders[$placeholder] = $pathSnippet;	
	}
	
	public function resolveWebPath($filename) {
		foreach($this->placeholders as $placeholder => $pathSnippet) {
			$filename = str_replace($filename, $placeholder, $pathSnippet);
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