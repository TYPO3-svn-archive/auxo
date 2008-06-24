<?php
/**
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 */

// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('t3unit') . 'class.tx_t3unit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'tests/class.tx_auxo_schema_example.php');
$class = 'tx_auxo_schema_testcase';
/**
 * Testcases for caching
 *
 * @package auxo 
 * @subpackage testcases
 * @author Andreas Horn
 */

class tx_auxo_schema_testcase extends tx_t3unit_testcase {

	
	function testSchemaIsLoaded() {
		 $instance = tx_auxo_schemabase::getInstance('tx_auxo', 'example');
		 self::assertTrue(is_object($instance));
	}

	function testSchemaGetUniqueKey() {
		 $instance = tx_auxo_schemabase::getInstance('tx_auxo', 'example');
		 self::assertTrue(is_object($instance));
		 self::assertEquals($instance->getUniqueKey('tx_auxo_people'), 'uid');
		 self::assertEquals($instance->getUniqueKey('tx_auxo_group'), 'uid');
	}
	
	function testSchemaTables() {
		 $instance = tx_auxo_schemabase::getInstance('tx_auxo', 'example');
         self::assertTrue(isset($instance->objects['tx_auxo_people']));
	}
	
	function testSchemaModelClassName() {
		self::assertEquals(tx_auxo_schemabase::getModelClassName('tx_auxo_people'), 'tx_auxo_models_people');
		self::assertEquals(tx_auxo_schemabase::getModelClassName('tx_auxo_group'), 'tx_auxo_models_group');
	}

	function testSchemaModelClassNameWithExtension() {
		self::assertEquals(tx_auxo_schemabase::getModelClassName('tx_auxo_people', 'tx_auxo'), 'tx_auxo_models_people');
	}
	
	function testSchemaExtensionByTable() {
		self::assertEquals(tx_auxo_schemabase::getExtensionByTable('tx_auxo_people'),'tx_auxo');
	}
	
	function testSchemaGetStrippedTableName() {
		self::assertEquals(tx_auxo_schemabase::getStrippedTableName('tx_auxo_people', 'tx_auxo'), 'people');
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
    public function __construct($name) {
        parent::__construct($name);
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