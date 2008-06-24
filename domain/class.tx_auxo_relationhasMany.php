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
 * Relation Has Many
 *
 * @package auxo
 * @subpackage models
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */
class tx_auxo_relationHasMany extends tx_auxo_relation implements tx_auxo_associative {

  /**
   * tx_auxo_relationHasMany::__construct()
   *
   * @param object $schema
   * @param string $table
   * @param string $forgein_table
   * @param array  $options
   * @return void
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		parent::__construct($schema, $table, $forgein_table, $options);
		$this->setOption('forgein_key', $options, $table.'_id');
		$this->setOption('selectMethod', $options, 'select');
		if (isset($options['conditions'])) {
			if ($options['conditions'] instanceof tx_auxo_query) {
				throw new tx_auxo_exception('Relationship %s -> %s has an invalid condition', $table, $forgein_table);
			}
		} 
	}
	
  /**
   * tx_auxo_relationHasMany::get()
   *
   * @param mixed $name
   * @return void
   */
 	public function get($object) {
        $uid = $object->get($this->schema->getUniqueKey($this->table));
		$query = new tx_auxo_query();
		$query->addWhere($this->forgein_key, $uid);
		if (isset($this->conditions)) {
			$query = $query->merge($this->conditions);
		}
	    $has_many = call_user_func(array($this->model, $this->selectMethod), $query);
	    $object->setInternal($this->forgein_table, $has_many);
	    return $has_many;
	}		


  /**
   * tx_auxo_relationHasMany::set()
   *
   * @param mixed $parent
   * @param mixed $object
   * @return void
   */
	public function set($parent, $object) {
		// update forgein key in source object
		$object->set($this->forgein_key, $parent->id());
		// get collection of parent object
		if (!is_object($collection = $parent->get($this->forgein_table))) {
			$collection = new tx_lib_object();
		}
		// update/add collection with object
		$collection->set($object->uid(), $object);
		$parent->setInternal($this->forgein_table, $collection);
	}		
	
	
  /**
   * tx_auxo_relationHasMany::isValid()
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
   * tx_auxo_relationHasMany::save()
   *
   * @param mixed $uid
   * @param mixed $collection
   * @return
   */
	public function save($object) {
	   $saved = true;
	   $collection = $object->get($this->forgein_table);
	   if (is_object($collection)) {
	   	   for($collection->rewind(); $collection->valid(); $collection->next()) {
	   	   	  $instance = $collection->current();
		   	  $instance->set($this->forgein_key, $object->get($this->unique_key));
			  $saved = $instance->save() && $saved;	
		   }
	   }

	   return $saved;		
	}

	
  /**
   * tx_auxo_relationHasMany::delete()
   *
   * @param mixed $collection
   * @return void
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
					$instance->set($this->forgein_key, 0);
					$instance->save();
					break;
			}
		}
	}

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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relationHasMany.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mvext/class.tx_auxo_relationHasMany.php']);
}
?>