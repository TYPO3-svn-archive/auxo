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
 * Relation Has One
 *
 * @package auxo
 * @subpackage models
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */
class tx_auxo_relationHasOne extends tx_auxo_relation implements tx_auxo_associative {

  /**
   * tx_auxo_relationHasOne::__construct()
   *
   * @param object $schema
   * @param string $table
   * @param string $forgein_table
   * @param array  $options
   * @return void
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		parent::__construct($schema, $table, $forgein_table, $options);
		$this->forgein_key = isset($options['forgein_key']) ? $options['forgein_key'] : $table.'_id';
		$this->selectMethod = isset($options['selectMethod']) ? $options['selectMethod'] : 'selectSingle';
	}
	
  /**
   * tx_auxo_relationHasOne::get()
   *
   * @param mixed $name
   * @param mixed $object
   * @return
   */
 	public function get($object) {
 		if ($object->isNew()) {
			return NULL;
		}
	    $query = new tx_auxo_query();
	    $query->addWhere($this->forgein_key, $object->id());
	    $has_one = call_user_func(array($this->model, $this->selectMethod), $query);
	    $object->setInternal($this->forgein_table, $has_one);
	    return $has_one;
	}		


  /**
   * tx_auxo_relationHasOne::set()
   *
   * @param mixed $name
   * @param mixed $uid
   * @param mixed $object
   * @return
   */
	public function set($parent, $object) {
        // an already assigned object will be either deleted
        // or if not already saved to the database just 
		// overwritten
		if ($hasOne = $parent->get($this->forgein_key)) {
			if (!$hasOne->isNew()) {
				$this->delete($parent);
			} 
		} 
		// update forgein key in object
		$object->set($this->forgein_key, $parent->id());
        // set object in parent object
		$parent->setInternal($this->forgein_table, $object);
	}		

	
  /**
   * tx_auxo_relationHasOne::isValid()
   *
   * @param mixed $object
   * @return
   */
	public function isValid($object) {
	   if (is_object($has_one = $object->get($this->forgein_table))) {
	   	   return $has_one->isValid();
	   }
  	   return true;
	}


  /**
   * tx_auxo_relationHasOne::save()
   *
   * @param mixed $object
   * @return boolean $success
   */
	public function save($object) {
	   if (is_object($has_one = $object->get($this->forgein_table))) {
	   	   $has_one->set($this->forgein_key, $object->id());
	       return $has_one->save();	
	   }
  	   return true;
 	}

	
  /**
   * tx_auxo_relationHasOne::delete()
   *
   * @param mixed $object
   * @return
   */
	public function delete($object) {
		if (is_object($has_one = $object->get($this->forgein_table))) {
			switch($this->dependent) {
			   	case 'delete':
					return $has_one->delete();
					break;
				case 'release':
					$has_one->set($this->forgein_key, 0);
					return $has_one->save();
					break;
			}
		}
		return true;
	}

  /**
   * tx_auxo_relationHasOne::getJoinClause()
   *
   * @return
   */
	public function getJoinClause() {
		/* table A unique key 'uid' (default) has to be joined with 
		 * forgein table B using field forgein_key (default: <table>_id).
		 * for this kind of associations.
		 */
		$clause = sprintf(' LEFT OUTER JOIN %s AS %s ON %s.%s = %s.%s ', 
							$this->table,
							$this->getAlias($this->forgein_table),
							$this->getAlias($this->table), 
							$this->unique_key, 
		                  	$this->getAlias($this->forgein_table),
							$this->forgein_key
				   );		
		return $clause;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationHasOne.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mvext/class.tx_auxo_relationHasOne.php']);
}
?>