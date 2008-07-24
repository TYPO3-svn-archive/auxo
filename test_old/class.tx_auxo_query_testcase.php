<?php

/**
 * @author 
 * @copyright 2007
 */

// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('t3unit') . 'class.tx_t3unit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo_modelbase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo_query.php');

$class = 'tx_auxo_query_testcase';
/**
 * query_testcase
 *
 * This test class covers tests for: 
 * sort, conditions.
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */
 
class tx_auxo_query_testcase extends tx_t3unit_testcase {

	function testQueryWhereEqual() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('name', 'Miller');
		$sql = $query->build('tx_auxo_people');
		$expect = "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.name = 'Miller'";
		self::AssertSame($sql, $expect);
	}
	
	function testQueryWhereGreaterEqual() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::GREATER_EQUAL);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age >= '18'");
	}

	function testQueryWhereLessEqual() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::LESS_EQUAL);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age <= '18'");
	}
		
	function testQueryWhereNotEqual() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::NOT_EQUAL);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age <> '18'");
	}

	function testQueryWhereLess() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::LESS);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age < '18'");
	}

	function testQueryWhereGreater() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::GREATER);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age > '18'");
	}

	function testQueryWhereLike() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', '18', tx_auxo_query::LIKE);
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age LIKE '18'");
	}
	
	
	function testQueryWhereIn() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('uid', array(1,2,3), tx_auxo_query::IN);
		$sql = $query->build('tx_auxo_people');
		$expect = "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.uid IN ('1','2','3')";
		self::AssertSame($sql, $expect);
	}
	
	function testQueryWhereLikeOrder() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', 'M%', tx_auxo_query::LIKE);
		$query->addSort('name');
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age LIKE 'M%' ORDER BY name");
	}
	
	function testQueryWhereLikeOrderMore() {
		// fixture
		$query = new tx_auxo_query();
		$query->addWhere('age', 'M%', tx_auxo_query::LIKE);
		$query->addSort('name');
		$query->addSort('age');
		$sql = $query->build('tx_auxo_people');
		self::AssertSame($sql, "SELECT tx_auxo_people.* FROM tx_auxo_people WHERE tx_auxo_people.age LIKE 'M%' ORDER BY name,age");
	}
			
    /****************************************************************
     * main, setUP, tearDown
     ****************************************************************/

  /**
   * tx_mvext_query_testcase::__construct()
   *
   * @param mixed $name
   * @return
   */
    public function __construct ($name) {
        parent::__construct ($name);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
  /**
   * tx_mvext_query_testcase::setUp()
   *
   * @return
   */
    protected function setUp() {
		$this->schema = tx_auxo_schemabase::getInstance('tx_auxo', 'example');
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
    protected function tearDown() {
    }
		
    /**
     * Runs the test methods of this class.
     *
     * @access public
     * @static
     */
  /**
   * tx_mvext_query_testcase::main()
   *
   * @return
   */
    public static function main() {
        global $class;
        require_once "PHPUnit2/TextUI/TestRunner.php";
        $suite  = new PHPUnit2_Framework_TestSuite($class);
        $result = PHPUnit2_TextUI_TestRunner::run($suite);
    }
}

?>