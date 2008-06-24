<?php
/**
 * @package auxo
 * @subpackage view
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
 */

 
/**
 * Auxo
 * 
 * Class that enables autoloading of all auxo classes.
 * 
 * @package auxo
 * @subpackage presentation
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */

/**
 * This code is needed to implement a standard view.
 */
abstract class tx_auxo_view extends tx_auxo_arrayObject implements tx_auxo_extendable {

	/**
	 * predefined view types
	 *
	 */
	const NO_VIEW      = 'none';
	const SUCCESS	   = 'success';
	const ERROR        = 'error';
	const INPUT        = 'input';
	
	/**
	 * Controller object
	 *
	 * @var object
	 */
	public $context = NULL;
	public $controller = NULL;
	public $request = NULL;
	public $extension = '';
	
	public $module = '';
	public $action = '';
	
	protected $viewname = '';
	
	protected $template = '';
	protected $templatePath = '';
	
	
	/**
	 * Initialize the view
	 *
	 * @param	string $module name of module
	 * @param	string $action name of action
	 * @throws	tx_auxo_presentationException if no template engine has been defined
	 */
	public function __construct($module, $action, $viewname) {
		$this->context = tx_auxo_context::getInstance();
		$this->controller = $this->context->getService('controller');
		$this->request = $this->context->getService('request');
		$this->extension = $this->controller->getExtension();
		$this->module = $module;
		$this->action = $action;		
		$this->viewname = $viewname;

		// compose template name
		$this->setTemplate($action . ucfirst($viewname));
	}
	
  /**
   * includeSnippet
   *
   * A snippet is a partial html fragement which might be used in different views. Using snippets
   * also reduces the complexity of templates. Parameters could be passed to a shippet using array
   * $parameters.
   * Following rules are applies to parameter $snippet if no path is given:
   * - 1. configuration parameter "snippetsPath" is checked
   * - 2. $extensionPath . '/' . snippets is set if no configuration setting exist
   * - 3. EXT:<extension> is substituted within a path if supplied
   *
   * Snippets are cached by default. 
   *
   * @param string $snippet
   * @param array  $parameters
   * @return string $content
   */
	public function includeSnippet($snippet, $parameters=array()) {		
		$cacheID = md5($snippet . serialize($parameters));
		if (tx_auxo_cache::has($cacheID, 'SNIPPET')) {
			return tx_auxo_cache::get($cacheID, 'SNIPPET');
		}
		
		if (($sep = strpos($snippet, '/')) !== false) {
		    $path = substr($snippet, 0, $sep);
		    $snippet = substr($snippet, $sep + 1);
		}
		else {
			$path = t3lib_extMgm::extPath($this->controller->getExtension()) . 'snippets';
		}
			
		if (substr($path, 0, 3) == 'EXT') {
			$path = tx_div::resolvePathWithEXT($path);		
		}		
		
		$this->setTemplatePath($path);
		$this->setTemplate($snippet);
		$content = $this->renderOutput();
		tx_auxo_cache::add($cacheID, $content, 0, 'SNIPPET');
		return $content;
	}
	
 /**
   * Includes a view helper class so that can be used like $this->function(...);
   *
   * @param  $classname, ... 
   * @param  tx_auxo_viewException if no parameter has been provided
   * @return void
   */
	public function include_helper() {
		if (!($args = func_get_args())) {
			throw new tx_auxo_presentationException(tx_auxo_viewException::NO_PARAMETER_PROVIDED);
		}
		foreach($args as $arg) {
			tx_auxo_extender::register(get_class($this), $arg, 'tx_auxo_' . $arg . 'Helper');
		}
	}

	/**
	 * Sets data which have to be passed to a template
	 *
	 * @param mixed $data
	 */
	public function setData($data) {
		$this->data = $data;
	}
	
	/**
	 * Returns data which might modified within a template
	 *
	 * @return unknown
	 */
	public function getData() {
		return $this->data;
	}
	
	/**
	 * Searchs a template in a given path array
	 *
	 * @return string $path path to template
	 * @throws tx_auxo_presentationException if no template is found in path list
	 */
	public function findTemplatePath() {
  		foreach (tx_auxo_loader::getTemplatePathes($this->extension, $this->module) as $path) {
			$fullpath = $path . '/' . $this->getTemplate();
			if (is_readable($fullpath)) {
				return $path;
			}
  		}
  		
  		throw new tx_auxo_presentationException(sprintf('Template %s not found', $this->getTemplate()));
	}
	
	/**
	 * Render view based on configured template engine
	 *
	 * @return 	output stream
	 * @throws  tx_auxo_presentationException if template is not found in directory list
	 */
	abstract public function render();

	/**
	 * Sets a template path
	 *
	 * @param string $templatePath
	 */
	abstract public function setTemplatePath($templatePath);
	
	/**
	 * Returns the name of a template path
	 *
	 * @return string $templatePath
	 */
	abstract public function getTemplatePath();
		
	/**
	 * Sets a template file
	 *
	 * @param string $template
	 */
	abstract public function setTemplate($template);

	/**
	 * Returns the name of a template file
	 *
	 * @return string $template
	 */
	abstract public function getTemplate();
	
  /**
   * __call
   *
   * this method impements an plugin mechanism which is used to extend this class with
   * certain functionality. It is used to add methods of helper classes to a view and 
   * could be understand as a kind of configurable / dynamic strategy pattern.
   *
   * @param string $method	method name
   * @param mixed $args		arguments as array
   * @return mixed $value	return value
   */
   public function __call($method, $parameters) {
      return tx_auxo_extender::callPlugin($this, $method, true, $parameters);	
   }    

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_view.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_view.php']);
}
?>