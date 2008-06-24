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
 * Handles relationships of type 1:N but not in its usally way. Key references of table A
 * belonging not to the associated table B but are stored as comma separated list in a 
 * field of table A. This kind of approach allows a fast data access to reference records
 * of table B. Moreover, sorting and others things could be performed easily and without
 * changing records of table B.
 * Disadvantage of this approach is that no back reference from table B could be made without 
 * visiting all existing records of table A. Therefore eager loading is not supported.
 * NOTE: table B <b>must not</b> define an belongs_to relationship to table A because of the 
 * general idea of this approach.
 *
 * Example:
 *
 * Persons
 *    id, int(11)
 *    name, varchar(20)
 *    ...
 *    address_ids, tinyblob
 *    ...
 *
 * Address
 *    id, int(11)
 *    location, char(30)
 *    ...
 *
 * Default name the forgein key field is the table to which an relationship is defined 
 * suffixed with '_ids' e.g. address_ids. It could be overwritten using option 'forgein_key'.
 * 
 * @package auxo
 * @subpackage models
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */
class tx_auxo_relationHasManyInline extends tx_auxo_relation implements tx_auxo_associative {

	
  /**
   * Intializes an instance of this relationship
   *
   * @param object $schema
   * @param string $table
   * @param string $forgein_table
   * @param array  $options
   * @return void
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		parent::__construct($schema, $table, $forgein_table, $options);
		$this->setOption('forgein_key', $options, $table.'_ids');
		$this->setOption('selectMethod', $options, 'select');

		if (isset($options['conditions'])) {
			if ($options['conditions'] instanceof tx_auxo_query) {
				throw new tx_auxo_exception('Relationship %s -> %s has an invalid condition', $table, $forgein_table);
			}
		} 		
	}
	
  /**
   * Returns an collection of all records belonging this parent object. 
   *
   * @param $object parent object which keeps a field with primary keys of associated records
   * @return $collection collection of objects that belonging to a given parent object
   */
 	public function get($object) {
        if (!($uids = $object->get($this->forgein_key))) {
			return NULL;
		}
		$query = new tx_auxo_query();
		$query->addWhere($this->forgein_key, $uids, TX_AUXO_QUERY::IN);
		if ($this->conditions) {
			$query = $query->merge($this->conditions);
		}
	    $has_many = call_user_func(array($this->model, $this->selectMethod), $query);
	    $object->setInternal($this->forgein_table, $has_many);
	    return $has_many;
	}		


  /**
   * Sets/overwrite or adds a new object to a collection
   *
   * @param object $parent parent object 
   * @param object $object child object that should be set/added
   * @return void
   */
	public function set($parent, $object) {
		// get collection of parent object
		if (!is_object($collection = $parent->get($this->forgein_table))) {
			$collection = new tx_lib_object();
		}
		// update/add collection with object
		$collection->set($object->uid(), $object);
		$parent->setInternal($this->forgein_table, $collection);
	}		
	
	
  /**
   * Validates all objects of an collection according to defined business logic set
   * and validators.
   *
   * @param object $object object that keeps an collection of objects
   * @return boolean $valid returns true if all object and data are valid
   */
	public function isValid($object) {
	   $valid = true;
	   $collection = $object->get($this->forgein_table);
	   if (is_object($collection)) {
	   	   for($collection->rewind(); $collection->valid(); $collection->next()) {
	   	   	  $instance = $collection->current();
			  if (!$instance instanceof tx_auxo_modelbase) {
			      throw new tx_auxo_exception('Objects are excepted in collections');	
			  }	   	  
			  $valid = $instance->isValid() && $valid;
		   }
	   }

		return $valid;		
	}
	
	
	
  /**
   * Saves all objects of an collection and collects its id which are store
   * in field "forgein_key" of the parent object.
   *
   * @param mixed $object object of class tx_auxo_modelbase
   * @return boolean $saved true if saved successfully
   */
 	public function save($object) {
	   $saved = true;
	   $uids = array();
	   
	   $collection = $object->get($this->forgein_table);
	   if (is_object($collection)) {
	   	   for($collection->rewind(); $collection->valid(); $collection->next()) {
	   	   	  $instance = $collection->current();
			  $saved = $instance->save() && $saved;	
			  $uids[] = $instance->id();
		   }
	   }
	   
	   $object->setInternal($this->forgein_key, implode(',',$uids));
	   return $saved;		
	}

  /**
   * Either deletes all depending objects or releases relation between 
   * those objects. Behaviour is set by using parameter 'delete' or 'release' 
   * in the schema table definition.
   *
   * @param object $object object of class tx_auxo_modelbase
   * @return boolean $deleted returns true if objects where delete/released
   */
	public function delete($object) {
	   	if (!$collection = $object->get($this->forgein_table)) {
			return false;
		}
				
		for($collection->rewind(); $collection->valid(); $collection->next()) {
			switch($this->dependent) {
			   	case 'delete':
					$instance->delete();
					break;
				case 'release':
					// depending objects will be just saved if any changes 
					// has been made in the meantime. No other actions are needed
					// because object's unique keys are kept in a field of the
					// parent object.
					$instance->save();
					break;
			}
		}
	}


  /**
   * Usally, this method returns an join clause which allows eager loading of 
   * all objects that belongs to. 
   *
   * @return string $clause returns an sql join partial 
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationHasManyInline.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationHasManyInline.php']);
}
?>