<?php
/**
 * @package auxo
 * @subpackage models
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

/**
 * This class represents a relationship of type "has and belongs to many" or also known as N:M. 
 * It offers all needed methods to get, set, validate, etc. such relationships between 
 * objects.
 *
 * options which are used in this relationship are:
 *
 * - forgein_table 
 * - forgein_key
 * - joinTable
 * - joinKey
 * - conditions
 *
 * @package 	auxo
 * @subpackage 	models
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 * @access 		public
 */
class tx_auxo_relationHasAndBelongsToMany extends tx_auxo_relation implements tx_auxo_associative {
  /**
   * tx_auxo_relationHasAndBelongsToMany::__construct()
   *
   * @param object $schema
   * @param string $table
   * @param string $forgein_table
   * @param array  $options
   * @return void
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		parent::__construct($schema, $table, $forgein_table, $options);
		// set and register join table
		$this->setOption('joinTable', $options, $this->getDefaultJoinTable($table, $forgein_table));
		$this->schema->addTable($this->joinTable, NULL, 'tx_auxo_modelbase');
		
		// set additional parameters
		$this->setOption('joinKey', $options, tx_auxo_schemabase::getForgeinKey($forgein_table));
		$this->setOption('forgein_key', $options, tx_auxo_schemabase::getForgeinKey($table));
		$this->setOption('selectMethod', $options, 'select');
		
		if (isset($options['conditions'])) {
			if ($options['conditions'] instanceof tx_auxo_query) {
				throw new tx_auxo_exception('Relationship %s -> %s has an invalid condition', $table, $forgein_table);
			}
		} 
	}


  /**
   * tx_auxo_relationHasAndBelongsToMany::getDefaultJoinTable()
   *
   * @param  string $table
   * @param  string $forgein_table
   * @return string $joinTable
   */
	function getDefaultJoinTable($table, $forgein_table) {
		$tablenames[] = tx_auxo_schemabase::getStrippedTableName($table);
		$tablenames[] = tx_auxo_schemabase::getStrippedTableName($forgein_table);
		sort($tablenames);
		return $this->schema->extension . '_' . implode('_', $tablenames);
	}
	
	
  /**
   * tx_auxo_relationBelongsTo::get()
   *
   * @param   object $object
   * @return  object $objects
   */
 	public function get($object) {
        if (($uid = $object->id()) == 0) {
		    // not saved yet!
			return NULL;
		}
		
		// determine object ID's
        if (!($keys = $this->getForgeinObjects($uid))) {
			return NULL;
		}
		
		// build query
		$query = new tx_auxo_query();
		$query->addWhere($this->schema->getUniqueKey($this->forgein_table), $keys, TX_AUXO_QUERY::IN);
		if (isset($this->conditions)) {
			$query = $query->merge($this->conditions);
		}
			
		// fetch all determined objects by ID
	    $hasMany = call_user_func(array($this->model, $this->selectMethod), $query);
	    $object->setInternal($this->forgein_table, $has_many);
	    $object->setInternal($this->joinKey, new tx_lib_object($keys));
		return $hasMany;
	}		

  
  /**
   * tx_auxo_relationHasAndBelongsToMany::set()
   *
   * @param mixed $object
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
   * isValid
   *
   * @param mixed $collection
   * @return
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
   * tx_auxo_relationHasAndBelongsToMany::save()
   *
   * @param mixed $object
   * @return
   */
	public function save($object) {
		if (!is_object($collection = $object->get($this->forgein_table))) {
			return true;
		}
        
		$keys = $object->get($this->JoinKey);
		$saved = true;
	    $records = array();
	    
		// check for new objects in collect and save them
 		$collection->rewind();
 		while($collection->valid()) {
 			if ($collection->current()->isNew()) {
				$saved = $collection->current()->save() AND $saved;
			}
 			
 			if (!$keys) {
				$records[] = array( $this->forgein_key => $object->id(), $this->joinKey => $id);			
			}
			elseif (!$id = $keys->get($collection->current()->id())) {
				$records[] = array( $this->forgein_key => $object->id(), $this->joinKey => $id);			
			}
			$collection->next();
		}
		
		foreach($records as $record) {
			// insert record into database
			$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery($this->joinTable, $record);
			if ($result <> 1) {
				$saved = false;
			}			
		}
		// decide wich records has to saved to the join table
  	    return $saved;
 	}

	
  /**
   * tx_auxo_relationBelongsTo::delete()
   *
   * @param mixed $object
   * @return
   */
	public function delete($object) {
		if (!is_object($collection = $object->get($this->forgein_table))) {
			return NULL;
		}
        
		$deleted = true;
	    
		// check for new objects in collect and save them
 		$collection->rewind();
 		while($collection->valid()) {
 			if (! $collection->current()->isNew()) {
				$deleted = $collection->current()->delete() AND $deleted;
			}
			$collection->next();
		}
		
		$query = new tx_auxo_query();
		$query->addWhere($this->forgein_key, $object->id());
		$query->build($this->joinTable);
		// delete records from database
		$result = $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->joinTable, $query->whereClause);
		if ($result <> 1) {
			$delete = false;
		}			
		// nothing to do here
		return $deleted;
	}
	
  /**
   *
   * @param integer	$uid
   * @return array $keys for selection of data in forgein table
   */
	private function getForgeinObjects($uid) {		 
		// fetch all corresponding records of join table
		$query = new tx_auxo_query(); 	
		$query->addWhere($this->forgein_key, $uid);
		$query->addColumn($this->joinKey);
		if (!$statement = $query->build($this->joinTable)) {
			return NULL;
		}
		
		$handle = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $statement);
        
        // build result set
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($handle)){
            $joinKeys[] = $row[$this->joinKey];
        }
        return $joinKeys;
	}
	
  /**
   * tx_auxo_relationHasAndBelongsToMany::getJoinClause()
   *
   * Returns an sql partial to join table A with B using a join table to implement M:N
   * relations.
   *
   * @return string $clause SQL Partial with LEFT OUTER JOIN statements
   */
	public function getJoinClause() {
	    /* table A and B unique keys 'uid' (default) points to an 
		 * M:M table C with their forgein_keys <table>_id. This SQL statements
		 * joins those tables to get all relvant records.
		 */ 
		$clause = sprintf(' LEFT OUTER JOIN %s ON %s.%s = %s.%s ', 
							$this->table,
							$this->table, 
							$this->unique_key,
		                  	$this->join_table, 
		                  	$this->forgein_key
				  );
							  	
		$clause.= sprintf(' LEFT OUTER JOIN %s ON %s.%s = %s.%s ', 
							$this->joinTable,
							$this->joinTable,
							$this->joinKey, 
		                  	$this->forgein_table, 
		                  	$schema->getUniqueKey($this->forgein_table)
				  );
		return $clause;

	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationHasAndBelongsToMany.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mvext/class.tx_auxo_relationHasAndBelongsToMany.php']);
}
?>