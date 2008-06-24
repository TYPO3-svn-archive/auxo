<?php
/*****************************************************************************
 *  Copyright notice
 *
 *  (c) 2007 Andreas Horn
 *  Contact: Andreas.Horn@extronaut.de
 *  All rights reserved
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
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *****************************************************************************/
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');
 
class tx_auxo_models_notes extends tx_auxo_modelbase {	
		/** tablename **/
		public static $table = 'tx_auxo_notes';
		
		/** caching **/
		public static $caching = true;
		
		/** autofields **/
		public $autofields = array();
	
  /**
   * Constructor
   *
   * @param mixed $parameter1
   * @param mixed $parameter2
   * @return void
   */
	public function __construct($parameter1=NULL, $parameter2=NULL) {
		parent::__construct(__CLASS__, $parameter1, $parameter2);
	}

  	
 /**
   * Select
   *
   * Select records belonging to this model and returns them
   * as a list of objects or as object if only one object has been required. 
   *
   * @param 	mixed	either a $query object tx_auxo_query or record id (uid)
   * @param 	mixed   $limit either a array like (min, max) or just a integer value
   * @return	object  $result list of objects based on tx_lib_object or model's object
   */
	public function select($parameter=NULL, $limit=array()) {
		return parent::_select(__CLASS__, $parameter, $limit);
	}
 	
 /**
   * selectCount
   *
   * Counts and returns number of records based on criterias defined with 
   * a query object given for this model. One might use this method without 
   * using query objects then its returns the total number records.
   * 
   * @param 	object 	$query query object tx_auxo_query
   * @return	integer	$count number of records
   */
	public function selectCount($query=NULL) {
		return parent::_SelectCount(__CLASS__, $query);			 	
 	}
 	
	
  /**
   * selectSingle
   *
   * Select only a single record based on a given query object. It returns
   * either an object of this model or NULL. 
   *
   * @param 	object	$query
   * @return	object 	$object
   */
	public function selectSingle($query) {
		return parent::_selectSingle(__CLASS__, $query);	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_models_notes.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_models_notes.php']);
}
?>	