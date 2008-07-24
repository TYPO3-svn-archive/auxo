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

$class = 'tx_auxo_extender_testcase';

/**
 * Testcases for extender class
 *
 * @package auxo 
 * @subpackage testcases
 * @author Andreas Horn
 */

class tx_auxo_pluginTest {
	public static function b($object) {
		return get_class($object);
	}
	
	public function a($object) {
		return get_class($object);
	}
}


class tx_auxo_extendableTest implements tx_auxo_extendable {
	function __call($method, $parameters) {
		return tx_auxo_extender::callPlugin($this, $method, true, $parameters);			
	}
}

class tx_auxo_extendableInheritedTest extends tx_auxo_extendableTest {
	function myFunction() {
		return '0123456789';
	}
} 

class tx_auxo_extender_testcase extends tx_t3unit_testcase {
	
  /** --------------------------------------------------------- **/

	public function testExtenderObject() {	
		$extender = new tx_auxo_pluginTest();
		tx_auxo_extender::register('tx_auxo_extendableTest', 'Test', $extender);
		$extended = new tx_auxo_extendableTest();
		self::assertEquals($extended->a(), 'tx_auxo_extendableTest');
	}


	public function testExtenderClass() {	
		tx_auxo_extender::register('tx_auxo_extendableTest', 'Test', 'tx_auxo_pluginTest');
		$extended = new tx_auxo_extendableTest();
		self::assertEquals($extended->b(), 'tx_auxo_extendableTest');
	}

	public function testExtenderInheritedObject() {	
		$extender = new tx_auxo_pluginTest();
		tx_auxo_extender::register('tx_auxo_extendableTest', 'Test', $extender);
		$extended = new tx_auxo_extendableInheritedTest();
		debug($extended->a());
		self::assertEquals($extended->a(), 'tx_auxo_extendableInheritedTest');
	}
					
  /** --------------------------------------------------------- **/
  
  /**
   * __construct()
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
		
	}
	
    /**
     * Tears down the fixture, for example, close a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown() {
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