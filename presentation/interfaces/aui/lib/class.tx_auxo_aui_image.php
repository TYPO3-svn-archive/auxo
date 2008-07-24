<?php

declare(ENCODING = 'UTF-8');

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *	
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */	

/**
 * @package auxo
 * @subpackage presentation
 * @version $Id$
 */

/**	
 * AUI Image Element
 * 
 * This class represents an image element. 
 * 
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_image extends tx_auxo_aui_HTMLcomponent {

	protected	$path;
	protected	$title;
	protected	$alt;
	protected	$height;
	protected	$width;
	
	/**
	 * Creates a new Image UI element. 
	 *
	 * @param string $path	path to image file
	 * @param string $title	title of this image
	 * @param string $alt	alternative title
	 */
	public function __construct($path, $title='', $alt='') {
		parent::__construct();		
		$this->path = $path;
		$this->title = $title;
		$this->alt = $alt;
		$this->type = self::IMAGE;
	}
	
	/**
	 * Sets height of image 
	 *
	 * @param int $height
	 */
	public function setHeight(int $height) {
		$this->height = $height;
	}
	
	/**
	 * Sets width of image 
	 *
	 * @param int $width
	 */
	public function setWidth(int $width) {
		$this->width = $width;
	}

	/**
	 * Renders a image UI element
	 *
	 * @param tx_auxo_aui_renderer $renderer
	 * @return string rendered output
	 */
    public function render(tx_auxo_aui_renderer $renderer) {
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