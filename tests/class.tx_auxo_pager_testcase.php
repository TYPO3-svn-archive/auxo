<?php

/**
 * @author 
 * @copyright 2007
 */

// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('t3unit') . 'class.tx_t3unit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');
$class = 'tx_auxo_pager_testcase';

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

class tx_auxo_pager_testcase extends tx_t3unit_testcase {

    static $create_table_people = "create table tx_auxo_people (
								  uid int(11) unsigned not null auto_increment,
								  name varchar(20) not null default '',
								  primary key(uid)) 
								  type=myIsam;";

 
								    	
  /**
   * testPagerCreate
   *
   * @return
   */
	function testPagerCreate() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);		
	    self::assertTrue(is_object($pager));
	    self::assertTrue($pager instanceof tx_lib_object);
	}
	
  /**
   * testPagerGetTotal
   *
   * @return
   */
	function testPagerGetTotal() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);		
	    self::assertTrue(is_object($pager));
	    self::assertSame((int) $pager->getTotalPages(), 10);
	    self::assertSame((int) $pager->getTotalCount(), 100);
	}
 	
 /**
   * testPagerIsFirstLast
   *
   * @return
   */
	function testPagerIsFirstLast() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);
	    self::assertTrue(is_object($pager));
	    $pager->gotoFirstPage();
	    self::assertTrue($pager->isFirstPage());
	    self::assertFalse($pager->isLastPage());
	    $pager->gotoLastPage();
	    self::assertFalse($pager->isFirstPage());
	    self::assertTrue($pager->isLastPage());
	}
	

 /**
   * tx_auxo_model_testcase::testModelCreate()
   *
   * @return
   */
	function testPagerHasPreviousNext() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);
	    self::assertTrue(is_object($pager));
	    $pager->gotoFirstPage();
	    self::assertFalse($pager->hasPreviousPage());
	    self::assertTrue($pager->hasNextPage());
	    $pager->gotoLastPage();
	    self::assertFalse($pager->hasNextPage());
	    self::assertTrue($pager->hasPreviousPage());
	    $pager->setCurrentPage(5);
	    self::assertTrue($pager->hasPreviousPage());
	    self::assertTrue($pager->hasNextPage());	    
	}


 /**
   * testPagerGetPreviousNext
   *
   * @return
   */
	function testPagerGetPreviousNext() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);
	    self::assertTrue(is_object($pager));
	    $pager->gotoFirstPage();
	    self::assertSame((int) $pager->getNextPage(), 2);
	    $pager->gotoLastPage();
	    self::assertSame((int) $pager->getPreviousPage(), 9);
	}




 /**
   * TextPagerLoop
   *
   * @return
   */
	function testPagerLoop() {
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25);
	    self::assertTrue(is_object($pager));
	    $pager->setCurrentPage(5);
		$i = 41;
		while($pager->valid()) {
			$name = 'Name'.$i;
			self::assertSame($pager->current()->name, $name);
			$pager->next();
			$i++;
		}
		self::assertSame($i, 51);
	}
	

 /**
   * TextPagerCondition
   *
   * @return
   */
	function testPagerCondition() {
		$query = new tx_auxo_query();
		$query->addWhere('uid', '41', tx_auxo_query::GREATER_EQUAL);
		$pager = new tx_auxo_pager('tx_auxo_models_people', 10, 25, 1, array('criteria' => $query));
	    self::assertTrue(is_object($pager));
	    self::assertTrue($pager->isFirstPage());
		$i = 41;
		while($pager->valid()) {
			$name = 'Name'.$i;
			self::assertSame($pager->current()->name, $name);
			$pager->next();
			$i++;
		}
		self::assertSame($i, 51);
	}	
 	
    /****************************************************************
     * main, setUP, tearDown
     ****************************************************************/

  /**
   * Constructor
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
		tx_auxo_debug::enable(TX_AUXO_DEBUG::SQL);
		$GLOBALS['TYPO3_DB']->debugOutput = true;
        $GLOBALS['TYPO3_DB']->sql(TYPO3_db, self::$create_table_people);
        for ($i=1; $i <= 100; $i++) {
	        $this->setupTable('tx_auxo_people', array('name' => 'Name'.$i));		
		}
	}

  /**
   * setupTable
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
    protected function tearDown() {
		$GLOBALS['TYPO3_DB']->sql(TYPO3_db, 'DROP TABLE tx_auxo_people;');    	
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