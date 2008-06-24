<?php
/**
 * @package 	auxo
 * @subpackage 	models
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 	2007
 * @version 	$WCREV$
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
 * Query
 *
 * This class represents a SQL query with criterias that are used to obtain
 * records/objects from the database. It is an implementation of an query
 * pattern.
 *
 * @package 	auxo
 * @subpackage 	models
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 */
class tx_auxo_query {
	// modifier
	const DISTINCT        = 'DISTINCT';
	const SINGLE          = 'SINGLE';
	
	// operators
	const NOT_EQUAL       = '<>';
	const EQUAL 		  = '=';
	const GREATER         = '>';
	const GREATER_EQUAL   = '>=';
	const LESS			  = '<';
	const LESS_EQUAL      = '<='; 
	const IS_NULL         = 'IS NULL';
	const IS_NOT_NULL     = 'IS NOT NULL';
	const NOT_IN          = 'NOT IN';
	const IN              = 'IN';
	const LIKE            = 'LIKE';
	const NOT_LIKE        = 'NOT LIKE';
	const CUSTOM 	  	  = 'CUSTOM';
	
	// logical operators
	const BOOL_AND        = 'AND';
	const BOOL_OR         = 'OR';
	
	// joins
	const LEFT_JOIN       = 'LEFT';
	const RIGHT_JOIN      = 'RIGHT';
	const INNER_JOIN      = 'INNER'; 
	
	// sort orders 
	const DESC     	      = 'DESC';
	const ASC      	  	  = '';
	
	// column functions
	const MAX			  = 'MAX';
	const MIN			  = 'MIN';
	const AVG			  = 'AVG';
	const SUM			  = 'SUM';
	const COUNT           = 'COUNT';
	
	public  $schema  	  = NULL;
	public  $uniqueKey    = '';
	public	$table        = '';
	public	$isSingle  	  = false;
	public  $caching      = true;

	public  $columns      = array();
	public  $conditions   = array();
	public  $joins        = array();
	public  $sortOrder    = array();
	
	private $relations    = array();
	private $includes     = array();
	
	private $limitClause  = '';
	private $orderClause  = '';
	private $tableClause  = '';
	private $groupClause  = '';
	private $fieldClause  = '*';
	private $joinClause   = '';
	private $havingClause = '';
	
	private $tableAlias   = array();
	private $columnAlias  = array();
	private $modifiers    = array();
   
  /**
   * tx_auxo_query::__construct()
   *
   * @return void
   */
   public function __construct(){
		$this->schema = tx_auxo_schemabase::getCurrentSchema();	
   }
  
   /**
    * Returns schema used in this query
    *
    * @return object $schema schema
    */
   public function getSchema() {
   		return $this->schema;
   }
  
  /**
   * Merge
   *
   * Merges a query with this instance and returns a new merged query
   *
   * @param  object $query   query that should be merged with data of this instance
   * @return object $merged	 merged query
   */
  function merge($query) {
		$merged = tx_auxo_query();
		// merge selected columns
		$merged->columns = array_merge($this->columns, $query->columns);
	  	// merge conditions
		$merged->conditions = array_merge($this->conditions, $query->conditions);
		// merge sort order
		$merged->sortOrder = array_merge($this->sortOrder, $query->sortOrder);
		return $merged;
  } 
  
  
  /**
   * tx_auxo_query::limit()
   *
   * @param mixed $limit
   * @return void
   */
   public function limit($option) {
   		$start = 0;
   		$limit = 0;   		
   		
		if (is_array($option)) {
			if (count($option) == 1) {
				$limit = intval($option[0]);
			}
			else {
		 		$offset = $option[0];
		 		$limit  = $option[1];
			}
		} elseif (is_int($option)) {
			$limit = $option;
		}
		else {
			throw new tx_auxo_exception('invalid LIMIT option given');
		}
		
		if ($limit == 1) {
			$this->setModifier(self::SINGLE);
			$this->isSingle = true;
		} 
		
		$this->limitClause = sprintf('%d, %d', $offset, $limit);
   }
   
   
  /**
   * hasLimit
   *
   * @return boolean $hasLimit returns true if a limit has been set
   */
   public function hasLimit() {
		return strlen($this->limitClause) <> 0;
   }


  /**
   * setModifier
   *
   * Sets either SINGLE or DISTINCT als SELECT modifier.
   * 
   * @param string $modifier either tx_auxo_query::SINGLE or ...::DISTINCT
   * @return void  
   */
   public function setModifier($modifier) {
 		$this->modifiers[$modifier] = true;
 	}
		
  /**
   * addColumn
   *
   * Adds a specific column to this query. It is necessary to use "table.column"
   * syntax for fieldnames if this definition should be applied only 
   * to a specific table.
   *
   * @param mixed  $fieldname fieldname
   * @param string $function  supported functions are MAX, MIN, SUM, AVG, COUNT
   * @param string $alias	  fieldname alias if required
   * @return void
   */
   public function addColumn($fieldname, $function=NULL, $alias=NULL) {
   	    $this->buildColumn($fieldname, $function, $alias);
   }
      
  /**
   * addWhere
   *
   * Adds a condition to this query. It is necessary to use "table.column"
   * syntax for fieldnames if this condition should be applied only 
   * to a specific table.
   *
   * @param string $fieldname name of a column 
   * @param mixed  $value	  value for operation
   * @param string $operator  operators like e.g. EQUAL, GREATER, LESS, etc.
   * @param string $bool	  boolean operators like AND, OR, NOT AND, NOT OR, etc.
   * @return void
   */
	public function addWhere($column, $value, $operator = '=', $bool=TX_AUXO_QUERY::BOOL_AND) {
   	    //TODO: improve handling of operator IN/NOT_IN and given values
   	    if (is_array($value) AND in_array($operator, array(self::IN, self::NOT_IN))) {
			foreach ($value as $item) {
				$secureValues[] = $this->quoteString($item);
			}
			$quoteStr = '('.implode($secureValues, ',').')';
		}
		else {
   	    	$quoteStr = $this->quoteString($value);
   	    }
      	$this->conditions[$column] = array('operator' => $operator, 'value' => $quoteStr, 'bool' => $bool);
   }
   
  /**
   * addJoin
   *
   * Adds a join to a query. Columns has to be defined like table.column otherwise an
   * exception will be raised.
   *
   * @param  string $a column A fully specified
   * @param  string $b column B fully specified
   * @param  mixed  $type join types could be ...::LEFT_JOIN, RIGHT_JOIN or INNER_JOIN
   * @throws tx_auxo_exception Join is not fully specified
   * @return void
   */
	public function addJoin($a, $b, $type=TX_AUXO_QUERY::LEFT_JOIN) {
		if (strpos($a, '.') === false || strpos($b, '.') == false) {
			throw new tx_auxo_exception('Join %s = %s is not fully specified', $a, $b);
		}
		$this->joins[] = array($a, $b, $type);
	}
	
  /**
   * addSort
   *
   * Adds a sort order to this query. 
   *
   * @param string $fieldname 	name of a column
   * @param string $order		either ASC for ascending or DESC for descending order
   * @return void
   */
	public function addSort($fieldname, $order = '') {
      	$this->sortOrder[$fieldname] = $order;
    }   
   
  /**
   * build
   *
   * Builds and returns a valid SQL statement based on performed definitions.
   *
   * @param string 	$table default table
   * @param array	$options
   * @return string $statement
   */
	public function build($table, $options=array()) {
   		// set object variables
		$this->table = $table;
		$this->relations = $this->schema->relations[$table];

		if (isset($options['include']) AND is_array($options['include'])) {
			$this->includes = $options['include'];
		}
		
		if (!$this->columns) {
			$this->addColumn('*');
		}

		// build sql partials
   	    $this->fieldClause = $this->createFieldClause($table, $this->relations);
   	    $this->tableClause = $this->createTableClause($table, $this->relations);
   	    $this->joinClause  = $this->createJoinClause( );
   	    $this->whereClause = $this->createWhereClause( );
   	    $this->orderClause = $this->createOrderClause( );

   	    // build sql query
		$statement = $GLOBALS['TYPO3_DB']->SELECTquery(
				                 $this->fieldClause, 
								 $this->tableClause . $this->joinClause, 
								 $this->whereClause, 
								 $this->groupClause, 
								 $this->orderClause, 
								 $this->limitClause 
							);	

		// sanatize otherwise it is difficult to compare result in testcases
		$this->statement = trim(preg_replace('/[\t]/','', preg_replace('/[\r\n]/',' ',$statement)));
		// builtin debugging
		tx_auxo_debug::dumpIfEnabled($this->statement, tx_auxo_debug::SQL, 'Query');
		return $this->statement;
	}
	
  /**
   * Returns an md5 cache ID based on an generated sql statement which is used 
   * for caching of selection results
   *
   * @return string $cacheID cache ID of this current object
   */
	public function cacheID() {
		return md5(trim($this->statement));
	}
	
	
  /**
   * getTableAlias
   *
   * @param  mixed  $table
   * @return string $alias
   */
	public function getTableAlias($table) {
		if (!isset($this->schema->objects[$table])) {
			throw new tx_auxo_coreException('Table ' . $table . ' is not part of this schema');
		}
		return isset($this->tableAlias[$table]) ? $this->tableAlias[$table] : $table;
	}
	
	
  /**
   * __tostring
   *
   * Echos all build compontents
   *
   * @return void
   */
	public function __tostring() {
		printf('Fields:   %s\n', $this->fieldClause);
		printf('Tables:   %s\n', $this->tableClause);		
		printf('Joins:    %s\n', $this->joinClause);
		printf('Where:    %s\n', $this->whereClause);
		printf('Order:    %s\n', $this->orderClause);
		printf('Group by: %s\n', $this->groupClause);
		printf('Having:   %s\n', $this->havingClause);
		printf('Limit:    %s\n', $this->limitClause);
	}
	
  /**
   * createFieldClause
   *
   * @param string	$table
   * @param mixed 	$relations
   * @return string	$clause
   */
	private function createFieldClause($table, $relations) {
		$clause = '';
//TODO:needs to be reviewed. SINGLE doest work not properly with <table>.*
//		if (count($this->modifiers)) {
//			$clause.= implode(' ', array_keys($this->modifiers)).' ';
//		}	
		$sources[] = $this->createFieldList($table);
		if (isset($relations)) {
			foreach ($relations as $relation) {
				if ($relation->loading == tx_auxo_schemabase::EAGER OR 
				    in_array($relation->forgein_table, $this->includes)) {				
					$sources[] = $this->createFieldList($relation->forgein_table);
				}
			}
		}
		
		$clause.= implode(',', $sources);
		return $clause;
	}
	
  /**
   * createFieldList
   *
   * @param mixed $table
   * @param string $alias
   * @return string $clause
   */
	private function createFieldList($table, $alias=NULL) {		
		foreach($this->columns AS $key => $parameters) {
			$tablename = isset($parameters['table']) ? $parameters['table'] :  $table;
			$fieldname = $parameters['fieldname'];
			$function  = $parameters['function'];
			$column    = $key;

			if (isset($parameters['table'])) {
				if ($tablename <> $table AND $tablename <> '__TABLE__') {
					continue;
				}
			}

			$column = str_replace('__TABLE__', $table, $column);

			if (isset($alias)) {
				$column = str_replace($tablename, $alias, $column);
			}				
			 
			$list[] = $column;
		}
		
		return implode(',', $list);
	}


  /**
   * buildColumn
   *
   * @param mixed $fieldname
   * @param mixed $function
   * @return void
   */
	private function buildColumn($fieldname, $function, $alias) {
		if ($function == self::COUNT) {
			$column = '*';
		}
		else {
			if (strpos($fieldname, '.') === true) {
				list($prefix, $suffix) = explode('.', $fieldname);
			}
			else {
				$prefix = '__TABLE__';
				$suffix = $fieldname;
			}
	
			$column = $prefix . '.' . $suffix;	
		}

					
		if ($function) {
			$column = sprintf('%s(%s)', $function, $column);
		}

		
		if ($alias) {
			$column.= ' AS '. $alias;
			$this->columnAlias[$column] = $alias;
		}
		
		$this->columns[$column] = array('table' => $prefix, 'fieldname' => $suffix, 'function' => $function);
	}	

	
  /**
   * createTableClause
   *
   * @param  string $table
   * @return string $clause
   */
	private function createTableClause($table, $relations) {
		$clause = $table;		
		if (isset($relations)) {
			foreach ($relations as $relation) {
				if ($relation->loading == tx_auxo_schemabase::EAGER OR 
				    in_array($relation->forgein_table, $this->includes)) {				
					$clause.= $relation->getJoinClause(); 
				}
			}
		}
		return $clause;
	}
	
  /**
   * createJoinClause
   *
   * @return string $clause
   */
	private function createJoinClause() {
		$clause = '';
		foreach ($this->joins as $join) {
			list($table_a, $column_a) = explode('.', $join[0] );
			list($table_b, $column_b) = explode('.', $join[1] );			
			$clause.= sprintf(' LEFT OUTER JOIN %s AS %s ON %s.%s = %s.%s ', 
								$this->getTableAlias($table_a),
								$this->getTableAlias($table_a),
								$column_a,
								$this->getTableAlias($table_b),
								$column_b
					  );
		}		
		return $clause;
	}
	
	
  /**
   * createWhereClause
   *
   * @return string $clause
   */
	private function createWhereClause() {
		$clause = '';		
		foreach ($this->conditions as $fieldname => $condition) {
			if ($clause) $clause.= ' ' . $condition['bool'] . ' ';
			//$clause.= $this->getTableAlias($this->table). '.' . $fieldname . ' ' . $condition['operator'] . ' ' . $condition['value'];		
			$clause.= $this->table. '.' . $fieldname . ' ' . $condition['operator'] . ' ' . $condition['value'];		
		}		
		return $clause;
	}
	
  /**
   * createOrderClause
   *
   * @return string $clause
   */
	private function createOrderClause() {
		$clause = '';
		foreach ($this->sortOrder as $fieldname => $order) {
			if ($clause) $clause.=',';
			$clause.= $fieldname;
			if ($order) $clause.=' '.$order;		
		}
		return $clause;
	}
	
  /**
   * quoteString
   *
   * @param mixed $string
   * @return string $quotedString
   */
	private function quoteString($string) {
		return "'".$string."'";		
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_query.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_query.php']);
}
?> 