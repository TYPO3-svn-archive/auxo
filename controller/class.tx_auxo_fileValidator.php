<?php
/**
 * fileValidator
 *
 */
class tx_auxo_fileValidator extends tx_auxo_validator {
	
	protected $mimetypes;
	protected $maxSize;
	protected $extension;
	
	
	public function __construct($mimetypes, $maxSize) {
		$this->mimetypes = $mimetypes;
		$this->maxSize = $maxSize;
		$this->extension = tx_auxo_context::getService('controller')->getExtension();
	}
	
	private function hasValidMimeType($filename) {
		// verify mime type of file
		$type = $_FILES[$this->extension]['type'][$filename];
		$type = substr($type, strrpos($type,'/')+1);
		if (!strstr($this->mimetypes, $type)) {
			return false;
		}
		
		return true;
	}
	
	private function hasValidSize($filename) {
		if ($_FILES[$this->extension]['size'][$filename] > $this->maxSize) {
			return false;
		}
		return true;
	}
	
	public function validate($filename) {
		if ($this->hasValidMimeType($filename) && $this->hasValidSize($filename)) {
			return true;
		}
	}
}
?>