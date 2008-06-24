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
 * Schemabase
 *
 * This class represents an database schema and could also be understand as 
 * object domain. It is used to defined all table presentations and 
 * its associations for a certain application. 
 *
 * @package 	auxo
 * @subpackage	models
 * @author 		Andreas Horn
 * @access 		public
 */
class tx_auxo_schemabase extends tx_lib_object {
	 
	 /** public section **/
	 const DEFAULT_PRIMARY_KEY = 'uid';	 			// default name for table's primary key
	 
	 const CLASS_TABLENAME     = 'table';			// variable name within models for table name
	 const CLASS_PRIMARY_KEY   = 'primaryKey'; 		// variable name within models for primary key
	 
	 const HAS_ONE = 1;
	 const HAS_MANY = 2;
	 const BELONGS_TO = 3;
	 const HAS_AND_BELONGS_TO_MANY = 4;
	 
	 const EAGER = 1;
	 const LAZY  = 2;

	 public $reflections = array();			// reflection of model clases
	 public $relations = array();           // association between tables
	 public $tables = array(); 			// mapped tables with additonal parameters	 
	 public $plugins = array();				// plugins per table
	 
	 /** private section **/
	 private static $schemas = array();     // schema singetons
	 private static $current = NULL;        // current schema 
		
	
  /**
   * tx_auxo_schemabase::getInstance()
   *
   * Returns a singleton of an schema object which keeps information about all tables 
   * and models of an application.
   *
   * @return
   */
	public static function getInstance($extension, $schema) {
		$key = $extension.$schema;
		if (self::$schemas[$key] == NULL) {
			$classname = 'tx_' . $extension . '_schema_' . $schema;
			self::$schemas[$key] = new $classname;
		}
		
		self::$current = self::$schemas[$key];
		return self::$current;
	}

  /**
   * tx_auxo_schemabase::getCurrentSchema()
   *
   * Returns current schema object which has been created at last
   *
   * @return object	current schema 
   * @throws tx_auxo_exception if no current schema has been set
   */
	public static function getCurrentSchema() {
		if (self::$current == NULL) {
			throw new tx_auxo_exception('no current schema available');
		}
		return self::$current;
	}
		
		
  /**
   * tx_auxo_schemabase::getExtensionSchema()
   *
   * @param  string $extension name of an extensions
   * @return object $schema schema object
   * @throws tx_axuo_exception
   */
	public static function getExtensionSchema($extension) {
		if (!isset(self::$schemas[$extension])) {
			throw new tx_auxo_exception('schema for extension '.$extension.' not instiated');
		}
		return self::$schemas[$extension];
	}
	
	
  /**
   * tx_auxo_modelbase::getModelClassName()
   *
   * This method guesses model's class name based on its table name. Following rules are 
   * considered:
   * @example
   * tx_extensiontablename  -> tx_extension_models_tablename
   * tx_extension_tablename -> tx_extension_models_tablename
   * fe_users               -> models_fe_users
   *
   * Table is defined in extension 'news' but should be used with a model of 'abc' 
   * 
   * getModelClassName('tx_news_articles', 'tx_abc') -> tx_abc_models_articles
   *
   * @param  string $table table name
   * @param  string $prefix optional parameter to set an different target extension
   * @return string $classname models class name
   */
	public static function getModelClassName($table, $prefix=NULL) {
		if (!$prefix) {
			$extension = self::getExtensionByTable($table);
		}
		else {
			$extension = $prefix;
		}	
		
		if (!$extension) {
			throw new tx_auxo_exception('Extension could not be determined');
		}

		return $extension.'_models_'.self::getStrippedTableName($table, $extension);
	}

	
  /**
   * Returns a default name for forgein keys based on a table name
   *
   * @param string $table name of a database table
   * @return string $forgeinKey name of forgein key
   */
   public static function getForgeinKey($table) {
		return $table.'_id';
   }
   

  /**
   * tx_auxo_schemabase::getExtensionByTable()
   *
   * @param mixed $table
   * @return
   */
	public static function getExtensionByTable($table) {
		if(preg_match('/^tx_([^_]+)/', $table, $matches) ||
		   preg_match('/^user_([^_]+)/', $table, $matches)) {
			$candidate = $matches[1];
			if($candidate != 'lib') {
				$keys = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']);
				foreach($keys as $key) {
					if($candidate == str_replace('_', '', $key)) {
						$prefix = 'tx_'.str_replace('_', '', $key);
						// extract extension prefix form table name
						if (strncmp($table, $prefix, strlen($prefix)) == 0) {
							return $prefix;
						}
					}
				}
			}
		}
	}

  /**
   * tx_auxo_schemabase::getStrippedTableName()
   *
   * returns a given table name without its prefix
   *
   * @param mixed $table
   * @param mixed $extension
   * @return mixed $tablename without extension prefix
   */
	public static function getStrippedTableName($table, $extension='') {
		if (!$extension) {
			$extension = self::getExtensionByTable($table);
		}
		$name = substr($table, strlen($extension));
		if (strpos($name, '_') === 0) {
			$name = substr($name, 1);
		}
		
		return $name;		
	}	
	
  /**
   * getUniqueKey()
   *
   * @param  string $table
   * @return string $keyname name of primary key 
   */
	public function getUniqueKey($table) {
		if (!isset($this->tables[$table])) {
			throw new tx_auxo_exception('Table: '.$table.' not found in schema.');
		}
		
		$classname = $this->tables[$table]['classname'];
		return tx_auxo_inspector::getPropertyOrDefault($classname, 
		                                               self::CLASS_PRIMARY_KEY, 
													   self::DEFAULT_PRIMARY_KEY
													   );
	}
	
	
  /**
   * Defines a certain behaviour for a model class. Behaviours are plugins implements
   * as strategy pattern and are used in model base methods.
   *
   * @param string $table
   * @param string $behaviour
   * @param string $extension
   * @return void
   */
	public function behaveLike($table, $behaviour, $extension = '') {
		$className = self::getExtensionByTable($table).'_behaviour'.$behaviour;
		$this->plugins[$table][$behaviour]['className'] = $className; 
	}
	
	
 /**
   * tx_auxo_schemabase::makeModelInstance()
   *
   * @param  string $table
   * @return object	$instance belongs to this table (model)
   */
	public function makeModelInstance($table) {
	
		if (!$this->tables[$table]) {
			throw new tx_auxo_domain_exception('table: '.$table.' not defined in schema.');
		}
	
		return new $this->tables[$table]['classname'];
	}

 /**
   * Adds a table
   *
   * Adds a table to your schema defintion. If a model belongs to another extension one can use it by giving an
   * extension name. If you need a different name for your model class it can be defined using parameter
   * $className. Usually, a table and its model class is associated with extension defined in schema variable
   * self::$extension.
   *
   * @param  string $table
   * @param  string $extension
   * @param  string $className
   * @return void
   */
	public function addTable($table, $extension = NULL, $className=NULL) {
		if (isset($extension)) {
			if (!in_array($extension, t3lib_div::trimExplode(',', $GLOBALS['TYPO3_CONF_VARS']['EXT']['extList']))) {
				throw new tx_auxo_exception('Extension: '.$extension.' does not exist');
			}
		}
		if (!isset($extension)) {
			$extension = $this->extension;
		}
		if (!isset($className)) {
			$className = self::getModelClassName($table, $extension);
		}
		$this->reflections[$table] = new ReflectionClass($className);
		$this->tables[$table] = array('extension' => $this->extension, 'classname' => $className);
	}
	
	
  /**
   * Associate
   *
   * Creates an association between two tables or in case of has_and_belongs_to_many between 
   * three tables.
   *
   * Types of Relationships:
   * - has_one
   * - has_many
   * - belongs_to
   * - has_and_belongs_to_many
   *
   * <b>Options:</b>
   * - loading => ('eager', 'lazy')   
   *   Normally, tables are loaded lazy that means associated tables are only loaded
   *   if accessed. This is alright as long as small amounts of tables have to be
   *   handled but if not then each access would initiate and separate SQL statement 
   *   which might costs a lot. Eager loading means that only one left outer join 
   *   SQL statement is performed when creating a new object which is more effective 
   *   for many depending tables.
   *
   * - dependent => ('delete', 'nullify')
   *   Deleting is performed deeply and can be either <b>delete</b> all associated
   *   tables or just <b>nullify</b> its relationsships.
   *
   * - joinTable 
   *   M:N tablename by default composed of both tables in sorted order separated by 
   *   underscore. It can be overwritten using this option.
   *
   * - joinKey
   * - joinModel
   * - forgein_key
   * - inline
   *   Might be used for HAS_MANY relationships and signals that the forgein key 
   *   is not saved in a field in the associated table but as comma separated list in 
   *   a field within this table. 
   *
   * @param  string $table
   * @param  enum   $kind
   * @param  string $association
   * @param  array  $options
   * @return void
   */
	public function associate($table, $kind, $association, $options=array()) {
		if (!isset($this->tables[$table])) {
			throw new tx_auxo_exception('Table '.$table.' not defined in schema');
		}

		if (!isset($this->tables[$association])) {
			throw new tx_auxo_exception('Table '.$association.' not defined in schema');
		}
					
        $defaults = array( 'loading' => self::LAZY );
		$options  = array_merge($defaults, $options); 
		$relation = tx_auxo_relation::getInstance($kind, $this, $table, $association, $options);		
		$this->relations[$table][$association] = $relation;
	}		
 }

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_schemabase.php']){
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_schemabase.php']);
}
?>