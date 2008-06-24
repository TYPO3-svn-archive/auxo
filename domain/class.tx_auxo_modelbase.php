<?php
/*
 * @package auxo
 * @subpackage models
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
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
 * Modelbase
 *
 * This classes represents database objects/tables and offers general methods 
 * to manipulate data entries e.g. select, selectSingle, count, delete, set, get,
 * hide, unhide and save. 
 * It has to be extended for each object/table that should be supported and certain
 * class variables have to be set to do so. 
 * 
 * Functionality of this class is inspired by the propel project, ative record pattern 
 * and ruby on rails.
 *
 * <b>Features</b>
 *  - table object mapping
 *  - accessors for table fields
 *  - single table inheritance according to Martin Fowler
 *  - associations: belongs_to, has_many, has_one, has_and_belongs_to_many
 *  - inline forgein keys for has_many association
 *  - implements observer/observable pattern
 *  - plugin techique used for behaviours e.g. sortable, 
 *  - autocompletion for fields e.g. created_at
 *  - validation and business logic
 *
 * <b>Usage<b>
 * <code>
 * class tx_abc_models_persons extends tx_auxo_modelbase {
 *    public $table = 'tx_abc_persons';
 *    public $caching = true;
 *    public $autofields = array ('created_at', 'pid');
 *	  public $unique_key = 'uid';
 * }
 * 
 * // add a new record
 * $entry = new tx_abc_models_people();
 * $entry->set('lastname', 'Meyer');
 * $entry->set('firstname', 'Paul');
 * 
 * or use accessors
 *
 * $entry->lastname = 'Meyer';
 * $entry->firstname = 'Paul';
 *
 * or for new objects create it an just one step
 *
 * $entry = new tx_abc_models_peope(array('lastname' => 'Meyer', 'firstname' => 'Paul'));
 * 
 * $entry->save();
 * ...
 *
 * // select a record
 * $query = tx_auxo_query( );
 * $query->addWhere('lastname', 'M%', tx_auxo_query::LIKE);
 * $query->addWhere('age', 35, tx_auxo_query::GREATER);
 * $persons = tx_abc_models_persons::select($query);
 *
 * // loop at results 
 * while($persons->valid()) {
 *   $person = $persons->current();
 *   echo $person->get('lastname');
 *   // or use accessors
 *   echo $person->firstname;
 *   $persons->next();
 * }
 * ...
 *
 * // delete a record
 * $person = tx_abc_models_persons::selectSingle($id);
 * $person->delete();
 *
 * // modify a record
 * $person = tx_abc_models_persons::selectSingle($id);
 * $person->firstname = 'Theo';
 * $person->save();
 * </code>
 *
 * @package auxo
 * @subpackage models 
 * @author Andreas Horn
 * @copyright 2007
 * @version $WCREV$
 * @access public
 */
class tx_auxo_modelbase extends tx_lib_object implements tx_auxo_observable, tx_auxo_extendable {
	 /** events that might be observed by observer classes **/
	 const EVENT_BEFORE_DELETE   = 1;
	 const EVENT_AFTER_DELETE  	 = 2;
	 const EVENT_BEFORE_SAVE 	 = 3;
	 const EVENT_AFTER_SAVE		 = 4;
	 const EVENT_HIDDEN   		 = 5;
	 const EVENT_UNHIDDEN 		 = 6;
	 const EVENT_MODIFIED        = 7;
	 const EVENT_BEFORE_VALIDATE = 8;
	 
	 /** default unique key used if no user specific has been defined **/
	 const DEFAULT_LIMIT         = '500';

	 public $fieldnames = array();
	 public $autofields = array('pid', 'tstamp', 'sys_language_id', 'crdate', 'cr_userid');

	 public	$businessLogic = NULL;
	 public	$errors = array();
	 public	$storageFolder = 0;
	 public $language;
	 	 
	 /** private section **/
	 private $cacheID = NULL;
	 private $cached = false;
	 private $existence = false;
	 private $modified = false;
	 private $saved = false;
	 private $deleted = false;	 
	 private $schema = NULL;
	 private $relations = NULL;
	 private $listener = array();
	 private $modifiedFields = array();
	 private $uid;
	 
	 private $_table;
	 private $_primaryKey;
	 private $_type;
	  
 /**
   * tx_auxo_modelbase::__construct()
   *
   * @return void
   */
	 public function __construct($classname, $parameter1=NULL, $parameter2=NULL) {
		parent::__construct($parameter1, $parameter2);
		// determine language Id in order to enable language related
		// data records
		$this->language = $this->getLanguageId( );

		// obtain class static parameters		
		$this->_table = tx_auxo_inspector::requireProperty($classname, 'table');
		$this->_type  = tx_auxo_inspector::getPropertyIfexist($classname, 'type');
		
		// We also allow single table inheritance by using an type
		// column in the database according to Martin Fowlers Pattern. 
		// This field is filled according to object classname.
		if (isset($this->_type)) {
			$this->autofields = array_merge($this->autofields, array('type'));
		}

		// get current schema
		if ((!$this->schema = tx_auxo_schemabase::getCurrentSchema())) {
			throw new tx_auxo_exception('no schema defined');
		}

		// get primary key 
		$this->_primaryKey = $this->schema->getUniqueKey($this->_table);
	
		// create a short cut
		$this->relations = $this->schema->relations[$this->_table];
	}
	
 /**
   * tx_auxo_modelbase::select()
   *
   * Select records belonging to this model and returns them
   * as a list of objects or as object if only one object has been required. 
   * One might using it as following:
   * 
   * tx_abc_models_address::select(1)  			 selection record with number 1
   * tx_abc_models_address::select(array(1,2,3)) selects three record according to its unique key
   * tx_abc_models_address::select('1,2,3')      selects also three records 
   * tx_abc_models_address::select($query)       selects records using a query object
   *
   * A selection could be limited by given an maximum number of records. Limit has 
   * to be either an array with a limit and an optional startpoint (start, limit) 
   * or just an integer value which defineds the limit. 
   * NOTE: Criterias are quoted automatically.
   *
   * @param     string  $classname name of model class 
   * @param 	mixed	either a $query object tx_auxo_query or record id (uid)
   * @param 	mixed   $limit either a array like (min, max) or just a integer value
   * @return	object  $result list of objects based on tx_lib_object or model's object
   */
	public static function _select($classname, $parameter=NULL, $limit=NULL) {
		// get current schema
		if ((!$schema = tx_auxo_schemabase::getCurrentSchema())) {
			throw new tx_auxo_domain_exception('no schema defined');
		}
		
	 	// handle parameters
	 	$query = self::buildQueryByParameter($classname, $parameter, $limit);

		// obtain settings		
		$caching = tx_auxo_inspector::getPropertyOrDefault($classname, 'caching', true) AND $query->caching;
		$table = tx_auxo_inspector::requireProperty($classname, 'table');
		$type = tx_auxo_inspector::getPropertyIfExist($classname, 'type');		

		// single table inheritance
		if ($type) {
			$query->AddWhere($type, $classname);
		}
				
		// build sql query
		if (!$statement = $query->build($table)) {
			return false;
		}		
		
		// already available in cache?
		if ($caching) {
			if (tx_auxo_cache::has($query->cacheID())) {
				return tx_auxo_cache::get($query->cacheID());
			} 
		}
		
		// execute sql query
        $handle = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $statement);
        
        // build result set
        while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($handle)){
		    tx_auxo_debug::dumpIfEnabled($row, TX_AUXO_DEBUG::SQL, 'Select');

		    if ($query->isSingle) {
			    $result = self::mapDataFieldsToObjects($query, $row);
			    $result->setExistence(true);
			    
			    if ($caching) {
					$result->setCaching($query->cacheID());
				}
			    break;
			}
			if (!isset($result)) {
				$result = new tx_lib_object();
			}
			
			$object = self::mapDataFieldsToObjects($query, $row);
			
			$object->setExistence(true);
			
			if ($caching) {
				$object->setCaching($query->cacheID());
			}
            $result->append($object);			 
        }
        
        if (!isset($result)) {
			return NULL;
		}
		
		// add results to cache if required
		if ($query->caching) {
			tx_auxo_cache::add($query->cacheID(), $result);
		}
		
        $result->rewind();
        return $result;
 	}
 	
 /**
   * tx_auxo_modelbase::selectCount()
   *
   * Counts and returns number of records based on criterias defined with 
   * a query object given for this model. One might use this method without 
   * using query objects then its returns the total number records.
   * 
   * @param 	object 	$query query object tx_auxo_query
   * @return	integer	$count number of records
   */
	public static function _selectCount($classname, $query=NULL) {
			 	
		if(!isset($query)) {	
			$request = new tx_auxo_query();
		}
		else {
			$request = clone $query;
		}
		
		$table = tx_auxo_inspector::requireProperty($classname, 'table');
		
		$request->addColumn('*', tx_auxo_query::COUNT, 'COUNT');
		
        // build sql statement
		if(!$statement = $request->build($table)) {
			return false;
		}			

        $handle = $GLOBALS['TYPO3_DB']->sql(TYPO3_db, $statement);
        
        // fetch count result and return it
        if (($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($handle))) {
		    tx_auxo_debug::dumpIfEnabled($row, TX_AUXO_DEBUG::MODELS);
			return intval($row['COUNT']);
		};
            
        return 0;
 	}
 	
  /**
   * tx_auxo_modelbase::selectSingle()
   *
   * Select only a single record based on a given query object. It returns
   * either an object of this model or NULL. 
   *
   * @param 	object	$query
   * @return	object 	$object
   */
	public static function _selectSingle($classname, $query) {
		return self::_select($classname, $query, 1);	
	}

  /**
   * tx_auxo_modelbase::mapDataFieldsToObjects()
   *
   * @param  array  $row
   * @return object $instance of this class
   */
	private static function mapDataFieldsToObjects($query, $row) {
		// check for schema
		if (!$query->getSchema()) {
			throw new tx_auxo_domain_exception('no schema defined');
		}
		
		// determine class name and make an instance	
		if (!($instance = $query->getSchema()->makeModelInstance($query->table))) {
			throw new tx_auxo_domain_exception('can not create model object for table');
		}

		$collections = array();
		
		/*  map main data fields and collect fields of
		 *  proxy objects in $collections.
		 */
		foreach($row as $key => $value) {
           if (strpos($key,'.') === true) {
  		       $names = explode($key, '.');			
		   }
		   else {
			   $names = array($query->table, $key);	
		   }
		   if ($names[0] == $query->table) {
		       $instance->set($key, $value, false);
		   }
		   else {
		       $collections[$names[0]] = array($names[1], $value);	
		   }
		}
	
		/* create based on $collections proxy objects and
		 * map these to the instance
		 */	
		foreach($collections as $collection => $values) {
		   $object = $query->schema->makeModelInstance($collection);
		   foreach($values as $key => $value) {
				$object->set($key, $value, false);	  
		   }
		   $instance->set($collection, $object);
		}
		return $instance;				
	}	 	
	
  /**
   * id
   *
   * Returns its primary key an is in fact a shortcut of ...->get($this->_primaryKey)
   * @return mixed $id primary key
   */
	 public function id() {
		return $this->get($this->_primaryKey);
	 }
	
  /**
   * tx_auxo_modelbase::__get()
   * This method enables to use a shortcut/comprehensive way to access values/members of this 
   * class. Usally, one has to use e.g. $this->get('firstName'). Now, one might also
   * use $this->getFirstName() instead of. Moreover, assoziated objects can be accessed 
   * via this method e.g. address = $people->address;
   * 
   * @param 	string $name		name of required field / object
   * @return 	mixed  $value 
   */
	 public function __get($name) {
		return $this->get($name);
	 }	 

  /**
   * tx_auxo_modelbase::get()
   *
   * @param 	string $fieldname
   * @return	mixed $object or value
   */
	 public function get($name) {
		if (isset($this->relations[$name])) {
			if (!parent::get($name) AND $this->relations[$name]->loading == tx_auxo_schemabase::LAZY) {
                return $this->relations[$name]->get($this);
			}
		}
	 	return parent::get($name);		
	}
	
 /**
   * tx_auxo_modelbase::__set()
   * This method enables to use a shortcut/comprehensive way to set values of class members. 
   * Usally one has to use e.g. $this->set('firstName', 'Miller'). Now, one might 
   * also use $this->firstName = 'Miller' instead of.
   * 
   * @param 	string $name	name of required attribute or object
   * @return 	mixed  $value   value or object reference
   */	 
	 public function __set($name, $value) {
		return $this->set($name, $value);
	 }
		
  /**
   * Set value of fieldname
   *
   * @param mixed $name fieldname 
   * @param mixed $value new value
   * @param boolean $track track changes in object 
   * @return void
   */
	 public function set($name, $value, $track=true) {
		 if (isset($this->relations[$name])) {
		 	/* assoziated objects of type "has_one" or "has_many" using a field (forgein_key) to 
		 	 * create an relationship to this model. Assoziation "belongs_to" means that 
		 	 * a field of this model needs to be updated.
			 * Therefore assoziated keys are also updated if an object is assigned.
		 	 */
			$this->relations[$name]->set($this, $value);
		}
		else {
			if (parent::get($name) <> $value && $track) {
				$this->triggerEvent(self::EVENT_MODIFIED);
				$this->modifiedFields[$name]++;
				$this->modified = true;
			}
		    parent::set($name, $value);	
	    }
	 }
	  
  /**
   * tx_auxo_modelbase::setInternal()
   *
   * @param mixed $name
   * @param mixed $object
   * @return void
   */
	 public function setInternal($name, $object) {
		parent::set($name, $object);
	}
	
  /**
   * tx_auxo_modelbase::isValid()
   *
   * Checks validity of the current record and returns true or false. Moreover,
   * if errors occured class variable $errors will be filled. This array consists
   * of fieldnames and detected errors like:
   * 
   * fieldname -> error  
   * etc.
   *
   * @return 	boolean	$success
   */
	 public function isValid() {
		$this->triggerEvent(self::EVENT_BEFORE_VALIDATE);
		
		if ($this->businessLogic AND $this->count() > 0) {
			if (!$this->businessLogic->validate($this)) {
				$this->errors = $this->getErrors();
				return false;
			}				
		}
		
		if ($this->relations) {
			return $this->triggerAssociationStrategy('isValid');
		}
		
		return true;
	}
	  
  /**
   * getValues
   *
   * Returns all data fields object associated to this table. Note associated objects are
   * excluded.
   *
   * @param  string $option 'all' = all available fields or 'modified' only new/changed fields
   * @return array  $values record values of this model table
   */
	 public function getValues($option='all') {
		$values = $this->getArrayCopy();
		foreach ($values as $key => $value) {
			if (is_object($value)) {
				unset($values[$key]);
			}			
			if ($option = 'modified' && !isset($this->modifiedFields[$key])) {
				unset($values[$key]);
			}
		}
		return $values;		
	}
	
  /**
   * tx_auxo_modelbase::save()
   *
   * This object is saved to database and its record created or updated. 
   * Before one can update a record it always has to be selected. Relationships
   * to other models are updated/created automatically based on schema 
   * definitions. Data will be validated automatically before any database
   * operation. In case of errors this method returns false and array $this->errors()
   * might be filled with detailed information.
   *
   * @return	boolean	$success
   */
	 public function save() {
		// check validity
		if (! $this->isValid()) {
			return false;
		}
		
		$this->triggerEvent(self::EVENT_BEFORE_SAVE);

		// extract allowed fields and set auto values for certain fields
		// insert or update record to database
		if ($this->isNew()) {
		    $values = $this->setAutoFields($this->getValues('all'));	
			$this->saved = $this->insertRecord($values);
			$this->setExistence(true);
		}
		else {
		    $values = $this->setAutoFields($this->getValues('modified'));	
		    $this->saved = $this->updateRecord($values);
		}

		// also update associated objects
		if ($this->saved && $this->relations) {
			$this->saved = $this->triggerAssociationStrategy('save');
		}

		// reset modification flag, clear cache and trigger event		
		if ($this->saved) { 
		    if ($this->isCached()) {
				tx_auxo_cache::remove($this->cacheID);
				$this->cached = false; 
				$this->cacheID = NULL;
			} 
			$this->modified = false;
			$this->modifiedFields = array();
			$this->triggerEvent(self::EVENT_AFTER_SAVE);
		}
		
		return $this->saved;
	} 
		   
  /**
   * tx_auxo_modelbase::delete()
   *
   * Deletes the current record of this object. Before one can delete
   * a record it has to be selected in beforehand. 
   *   
   * @return	boolean	$success
   */
	 public function delete() {
		// check wether a record has been loaded in this object
        if ($this->count() == 0) {
			return false;
		}
		
		// check whether a record is also present in the database
		if ($this->isNew() OR $this->isDeleted()) {
			return false;
		}

		$this->triggerEvent(self::EVENT_BEFORE_DELETE);

		// build sql statement to delete this record
		$query = new tx_auxo_query();
		$query->addWhere($this->_primaryKey, $this->id());
		$query->build($this->_table);
		
		// execute sql statement
		if (!$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->_table, $query->whereClause, $values)) {
	    	throw new tx_auxo_exception(sprintf('DELETE FROM TABLE %s failed', $this->_table));
	    }

		// remove from cache
	    if ($this->isCached()) {
			tx_auxo_cache::remove($this->cacheID);
			$this->cached = false; 
			$this->cacheID = NULL;
		} 
	    
		if ($this->relations){
			$this->triggerAssociationStrategy('delete');	    
		}
		
	    $this->deleted = true;
		$this->triggerEvent(self::EVENT_AFTER_DELETE);
	    return true;
	}	  
	 
  /**
   * tx_auxo_modelbase::is_new()
   *
   * @return	boolean returns true if new and unsaved otherwise false
   */
	public function isNew( ) {
		return $this->existence == false;
	}

  /**
   * tx_auxo_modelbase::isCached()
   *
   * @return
   */
	public function isCached() {
		return $this->cached;
	}
	
  /**
   * tx_auxo_modelbase::isModified()
   *
   * @return
   */
    public function isModified() {
		return $this->modified;
    }
   
  /**
   * tx_auxo_modelbase::is_saved()
   *
   * @return	boolean returns true if data have been saved otherwise false
   */
	public function isSaved( ) {
        return $this->saved;
 	}
		
  /**
   * tx_auxo_modelbase::isDeleted()
   *
   * @return	boolean returns true if data of this object has been deleted otherwise false
   */
	public function isDeleted( ) {
		return $this->deleted;
	}
 
  /**
   * Set caching
   *
   * @param string $id
   * @return void
   */
	public function setCaching($id) {
		$this->cached = true;
		$this->cacheID = $id;
	}

  /**
   * __call
   *
   * this method impements an plugin mechanism which is used to extend this class with
   * certain functionality. Here, it is used mainly to implement different kinds of behaviour 
   * strategies.
   *
   * @param string $method	method name
   * @param mixed $args		arguments as array
   * @return mixed $value	return value
   */
   function __call($method, $parameters) {
      return tx_auxo_extender::callPlugin($this, $method, true, $parameters);	
   }    
	 
  /**
   * tx_auxo_modelbase::insertRecord()
   *
   * Values of this record and insert a new one into the database. After a 
   * successful processing field "uid" will be filled
   * with the new record id.
   *
   * @param array $record array of fieldnames and values
   * @return boolean $success returns true if data has been updated
   */
   protected function insertRecord($record) {
		tx_auxo_debug::dumpIfEnabled($record, TX_AUXO_DEBUG::SQL, 'Insert');
		// insert record into database
		if (!$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->_table, $record)) {
			throw new tx_auxo_exception(sprintf('INSERT INTO TABLE %s failed', $this->_table));
		}
		
		// set primary record id but only when key has an inital value 
        if (! $this->has($this->_primaryKey)) {
		   $this->set($this->_primaryKey, $GLOBALS['TYPO3_DB']->sql_insert_id());
        }
		return true;
	}

  /**
   * tx_auxo_modelbase::updateRecord()
   *
   * Set record values of this record and insert a new one into the 
   * database. After a successful processing field "uid" will be filled
   * with the new record id.
   *
   * @param array $record	 array of fieldnames and values
   * @return boolean $success returns true if data has been updated
   */
   protected function updateRecord($record) {
		tx_auxo_debug::dumpIfEnabled($record, TX_AUXO_DEBUG::SQL, 'Update');
		// build sql statement to update this record
		$query = new tx_auxo_query();
		$query->addWhere($this->_primaryKey, $this->id());
		$query->build($this->_table);

		// execute sql statement
		if (!$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->_table, $query->whereClause, $record)) {
			throw new tx_auxo_exception(sprintf('UPDATE TABLE %s failed', $this->_table));
	    }

	    return true;
	}
	 
	/**
	 * Set State 'existence' means existing on database
	 * 
	 * @return volid
	 */
	public function setExistence($flag) {
		$this->existence = $flag;
	}
	 
  /**
   * tx_auxo_modelbase::hide()
   *
   * Hides the current record of this object and updates the database.
   * Before one can hide a record it has to be selected in beforehand.
   *
   * @return	boolean returns true if data have been saved otherwise false
   */
	public function hide() {
		$this->set('hidden', '1');
		$this->triggerEvent(self::EVENT_HIDDEN);
		return $this->save();
	}
	
  /**
   * tx_auxo_modelbase::unhide()
   *
   * Unhides the current record of this object and updates the database. Before one can 
   * unhide a record it has to be selected in beforehand.
   *
   * @return	boolean returns true if data have been saved otherwise false
   */
	public function unhide() {
		$this->set('hidden', '0');
		$this->triggerEvent(self::EVENT_UNHIDDEN);
		return $this->save();
	}
	
  /**
   * tx_auxo_modelbase::uid()
   *
   * Returns an unique id for this object wether it is already existing
   * in the database or not. This unique ID is used in relationships and collections
   * to identify an object.
   *
   * @return $id unique id of this object
   */
	public function uid() {
		if ($this->isNew()) {
			$this->uid = uniqid(rand());
			return $this->uid;
		}
		return $this->id();
	}

  /**
   * tx_auxo_modelbase::addListener()
   *
   * @param mixed $listener
   * @param mixed $event
   * @return void
   */
	public function addListener($listener, $event) {
		$this->listener[$event][md5(serialize($listener))] = $listener;
	}	
	
  /**
   * tx_auxo_modelbase::removeListener()
   *
   * @param mixed $listener
   * @return void
   */
	public function removeListener($listener) {
		unset($this->listener[$event][md5(serialize($listener))]);		
	}
	
  /**
   * tx_auxo_modelbase::triggerEvent()
   *
   * @param mixed $event
   * @return
   */
	public function triggerEvent($event) {
		if (!$listeners = $this->listener[$event]) {
			return;
		}
		foreach($listeners as $listener) {
			$listener->listen($event, $this);
		}	
	}
	
  /**
   * tx_auxo_modelbase::setAutoFields()
   *
   * this methods sets field values for certain auto fields. These are fields which should not
   * not be set manually e.g. date of creation, user id, etc. Currently, following fields are
   * support: pid, crdate, cruser_id, sys_language_id. A list of auto fields can be set using
   * class array variable autofields e.g. $this->autofields = array('pid', 'crdate');
   *
   * @param 	array $record input values
   * @return 	array $values input values and addtional filled fields
   */
	public function setAutoFields($record) {
		// fill those fields only at insert actions detected by uid is zero	
		if ($this->isNew()) {
			foreach ($this->autofields as $fieldname) {
				switch ($fieldname) {
					// used for single table inheritance
					case 'type':
						$record['type'] = $this->type;
						break;
					case 'pid':
					   	$record['pid'] = $this->storageFolder;
					   	break;
					case 'crdate':
					   	$record['crdate'] = time();
					   	break;
					case 'cruser_id':
						$record['cruser_id'] = $GLOBALS['TSFE']->fe_user->user_id['uid'];
					   	break;
					case 'sys_language_id':
						$record['sys_language_id'] = $this->languageId;
					   	break;
					others;
				}
			}
		}
		
		// here handled fields which are always filled 
		if (in_array('tstamp', $this->autofields)) {
		   $record['tstamp'] = time();
		}		
		
		return $record;
	}
	
  /**
   * tx_auxo_modelbase::getLanguageId()
   *
   * @return string $language current choosen language
   */
	private function getLanguageId( ) {
		//TODO:: might be removed because of transfer to tx_auxo_request
		$language = '';
		
        if(TYPO3_MODE == 'FE') {
            $language = $GLOBALS['TSFE']->config['config']['language'];
            if(empty($language))
                 $language = $GLOBALS['TSFE']->config['config']['language_alt'];
        } else {
            $language = $GLOBALS['LANG']->lang;
    	}	
		return $language;	
	}	  

  /**
   * tx_auxo_modelbase::buildQueryByParameter()
   *
   * Returns an query object based on given parameters and limits.
   *
   * @param string  $classname
   * @param mixed   $parameter
   * @param mixed   $limit
   * @return object $query
   */
	private function buildQueryByParameter($classname, $parameter, $limit=NULL) {	 	
	 	if (isset($parameter)) {
			   $primaryKey = tx_auxo_inspector::getPropertyOrDefault($classname, tx_auxo_schemabase::CLASS_PRIMARY_KEY, tx_auxo_schemabase::DEFAULT_PRIMARY_KEY);
	           
			   if (is_int($parameter) or is_numeric($parameter)) {
			       $query = new tx_auxo_query();
			       $limit = 1;
	 		       $query->addWhere($primaryKey, $parameter);
				}
				elseif (is_array($parameter)) {			   
				   $query = new tx_auxo_query();
				   $query->addWhere($primaryKey, $parameter, tx_auxo_query::IN);
				} 
				elseif (is_string($parameter)) {
				   $values = explode(',', $parameter);
				   $query = new tx_auxo_query();
				   if (is_array($values) AND count($values) > 0) {
				   		$query->addWhere($primaryKey, $values, tx_auxo_query::IN);
				   }
				} 
				elseif (is_object($parameter) AND $parameter instanceof tx_auxo_query) {
					$query = $parameter;
				}
				else {
					throw new tx_auxo_exception('select with unsupported parameter type');
				}
		}
		else {
			$query = new tx_auxo_query();
		}
		
		// set default limit if required
		if (!isset($limit) && !$query->hasLimit()) {
			$limit = array(0, tx_auxo_inspector::getPropertyOrDefault($classname, 'limit', self::DEFAULT_LIMIT));
		}
        if ($limit) { 
			$query->limit($limit);
		}
		
		return $query;		
	}	
		
  /**
   * tx_auxo_modelbase::triggerAssociationStrategy()
   *
   * @param string $action
   * @return boolean $success is set true if action has been executed successfully for all assosications
   */
 	private function triggerAssociationStrategy($action) {
		$success = true;
		// handle assoziations
		foreach($this->relations as $relation) {
			$retval = $relation->{$action}($this);
			if (tx_auxo_debug::isEnabled(tx_auxo_debug::MODELS)) {
				printf('<p>%s:%s->%s -> %s</p>', $this->_table, $relation->forgein_table, $action, $retval);
			}
			$success = $retval && $success;
		}
		
		return $success;
	}		
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_modelbase.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_modelbase.php']);
}
?>