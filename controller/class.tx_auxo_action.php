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
 
abstract class tx_auxo_action extends tx_auxo_arrayObject {
		
	protected $context = NULL;
	protected $request = NULL;
	protected $response = NULL;
	
  /**
   * Initialize action instance with some basic data
   *
   * @param mixed $context
   * @return void
   */
	public function initialize($context) {
		$this->context = $context;	
		$this->request = $context->getService('request');
		$this->response = $context->getService('response');
		$this->configuration = $context->getService('configuration');
	}
	
  /**
   * tx_auxo_actions::beforeExecute()
   *
   * @return
   */
	public function beforeExecute() {	
	}
	
  /**
   * tx_auxo_actions::afterExecute()
   *
   * @return
   */
	public function afterExecute() {
	}
	
  /**
   * tx_auxo_actions::forward()
   *
   * @param  string $target target string either: module[/action] or route
   * @return void
   */
	public function forward($target) {
		if ($target[0] == '@') {
			/**
			 *  @TODO implementation of routing with symbolic names 
			 */
		}
		else {
			$module = $target['module'] ? $target['module'] : $this->get('module');
			$action = $target['action'];
			if (!$action) {
				throw new tx_auxo_actionException('forward: no action given');
			}
		}
				 			
		throw new tx_auxo_forwardException($module, $action);
	}
	
  /**
   * tx_auxo_actions::redirect()
   *
   * @param mixed $destination
   * @return
   */
	public function redirect($destination) {
		$link = tx_div::makeInstance('tx_lib_link');
		if (strpos($destination, '//') === true)  {
			$link->destination($pageId);
	   	    $link->redirect($link->makeUrl());
		}
		else {
   	    	$link->redirect($url);			
		}
		throw tx_auxo_actionException( tx_auxo_actionException::STOP_ACTION );			
	}
		
  /**
   * tx_auxo_actions::redirectIf()
   *
   * @param mixed $url
   * @param mixed $condition
   * @return
   */
	public function redirectIf($url, $condition) {
		if ($condition == true) {
			$this->redirect($url);
		}
	}


  /**
   * tx_auxo_actions::redirectUnless()
   *
   * @param mixed $url
   * @param mixed $condition
   * @return
   */
	public function redirectUnless($url, $condition) {
		if ($condition == false) {
			$this->redirect($url);
		}
	}
}

?>