<?php
/**
 * @package auxo
 * @subpackage 
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
 
class tx_auxo_aui_image extends tx_auxo_aui_HTMLcomponent {

	protected	$path;
	protected	$title;
	protected	$alt;
	protected	$height;
	protected	$width;
	
	public function __construct($path, $title='', $alt='') {
		parent::__construct();		
		$this->path = $path;
		$this->title = $title;
		$this->alt = $alt;
		$this->type = self::IMAGE;
	}
	
	public function setHeight($height) {
		$this->height = $height;
	}
	
	public function setWidth($width) {
		$this->width = $width;
	}

    public function render() {
 		$path = t3lib_div::getFileAbsFileName($this->path, 1);
		$path = substr($this->path, strlen(PATH_site));		
        // create image object
		$image = tx_div::makeInstance('tx_lib_image');
		$image->path($this->path);
		$image->title($this->title);
        $image->alt($this->alt);   	      
      	return $image->make();   	
     }
}

?>