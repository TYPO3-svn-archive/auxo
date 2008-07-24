<?php

/**
 * @author 
 * @copyright 2007
 */

// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('phpunit'). 'class.tx_phpunit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');

$class = 'tx_auxo_businesslogic_testcase';
/**
 * businesslogic_testcase
 *
 * This test class covers tests for: 
 * date, mandatory, size, range, email, time
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */
class tx_auxo_businesslogic_testcase extends PHPunit_Framework_Testcase {
	

	function testBusinesslogicFirst() {
		// fixture
		$data = new tx_lib_object();
		$data->set( 'name', 'Andreas');
		$data->set( 'age' , '33' );
		$data->set( 'entry' , '14.12.2007' );
		$logic = new tx_auxo_businesslogic();
		$validator = new tx_auxo_validator(TX_auxo_VALIDATOR::RULE_MANDATORY, 'not filled');	
		$validator->addRules(TX_auxo_VALIDATOR::RULE_SIZE, 'too short', array('min' => 3));
		$logic->set('name', $validator);
		$logic->set('age', new tx_auxo_validator(TX_auxo_VALIDATOR::RULE_EVAL, 'too young', array ('eval' => '$value < 34')));
		$logic->set('entry', new tx_auxo_validator(TX_auxo_VALIDATOR::RULE_EVAL, 'too late', array ('eval' => '$value < strtotime("2007-12-31")')));
		self::assertTrue($logic->validate($data), 'logic is fulfilled failed');
	}

	function testBusinesslogicSecond() {
		// fixture
		$data = new tx_lib_object();
		$data->set( 'name', 'Jo');
		$data->set( 'age' , '34' );
		$data->set( 'entry' , strtotime('12/14/2007'));
		$data->set( 'leave' , strtotime('12/31/2007'));
		$logic = new tx_auxo_businesslogic();
		$logic->set('name', new tx_auxo_validator(
									tx_auxo_validator::RULE_SIZE,
									'too short',
									array('min' => 3)
								)
					);
		$logic->set('age', new tx_auxo_validator(
									TX_auxo_VALIDATOR::RULE_EVAL, 
									'too young', 
									array ('eval' => '$value < 34')
								)
					);
		$logic->set('entry', new tx_auxo_validator(
									TX_auxo_VALIDATOR::RULE_EVAL, 
									'too late', 
									array ('eval' => '$value <= strtotime("12/31/2007")')
								)
					);
		self::assertFalse($logic->validate($data), 'logic is fulfilled failed');
	}	
    /****************************************************************
     * main, setUP, tearDown
     ****************************************************************/

  /**
   * tx_mvext_validator_testcase::__construct()
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
   * tx_mvext_validator_testcase::setUp()
   *
   * @return
   */
    protected function setUp() { 
    	setLocale(LC_ALL, 'de_DE');
	}
	
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
  /**
   * tx_mvext_validator_testcase::tearDown()
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
   * tx_mvext_validator_testcase::main()
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