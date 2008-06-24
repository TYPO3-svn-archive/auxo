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

/**
 * Request 
 *
 */
class tx_auxo_request extends tx_auxo_arrayObject {

	const	  METHOD_GET  = 'GET';
	const	  METHOD_PUT  = 'PUT';
	const	  METHOD_POST = 'POST';
	const	  METHOD_HEAD = 'HEAD';
	
	protected $classname = '';
	
	public function __construct() {
		parent::__construct();
		$context = tx_auxo_context::getInstance();
		$this->setArray(t3lib_div::GParrayMerged($context->getService('controller')->getExtension()));
		// Initialize the cHash system if there are parameters available
		if ($GLOBALS['TSFE'] && count($parameters)) {
			$GLOBALS['TSFE']->reqCHash();
		}
		
		tx_auxo_debug::dumpIfEnabled($this->getArrayCopy, tx_auxo_debug::CONTROLLER, 'Request');
	}
	
	/**
	 * Sets class and method name
	 *
	 * @param string $classname
	 * @param string $method
	 * @return void
	 */
	public function setClassAndMethod($classname, $method) {
		$this->classname = $classname;
		$this->method = $method;
	}
	
	/**
	 * Returns name of class
	 *
	 * @return string $classname
	 */
	public function getClassname() {
		return $this->classname;
	}
	
	/**
	 * Returns name of request method. There are get, post, put, delete and head
	 *
	 * @return stirng $method
	 */
	public function getMethod() {
		return isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : self::METHOD_GET;
	}
	
	/**
	 * Returns current page Id
	 * 
	 * @return int $pageId Page Id
	 */
	public function getPageId() {
		return $GLOBALS['TSFE']->id;
	}
	
  /**
    * Moves an uploaded file into either the extension upload directory 
    * or if $target is an absolute path there.
	*
	* @param string $source
	* @param string $target
	* @throws tx_auxo_controller_exception if directory can not created
	* @throws tx_auxo_controller_exception if file can not be moved successfully
	*/
	public function moveUploadedFileTo($source, $target) {
		$filemode = octdec('0755');

		if ($filename[0] <> '/') {
			$path = t3lib_div::getFileAbsFileName('uploads/'. $this->context->getService('controller')->getExtension(), '/');
			if(!is_dir($path)){
				if(!mkdir($path)){
					// error handling in case of makedir errors
					throw new tx_auxo_controller_exception(sprintf('Can not create directory %s', $path));
				}
				else {
					@chmod($path, $filemode);
				}
			}
		}

		$filepath = $path . $target;
		if(move_uploaded_file($_FILES[$this->context->getService('controller')->getExtension()]['tmp_name'][$source], $filepath)) {
			// successfull moved to target directory and renamed
			@chmod($filepath, $filemode);
		} else {
			throw new tx_auxo_controller_exception(sprintf('Can not move uploaded file %s', $source));
		}
	}
}
?>