<?php
/**
 * @package auxo
 * @subpackage core
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$
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
 
 class tx_auxo_response extends tx_auxo_arrayObject {
	
	protected $context = NULL;
	protected $configuration = NULL;
	protected $controller = NULL;
	protected $content  = '';
	protected $viewname = '';
	protected $action = '';
	protected $module = '';

	/**
	 * Builds an instance that represents an web reponse 
	 *
	 */
	public function __construct() {
	   $this->context = tx_auxo_context::getInstance();	
	   $this->configuration = $this->context->getService('configuration'); 
	   $this->controller = $this->context->getService('controller'); 
	}
	
	
 	/**
	 * Generates an URL with given parameters
	 *
	 * @param  array   $actions
	 * @param  boolean $htmlspecial
	 * @return string  $url
	 */
	public function generateURL($actions=array(), $htmlspecial=1) {
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
        return t3lib_div::locationHeaderUrl($url->makeURL($htmlspecial));		
	}	
	
	
  /**
   * Adds a script or coding to the HTML header of the current response
   *
   * @param string $path
   * @param string $code
   * @param string $type
   * @return void
   */
	public function addHeaderScript($path='', $code='', $type='text/javascript') {
		if ($path) {
	      	$options['src'] = $path;
	      	$code = ' ';
 		}
      	$options['type'] = $type;
      	$GLOBALS['TSFE']->additionalHeaderData[$this->controller->getExtension()].= tx_auxo_aui_helper::getTag('script', $options, $code);
	}  
	
	
	/**
	 * Adds an external stylesheet to the HTML Header
	 *
	 * @param string $path
	 * @param string $media
	 */
	public function addStyleSheet($path, $media='') { 	      	
	    $options['href'] = $path;
	    $options['type'] = 'text/css';
	    $options['rel']  = 'stylesheet';
	    if ($media) $options['media'] = $media;
      	
      	$GLOBALS['TSFE']->additionalHeaderData[$this->controller->getExtension()].= tx_auxo_aui_helper::getTag('link', $options);
	}
		
		
	/**
	 * Defines current module and action
	 *
	 * @param string $module
	 * @param string $action
	 */
	public function setModuleAction($module, $action) {
		$this->module = $module;
		$this->action = $action;
	}
	
	/**
	 * Returns name of current module
	 *
	 * @return string $module module name
	 */
	public function getModule() {
		return $this->module;
	}
	
	/**
	 * Returns name of current action
	 *
	 * @return string $action action name
	 */
	public function getAction() {
		return $this->action;
	}
	
	/**
	 * Sets content of response
	 *
	 * @param string $content content
	 */
	public function setContent($content) {
		$this->content = $content;
	}
	
	
	/**
	 * Returns current context of this response
	 *
	 * @return string $content
	 */
	public function getContent() {
		return $this->content;
	}
	
	
	/**
	 * Sets name of current view
	 *
	 * @param string $viewname name of current view
	 */
	public function setView($viewname) {
		$this->viewname = $viewname;
	}

	
	/**
	 * Returns name of current view
	 *
	 * @return string $viewname name of current view
	 */
	public function getView() {
		return $this->viewname;
	}
}
?>