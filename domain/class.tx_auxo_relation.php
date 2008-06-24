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
 * This classes represents database objects/tables and offers general methods 
 * to manipulate data entries e.g. select, selectSingle, count, delete, set, get,
 * hide, unhide and save. 
 * It has to be extended for each object/table that should be supported and certain
 * class variables have to be set to do so. 
 * 
 * Functionality of this class is inspired by the propel project, ative record pattern 
 * and ruby on rails.
 *
 * class tx_abc_models_persons extends tx_auxo_modelbase {
 *    static function initialize() {
 *          // general
 *		    self::$table      = 'tx_abc_people';
 *    		self::$fieldnames = array('lastname', 'firstname');
 *   		self::$autofields = array('pid', 'crdate');	
 * 			...
 *          // associations
 *          ...
 *          // validations
 *          ...
 *
 * }
 *
 * // relations
 * if you would like to create relations between your models you can
 * use following methods in your constructor:
 * 
 * self::associate(self::HAS_ONE, 'address');
 * self::associate(self::HAS_MANY, 'postings');
 * self::associate(self::BELONGS_TO, 'company');
 * self::associate(self::HAS_AND_BELONGS_TO_MANY, 'issues');
 *
 * or with additional options:
 * 
 * $this->associate(self::has_one, 'address', array('forgein_key', => 'addr_id'));
 * $this->associate(self::has_many, 'postings', array('loading' => self:eager));
 *
 * - loading
 * - forgein_key
 * - unique_key
 * - dependent
 * - inline
 * 
 * // add a new record
 * $entry = new tx_abc_models_people();
 * $entry->set('lastname', 'Meyer');
 * $entry->set('firstname', 'Paul');
 * $entry->save();
 * ...
 *
 * // select a record
 * $query = tx_auxo_query( );
 * $query->addWhere('lastname', 'M%', tx_auxo_query::LIKE);
 * $peoples = tx_abc_models_persons::select($query);
 * 
 * while($persons->next()) {
 *   $person = $persons->current();
 *   echo $person->get('lastname');
 *   // or use named functions
 *   echo $person->getFirstname();
 * }
 * ...
 *
 * // delete a record
 * $person = tx_abc_models_persons::selectSingle($id);
 * $person->delete();
 *
 * @package 	auxo
 * @subpackage	models 
 * @author 		Andreas Horn
 * @copyright 	2007
 * @version 	$Id$
 * @access 		public
 */
 
class tx_auxo_relation {

	public	$kind;
	public	$table;
	public  $unique_key;
	public	$forgein_table;
	public	$forgein_key;
	public	$loading = tx_auxo_schemabase::LAZY;
	public	$model;
	public  $dependent;
	/** keeps query conditions, etc. that should be used selecting and sorting associated data **/
	public  $conditions = NULL;
	
	public	$schema = NULL;	
 
  /**
   * tx_auxo_relation::__construct()
   *
   * @param mixed $table
   * @param mixed $forgein_table
   * @param mixed $options
   * @return void
   */
 	public function __construct($schema, $table, $forgein_table, $options) {
		// get current schema
		if (!$schema) {
			throw new tx_auxo_exception('no schema defined');
		}
		
		$this->schema = $schema;
		$this->table = $table;
		$this->forgein_table = $forgein_table;
		
		if (!isset($options['model'])) {						
			$this->model = tx_auxo_schemabase::getModelClassName($forgein_table);
		}
		else {
			$this->model = $options['model'];
		}
			
		$this->unique_key = $this->schema->getUniqueKey($table);
	}

	
  /**
   * setOption
   *
   * @param string $name
   * @param array  $options
   * @param mixed  $default
   * @return void
   */
	public function setOption($name, $options, $default) {
		if ($options[$name]){
			$this->{$name} = $options[$name];
		}
		elseif ($default) {
			$this->{$name} = $default;
		}
	}


  /**
   * tx_auxo_relation::getInstance()
   *
   * @param mixed $kind
   * @param mixed $table
   * @param mixed $forgein_table
   * @param mixed $options
   * @return
   */
	public static function getInstance($kind, $schema, $table, $forgein_table, $options=NULL) {
		switch ($kind) {
			case tx_auxo_schemabase::HAS_ONE;
			  return new tx_auxo_relationHasOne($schema, $table, $forgein_table, $options);
			  break;
			case tx_auxo_schemabase::HAS_MANY;
			  if (isset($options['inline'])) {
				  return new tx_auxo_relationHasManyInline($schema, $table, $forgein_table, $options);
			  }
			  return new tx_auxo_relationHasMany($schema, $table, $forgein_table, $options);
			  break;
			case tx_auxo_schemabase::BELONGS_TO;
			  return new tx_auxo_relationBelongsTo($schema, $table, $forgein_table, $options);
		      break;
		    case tx_auxo_schemabase::HAS_AND_BELONGS_TO_MANY;
			  return new tx_auxo_relationHasAndBelongsToMany($schema, $table, $forgein_table, $options);
		      break;		    
			default:
			  throw new tx_auxo_exception('unknown association type');
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_relation.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mvext/class.tx_auxo_relation.php']);
}
?>