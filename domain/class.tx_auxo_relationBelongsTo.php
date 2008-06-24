<?php
/****************************************************************************
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
 ****************************************************************************/

/**
 * This class implements methods to handle database relations of type N:1
 *
 * @package auxo
 * @subpackage models
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */
class tx_auxo_relationBelongsTo extends tx_auxo_relation implements tx_auxo_associative {

  /**
   * tx_auxo_relationBelongsTo::__construct()
   *
   * @param object $schema
   * @param string $table
   * @param string $forgein_table
   * @param array  $options
   * @return
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		parent::__construct($schema, $table, $forgein_table, $options);
		if (!isset($options['forgein_key'])) {
			$this->forgein_key = $forgein_table.'_id';
		}
		else {
			$this->forgein_key = $options['forgein_key'];
		}		
	}
	
  /**
   * tx_auxo_relationBelongsTo::get()
   *
   * @param mixed $object
   * @return
   */
 	public function get($object) {
        if (!$uid = $object->get($this->forgein_key)) {
			return NULL;
		}
		
	    $belongsTo = call_user_func(array($this->model, 'select'), $uid);
	    $object->setInternal($this->forgein_table, $belongsTo);
	    return $belongsTo;
	}		

  
  /**
   * tx_auxo_relationBelongsTo::set()
   *
   * @param mixed $object
   * @return
   */
	public function set($parent, $object) {
		// update forgein key 
		$forgein_id = $object->get($this->schema->getUniqueKey($this->forgein_table));
		if ($forgein_id) {
	        $parent->set($this->forgein_key, $this->forgein_id);
        }
		$parent->setInternal($this->forgein_table, $object);
		// update object in parent object
	}		

  /**
   * tx_auxo_relationBelongsTo::isValid()
   *
   * @param mixed $object
   * @return void
   */
	public function isValid($object) {
		return true;		
	}
	
  /**
   * tx_auxo_relationBelongsTo::save()
   *
   * @param mixed $object
   * @return
   */
	public function save($object) {
	    // nothing to do here
  	   return true;
 	}

	
  /**
   * tx_auxo_relationBelongsTo::delete()
   *
   * @param mixed $object
   * @return
   */
	public function delete($object) {
		// nothing to do here
		return true;
	}
	
	public function getJoinClause( ) {
		/* table A has a field <forgein_table>_id (default) that indicates
		 * an association as "belongs_to". It is used to fetch its 
		 * corresponding record from table B.
		 */
		$clause = sprintf(' LEFT OUTER JOIN %s AS %s ON %s.%s = %s.%s ', 
							$this->forgein_table,
							$this->getAlias($this->forgein_table), 
							$this->getAlias($this->table), 
							$this->unique_key,
							$this->getAlias($this->forgein_table), 
							$this->forgein_key
				   );	
		return($clause);
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationBelongsTo.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mvext/class.tx_auxo_relationBelongsTo.php']);
}
?>