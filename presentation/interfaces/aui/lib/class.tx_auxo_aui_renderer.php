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
 * AUI Renderer
 * 
 * This class traverses and element tree and renderes HTML code.
 *
 * @package auxo
 * @subpackage presentation
 * @version $Id$	
 * @copyright Copyright belongs to the respective authors
 * @author andreas.horn@extronaut.de
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License, version 2
 */
class tx_auxo_aui_renderer {
	
	/**
	 * A unique list of includes/dependencies collected during the rendering process
	 *
	 * @var array $dependencies
	 */
	protected $dependencies = array();
	
	/**
	 * Path to Yahoo YUI
	 *
	 * @var unknown_type
	 */
	protected $YUIpath;	
	
	/**
	 * Javascript code needed to build a UI
	 *
	 * @var string $buildScript
	 */
	protected $buildScript;
	
	/**
	 * A namespace
	 *
	 * @var string $namesname
	 */
	protected $namespace;
	
	/**
	 * web response
	 *
	 * @var tx_auxo_aui_response $response
	 */
	protected $response;
	
	/**
	 * Renderer Constructor 
	 *
	 * @return void
	 */
	public function __construct() {
		$context = tx_auxo_context::getInstance();
		$this->configuration = $context->getService('configuration');
		$this->response = $context->getService('response');			
		$this->YUIpath = $this->configuration->getPropertyDefault('YUI.path', t3lib_extmgm::extRelPath('auxo') . 'vendors/yui/build/');
		$this->YUIsuffix = $this->configuration->getPropertyDefault('YUI.suffix', 'min');
		$this->namespace = $context->getExtension();

		if ($this->YUIsuffix == 'debug') {
			$this->addDependencies(array('logger/assets/skins/sam/logger.css', 'logger/logger-debug.js'));
		}
	}
	
	/**
	 * do something before main render process
	 *
	 * @return void
	 */
	public function preProcessing() {
	}
	
	/**
	 * Register dependencies at renderer
	 *
	 * @param array $dependencies array of filepathes
	 * @return void
	 */
	public function addDependencies($dependencies) {
		$this->dependencies = array_unique(array_merge($this->dependencies, $dependencies));		
	}
	
	/** 
	 * Renders an HTML Tag
	 *
	 * @param object $component 	an native component
	 * @param string $tagName 		HTML Tag div, p, etc.
	 * @param array	 $options 		array of attributes
	 * @param string $content 		content that has to be enclosed by this tag
	 * @return string $tag 			generated tag
	 */
	public function renderTag($component, $tagName, $options=array(), $content='') {
	    if (!$options['id'] && $component->getId()) {
	    	$options['id'] = $component->getId();
	    }
	    if (!$options['class'] && $component->getClass(true)) {
			$options['class'] = $component->getClass(true);
	    }

	    // add extension and brackets for tags: input, button
	    if (($tagName == 'input' || $tagName == 'button') && $options['name']) {
	    	$options['name'] = $this->namespace . '[' . $options['name'] . ']';
	    }
	    
	    return tx_auxo_aui_helper::getTag($tagName, $options, $content);
	}
	
	/**
	 * Adds a java script snippet to a list of bulding steps
	 *
	 * @param string $code
	 * @return void
	 */
	public function addJavaSnippetToBuffer($code) {
		$this->buildScript.= $code;
	}
	
	/**
	 * Renders all before added java snippets as html header script 
	 *
	 */
	protected function renderJavaSnippets() {
		$this->response->addHeaderScript(NULL, $this->buildScript);
	}

	/**
	 * Renders an URL with given actions 
	 *
	 * @param string $actions array of parameters
	 */
	public function renderURL($actions) {
		return $this->response->generateURL($actions);
	}
	
	/**
	 * do something after main render process
	 * 
	 * @return void
	 */
	public function postProcessing() {	
		$this->optimizeDependencies(array('reset/reset.css', 'fonts/fonts.css', 'grids/grids.css'), 'reset-fonts-grids/reset-fonts-grids.css');
		$this->optimizeDependencies(array('dom/dom.js', 'event/event.js', 'yahoo/yahoo.js'), 'yahoo-dom-event/yahoo-dom-event.js');
		$this->optimizeDependencies(array('dom/dom.js', 'event/event.js', 'yuiloader/yuiloader.js'), 'yuiloader-dom-event/yuiloader-dom-event.js');		
		$this->includeDependencies();
		$this->renderJavaSnippets();
	}
	
	/**
	 * Some includes are also available as pre-packed files. This
	 * method analyses which dependencies could be replaced by a single 
	 * pre-packed dependency. 
	 * 
	 * @param array $group list of dependencies which should be replaced
	 * @param string $combination name of dependency which replaces all items of $group
	 */
	protected function optimizeDependencies($group, $combination) {
		if (count(array_intersect($this->dependencies, $group)) == count($group)) {
			$this->dependencies = array_diff($this->dependencies, $group);
			/*
			 * combinations are inserted always at first position due to other dependencies
			 * which have to follow. This is not the best solution but works for now. Better
			 * would be to have a priority for sorting. 
			 */
			$this->dependencies = array_merge(array($combination), $this->dependencies);
		}		
	}
	
	/**
	 * Includes all registered files. Depending on configration setttings (suffix)
	 * either "-min", "-debug" or "-raw" javascript files are included.
	 * Currently, only CSS and JS files are handled.
	 *
	 * @return void
	 */
	protected function includeDependencies() {			
		foreach($this->dependencies as $dependency) {
			$componentPath = substr($dependency, 0, strrpos($dependency, '/')+1);

			switch (substr($dependency, strrpos($dependency, '.')+1)) {
				case 'css':
					$this->response->addStyleSheet($this->YUIpath . $dependency);
					break;
				case 'js':
					// building a new filepath considering the current YUI suffix
					if ($dependency == 'yahoo-dom-event/yahoo-dom-event.js') {
					    $filepath = sprintf('%s%s%s.js', $this->YUIpath, $componentPath, basename($dependency, '.js'));
					}
					else {
					    $filepath = sprintf('%s%s%s-%s.js', $this->YUIpath, $componentPath, basename($dependency, '.js'), $this->YUIsuffix);						
					}
					$this->response->addHeaderScript($filepath);
					break;
				default:
					throw new tx_auxo_aui_exception('unknown type of include file' . $dependency );
			}
		}					
	}
	
}
?>