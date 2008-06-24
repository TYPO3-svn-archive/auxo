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
		
	public function __construct() {
	   $this->context = tx_auxo_context::getInstance();	
	   $this->configuration = $this->context->getService('configuration'); 
	   $this->controller = $this->context->getService('controller'); 
	}

   /**
     * method		addJSlibrary
     *
     * @param 	string	$path
     */
    public function addJSlibrary($path) {
        $this->addHeaderScript($path);
    }
	
  /**
   * tx_auxo_view::addHeaderScript()
   *
   * @param string $path
   * @param string $code
   * @param string $type
   * @return void
   */
	public function addHeaderScript($path='', $code='', $type='text/javascript') {
		if ($path) {
	        $scriptPath = t3lib_extMgm::siteRelPath($this->controller->getExtension()).$path;      	      	
	      	$identifier = $this->controller->getExtension().'_'.basename($path);
	      	$options['src'] = $scriptPath;
 		}
      	$options['type'] = $type;
      	$GLOBALS['TSFE']->additionalHeaderData[$identifier] = addTag('script', $options, $code);
	}  
	
		
	public function setModuleAction($module, $action) {
		$this->module = $module;
		$this->action = $action;
	}
	
	public function getModule() {
		return $this->module;
	}
	
	public function getAction() {
		return $this->action;
	}
	
	public function setContent($content) {
		$this->content = $content;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function setView($viewname) {
		$this->viewname = $viewname;
	}
	
	public function getView() {
		return $this->viewname;
	}
}

?>