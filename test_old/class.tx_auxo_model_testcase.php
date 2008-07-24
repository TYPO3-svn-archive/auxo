<?php

/**
 * @author 
 * @copyright 2007
 */

// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('t3unit') . 'class.tx_t3unit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');
$class = 'tx_auxo_model_testcase';
/**
 * model_testcase
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */

class tx_auxo_model_testcase extends tx_t3unit_testcase {

    static $create_table_people = "create table tx_auxo_people (
								  uid int(11) unsigned not null auto_increment,
								  name varchar(20) not null default '',
								  tx_auxo_group_id int(11) unsigned default 0,
								  primary key(uid)) 
								  type=myIsam;";

    static $create_table_group  = "create table tx_auxo_group (
								  uid int(11) unsigned not null auto_increment,
								  name varchar(20) not null default '',
								  primary key(uid)) 
								  type=myIsam;";
								  

    static $create_table_address = "create table tx_auxo_address (
								    uid int(11) unsigned not null auto_increment,
								    location varchar(20) not null default '',
								    tx_auxo_people_id int(11) unsigned default 0,
								    primary key(uid)) 
								    type=myIsam;";
								  
    static $create_table_project = "create table tx_auxo_project (
								   uid int(11) unsigned not null auto_increment,
								   name varchar(20) not null default '',
								   tx_auxo_notes_ids tinyblob not null default '',
								   primary key(uid)) 
								   type=myIsam;";
								  

    static $create_table_notes  = "create table tx_auxo_notes (
								  uid int(11) unsigned not null auto_increment,
								  note blob not null default '',
								  primary key(uid)) 
								  type=myIsam;";
								  

    static $create_table_joined = "create table tx_auxo_people_project (
								  uid int(11) unsigned not null auto_increment,
								  tx_auxo_people_id int(11) not null,
								  tx_auxo_project_id int(11) not null,
								  primary key(uid)) 
								  type=myIsam;";
								  

								    	
  /**
   * tx_auxo_model_testcase::testModelCreate()
   *
   * @return
   */
	function testModelCreate() {
	    self::assertTrue(is_object(new tx_auxo_models_people()));
	}
	
  /**
   * Creates an empty object of model "people", verifies if that object
   * has been created an default values are passed into its members using
   * get method.
   *
   * @return void
   */
	function testModelNewGetMethod() {
		$person = new tx_auxo_models_people(array('name' => 'Martin'));
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		self::AssertEquals($person->get('name'), 'Martin');
	}

  /**
   * Creates an empty object of model "people", verifies if that object
   * has been created an default values are passed into its members using
   * an accessor (magic methods)
   *
   * @return void
   */
 	function testModelNewGetAccessor() {
		$person = new tx_auxo_models_people(array('name' => 'Martin'));
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		self::AssertEquals($person->name, 'Martin');
	}

  /**
   * Creates an empty object of model "people", verifies if that object
   * has been created an default values are passed into its members using
   * an accessor (magic methods)
   *
   * @return void
   */
 	function testModelNewSetMethod() {
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->set('name', 'Leo');
		self::AssertEquals($person->name, 'Leo');
	}
	
  /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and verifies
   * it.
   *
   * @return void
   */
 	function testModelNewSetAccessor() {
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->name = 'Leo';
		self::AssertEquals($person->get('name'), 'Leo');
	}
 
 /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and verifies
   * if this object is NEW.
   *
   * @return void
   */
 
	function testModelIsNew() {
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->name = 'Leo';
		self::AssertTrue($person->isNew());		
	}	


  /**
   * Creates an empty object of model "people", verifies if that object
   * has been created an default values are passed into its members using
   * Method getValues.
   *
   * @return void
   */
	function testModelNewGetValues() {
		$person = new tx_auxo_models_people(array('name' => 'Martin'));
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		self::AssertTrue(is_array($values = $person->getValues()));
		self::AssertEquals($values['name'], 'Martin');
	}

 /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and saves
   * its data. Select this object from database in order to find out if
   * data has really been saved. Changes value of field "name" and saves
   * again. Then reloads this object to ensure data has been really 
   * changed.
   *
   * @return void
   */
	function testModelUpdate() {
		tx_auxo_debug::enable(tx_auxo_debug::SQL);
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->name = 'Sebastian';
		self::AssertTrue($person->save());		
		self::AssertNotEquals($person->id(), 0);
		$id = $person->id();
		debug($id, 'Update');
		$person = NULL;
		$person = tx_auxo_models_people::select($id);
		self::AssertEquals($person->name, 'Sebastian');
		$person->name = 'Michael';
		self::AssertTrue($person->save());
		$person = NULL;
		$person = tx_auxo_models_people::select($id);
		self::AssertEquals($person->name, 'Michael');		
	}	



 /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and saves
   * its data. Delete this object from the database. Select it again
   * to find out if data has really been deleted. 
   *
   * @return void
   */
	function testModelDelete() {
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->name = 'Leo';
		self::AssertTrue($person->save());		
		self::AssertNotEquals($person->id(), 0);
		$id = $person->id();
		debug($id);
		$person = NULL;
		$person = tx_auxo_models_people::select($id);
		self::AssertEquals($person->name, 'Leo');
		self::AssertTrue($person->delete());
		self::AssertTrue($person->isDeleted());
		$person = NULL;
		$person = tx_auxo_models_people::select($id);
		self::AssertEquals($person, NULL);		
	}	

 /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and saves
   * its data. Select this object from database in order to find out if
   * data has really been saved.
   *
   * @return void
   */
 
	function testModelSave() {
		$person = NULL;
		$person = new tx_auxo_models_people();
		self::AssertTrue(is_object($person));
		self::AssertTrue($person instanceof tx_auxo_models_people);
		$person->name = 'Bernhardt';
		self::AssertTrue($person->save());		
		self::AssertNotEquals($person->id(), 0);
		$id = $person->id();
		debug($id, 'Save');
		$person = NULL;
		$person = tx_auxo_models_people::select($id);
		self::AssertEquals($person->name, 'Bernhardt');
	}	

 /**
   * Creates an empty object of model "people", verifies if that object
   * has been created. Sets a field value using an accessor and saves
   * its data. Select this object from database in order to find out if
   * data has really been saved.
   *
   * @return void
   */
 
	function testModelHasManySave() {
		$group = new tx_auxo_models_group();
		self::AssertTrue(is_object($group));
		self::AssertTrue($group instanceof tx_auxo_models_group);
		$group->name = 'Guests';
		$personA = new tx_auxo_models_people(array('name' => 'Arne'));
		$group->tx_auxo_people = $personA;
		$personB = new tx_auxo_models_people(array('name' => 'Theo'));
		$group->tx_auxo_people = $personB;
		self::AssertTrue($group->save());		
		self::AssertNotEquals($group->id(), 0);
		$group_id = $group->id();
		self::AssertNotEquals($personA->id(), 0);
		self::AssertNotEquals($personB->id(), 0);
		$persons_id = array($personA->id(), $personB->id() );
		$group = NULL;
		$persons = tx_auxo_models_people::select($persons_id);
		while ($persons->valid()) {
			$group = $persons->current()->tx_auxo_group;		
			self::AssertTrue(is_object($group));
			self::AssertEquals(intval($group->id()), $group_id);
			$persons->next();			
		}
	}	

		
  /**
   * tx_auxo_model_testcase::testModelPeopleSelectbyId()
   *
   * @return
   */
	function testModelPeopleSelectbyId() {
	    $people = tx_auxo_models_people::select(1);
	    self::assertTrue(is_object($people));
	}
	
  /**
   * tx_auxo_model_testcase::testModelPeopleSelectByQuery()
   *
   * @return
   */
	function testModelPeopleSelectByQuery() {
		$query  = new tx_auxo_query();
		$query->addWhere('name', 'Dirk');
		$persons = tx_auxo_models_people::select($query);
		self::assertTrue(is_object($persons));
		self::assertSame($persons->count(), 1);

		while($persons->valid()) {
   	    	self::assertEquals($persons->current()->get('name'), 'Dirk');	   
			$persons->next();
		}
	}

	
  /**
   * tx_auxo_model_testcase::testModelPeopleHasAddress()
   *
   * @return void
   */
	function testModelPeopleSelectHasAddress() {
		$people = tx_auxo_models_people::select(1);
		self::assertEquals($people->tx_auxo_address->location, 'Berlin');
	}
	

  /**
   * tx_auxo_model_testcase::testModelNewPeopleAddress()
   *
   * @return void
   */
	function testModelNewPeopleAddress() {
		$address = new tx_auxo_models_address(array('location' => 'Prague'));
		$people = new tx_auxo_models_people(array('name' => 'Sascha'));
		$people->tx_auxo_address = $address;		
		self::assertEquals($people->name, 'Sascha');
		self::assertEquals($people->tx_auxo_address->location, 'Prague');
	}

  /**
   * tx_auxo_model_testcase::testModelNewPeopleProjects()
   *
   * @return void
   */
	function testModelSaveHasAndBelongsToMany() {
		$typo3 = new tx_auxo_models_project(array('name' => 'typo3'));
		$sascha = new tx_auxo_models_people(array('name' => 'Sascha'));
		$mark = new tx_auxo_models_people(array('name' => 'Mark'));
		$mark->tx_auxo_project = $typo3;
		$typo3->tx_auxo_people = $sascha;
        tx_auxo_debug::dump($mark);
        tx_auxo_debug::dump($typo3);
		self::AssertTrue($mark->save());
	}

  /**
   * tx_auxo_model_testcase::testModelSavePeopleAddress()
   *
   * @return void
   */
	function testModelSaveHasOne() {
		$address = new tx_auxo_models_address(array('location' => 'Prague'));
		$people = new tx_auxo_models_people(array('name' => 'Sascha'));
		$people->tx_auxo_address = $address;		
		self::assertEquals($people->name, 'Sascha');
		self::assertEquals($people->tx_auxo_address->location, 'Prague');
	    $saved = $people->save();
  		tx_auxo_debug::dump($people);
		self::assertTrue($saved);
		$people_id = $people->id();
		$people = NULL;
		$people = tx_auxo_models_people::select($people_id);
		self::assertEquals($people->name, 'Sascha');
		self::assertEquals($people->tx_auxo_address->location, 'Prague');		
	}
	
	
    /****************************************************************
     * main, setUP, tearDown
     ****************************************************************/

  /**
   * tx_auxo_model_testcase::__construct()
   *
   * @param mixed $name
   * @return
   */
    public function __construct($name) {
        parent::__construct($name);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp() {
		$this->instance = tx_auxo_schemabase::getInstance('tx_auxo', 'example');
		tx_auxo_cache::clean();
		$GLOBALS['TYPO3_DB']->debugOutput = true;
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;		
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_people);
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_group);
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_address);
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_project);
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_notes);
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_joined);
        $this->setupTable('tx_auxo_people', array('name' => 'Karl', 'tx_auxo_group_id' => 1));
        $this->setupTable('tx_auxo_people', array('name' => 'Dirk', 'tx_auxo_group_id' => 2));
        $this->setupTable('tx_auxo_people', array('name' => 'Tomm', 'tx_auxo_group_id' => 1));
        $this->setupTable('tx_auxo_people', array('name' => 'Mark', 'tx_auxo_group_id' => 3));
        $this->setupTable('tx_auxo_people', array('name' => 'Paul', 'tx_auxo_group_id' => 4));
        $this->setupTable('tx_auxo_people', array('name' => 'Toby', 'tx_auxo_group_id' => 4));
        $this->setupTable('tx_auxo_group', array('name' => 'Admin'));
        $this->setupTable('tx_auxo_group', array('name' => 'Editor'));
        $this->setupTable('tx_auxo_group', array('name' => 'Designer'));
        $this->setupTable('tx_auxo_group', array('name' => 'User'));
        $this->setupTable('tx_auxo_address', array('location' => 'Berlin', 'tx_auxo_people_id' => 1));
        $this->setupTable('tx_auxo_address', array('location' => 'Madrid', 'tx_auxo_people_id' => 2));
        $this->setupTable('tx_auxo_address', array('location' => 'Rom', 'tx_auxo_people_id' => 3));
        $this->setupTable('tx_auxo_address', array('location' => 'London', 'tx_auxo_people_id' => 4));
        $this->setupTable('tx_auxo_address', array('location' => 'Paris', 'tx_auxo_people_id' => 5));
        $this->setupTable('tx_auxo_project', array('name' => 'Apache', 'tx_auxo_notes_ids' => '1,2,3'));
        $this->setupTable('tx_auxo_project', array('name' => 'Java', 'tx_auxo_notes_ids' => '4,5,6' ));
        $this->setupTable('tx_auxo_notes', array('note' => '1. Info'));
        $this->setupTable('tx_auxo_notes', array('note' => '2. Info'));
        $this->setupTable('tx_auxo_notes', array('note' => '3. Info'));
        $this->setupTable('tx_auxo_notes', array('note' => '4. Info'));
        $this->setupTable('tx_auxo_notes', array('note' => '5. Info'));
        $this->setupTable('tx_auxo_notes', array('note' => '6. Info'));
        $this->setupTable('tx_auxo_people_project', array('tx_auxo_people_id' => 1, 'tx_auxo_project_id' => 1 ));
        $this->setupTable('tx_auxo_people_project', array('tx_auxo_people_id' => 2, 'tx_auxo_project_id' => 1 ));
        $this->setupTable('tx_auxo_people_project', array('tx_auxo_people_id' => 1, 'tx_auxo_project_id' => 2 ));
        $this->setupTable('tx_auxo_people_project', array('tx_auxo_people_id' => 2, 'tx_auxo_project_id' => 2 ));
	}

  /**
   * tx_auxo_model_testcase::setupTable()
   *
   * @param mixed $table
   * @param mixed $values
   * @return void
   */
	function setupTable($table, $values) {
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($table, $values);		
	}	
	
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
  /**
   * tx_mvext_query_testcase::tearDown()
   *
   * @return
   */
  /**
   * tx_auxo_model_testcase::tearDown()
   *
   * @return
   */
    protected function tearDown() {
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_people;');    	
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_group;');    	
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_address;');    	
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_project;');    	
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_notes;');    	
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_people_project;');    	
    }
		
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */

    public static function main() {
        global $class;
        require_once "PHPUnit2/TextUI/TestRunner.php";
        $suite  = new PHPUnit2_Framework_TestSuite($class);
        $result = PHPUnit2_TextUI_TestRunner::run($suite);
    }
}

?>