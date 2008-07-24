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
 * AUI Helper
 * 
 * A helper class with some static methods that offers easy access solutions to certain 
 * common rendering problems.
 *
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_helper {
	/**
	 * Javascript code included during rendered by TYPO3
	 * 
	 * @var string $buildScript 
	 */
	protected $buildScript = '';
	
  /**
   * Adds a script Tag to the HTML Header
   *
   * @param string $path path to string
   * @param string $code string with optional coding 
   * @param string $type script type text/javascript by default
   * @return void
   */
	static function addHeaderScript($path='', $type='text/javascript') {   
		$identifier = uniqid();   	
	    $options['src'] = $path;
	    $options['type'] = $type;
	    $GLOBALS['TSFE']->additionalHeaderData['auxo'].= self::getTag('script', $options,' ');
	}	
	
	/**
	 * Adds java script to output
	 *
	 * @param string $code
	 */
	static function addJavaScript($code='') {
	    $options['type'] = 'text/javascript';		
	    $GLOBALS['TSFE']->additionalHeaderData['auxo'].= self::getTag('script', $options, $code);		
	}
	
	/**
	 * Adds a external stylesheet to the HTML Header
	 *
	 * @param string $path
	 * @param string $media
	 */
	static function addStyleSheet($path, $media='') { 	      	
	    $identifier = md5($path);
	    $options['href'] = $path;
	    $options['type'] = 'text/css';
	    $options['rel']  = 'stylesheet';
	    if ($media) $options['media'] = $media;
      	
      	$GLOBALS['TSFE']->additionalHeaderData[$identifier] = self::getTag('link', $options);
	}
	
	/** 
	 * Rendered an HTML Tag
	 *
	 * @param object $component 	an native component
	 * @param string $tagName 		HTML Tag div, p, etc.
	 * @param array	 $options 		array of attributes
	 * @param string $content 		content that has to be enclosed by this tag
	 * @return string $tag 			generated tag
	 */
	static public function renderTag($component, $tagName, $options=array(), $content='') {
	    if (!$options['id'] && $component->getId()) {
	    	$options['id'] = $component->getId();
	    }
	    if (!$options['class'] && $component->getClass(true)) {
			$options['class'] = $component->getClass(true);
	    }

	    // add extension and brackets for tags: input, button
	    if (($tagName == 'input' || $tagName == 'button') && $options['name']) {
	    	$options['name'] = tx_auxo_context::getInstance()->getExtension() . '[' . $options['name'] . ']';
	    }
	    
	    return self::getTag($tagName, $options, $content);
	}

	
	/**
	 * Returns a renderered Tag with attributes
	 *
	 * @params $tagName	name of a tag
	 * @params $content content which has to be enclosed
	 * @return $tag 	tag with optional attributes
	 */
	static public function getTag($tagName, $options=array(), $content='') {
	    if (count($options)) {
		    foreach($options as $option => $value) {
		    	if (is_array($value)) {
		    		$attrList[$option] = $option. '="' . implode($value, ' ') . '"';
		    	}
		    	else {
		    	    $attrList[$option] = $option.'="'.htmlspecialchars($value).'"';
		    	}
		    }
		    if (isset($attrList)) {
  			    $attributes = ' '.implode($attrList, ' ');
		    }
	    }
	    else {
	    	$attributes = '';
	    }
	    
	    if ($content) {
	    	return sprintf('<%s%s>%s</%s>', $tagName, $attributes, $content, $tagName);
	    }
	    else {
	    	return sprintf('<%s%s />', $tagName, $attributes);
	    }
	}
	
	/**
	 * Generates an URL with given parameters
	 *
	 * @param  array   $actions
	 * @param  boolean $htmlspecial
	 * @return string  $url
	 */
	static public function generateURL($actions=array(), $htmlspecial=1) {
		$url = tx_div::makeInstance('tx_lib_link');
        // set target page either by given parameter array or as current page
        $url->destination($actions['pageId'] ? $actions['pageId'] : $GLOBALS['TSFE']->id);
        // TODO link attribute designator is always set equal current running extension
        // and needs to be more flexibel
        $url->designator(tx_auxo_context::getInstance()->getExtension());
        if (count($actions) > 0) {
           $url->parameters($actions);
        }
        $url->noHash();
        return $url->makeURL($htmlspecial);		
	}
	
	/**
     * renderLink
     *
     * @param  mixed	$label
     * @param  mixed	$actions
     * @param  boolean	$htmlspecial
     * @param  array  	$options
     * @return string 	$tag
     */
    static public function renderLink($label, $actions, $htmlspecial=1, $options=array()) {
    	$link = tx_div::makeInstance('tx_lib_link');
        $link->label($label, $htmlspecial);
        
        $link->destination($actions['pageId'] ? $actions['pageId'] : $GLOBALS['TSFE']->id);
        // TODO link attribute designator is always set equal current running extension
        // and needs to be more flexibel
        $link->designator(tx_auxo_context::getInstance()->getExtension());
        // set class attribute based on options
        if (isset($options['class'])) {
	        $link->classAttribute($actions['class']);
	    }
        $link->parameters($actions);
        $link->noHash();
        return $link->makeTag();
    }	
}
?>