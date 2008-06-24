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

$class = 'tx_auxo_cache_testcase';

/**
 * Testcases for caching
 *
 * @package auxo 
 * @subpackage testcases
 * @author Andreas Horn
 */

class tx_auxo_cache_testcase extends tx_t3unit_testcase {
	
  /** --------------------------------------------------------- **/

	public function testAddToCache() {
    	tx_auxo_cache::clean();
		tx_auxo_cache::add('abc', 'abcdefghijklmnoqrstuvwxyz1234567890');		
		self::assertTrue(tx_auxo_cache::has('abc'));
	}

	public function testGetFromCache() {
    	tx_auxo_cache::clean();
		tx_auxo_cache::add('abc', 'abcdefghijklmnoqrstuvwxyz1234567890');		
		self::assertTrue(tx_auxo_cache::has('abc'));
		self::assertEquals(tx_auxo_cache::get('abc'), 'abcdefghijklmnoqrstuvwxyz1234567890');		
	}

	public function testRemoveFromCache() {
		tx_auxo_cache::clean();
		tx_auxo_cache::add('abc', 'abcdefghijklmnoqrstuvwxyz1234567890');		
		self::assertTrue(tx_auxo_cache::has('abc'));
		tx_auxo_cache::remove('abc');
		self::assertFalse(tx_auxo_cache::has('abc'));		
	}


	public function testFileCache() {
		tx_auxo_cache::clean();
		tx_auxo_cache::setMemorySize(2);
		tx_auxo_cache::setMemoryRequired(1);
		tx_auxo_cache::add('abc', 'abc String');		
		tx_auxo_cache::add('123', '123 String');		
		tx_auxo_cache::add('ABC', 'ABC String');		

		self::assertTrue(tx_auxo_cache::has('abc'));
		self::assertTrue(tx_auxo_cache::has('123'));
		self::assertTrue(tx_auxo_cache::has('ABC'));
		self::assertEquals(tx_auxo_cache::get('abc'), 'abc String');		
		self::assertEquals(tx_auxo_cache::get('123'), '123 String');		
		self::assertEquals(tx_auxo_cache::get('ABC'), 'ABC String');		
	}
	

	public function testCleanCache() {
		tx_auxo_cache::clean();
		tx_auxo_cache::setMemorySize(2);
		tx_auxo_cache::setMemoryRequired(1);
		tx_auxo_cache::add('abc', 'abc String');		
		tx_auxo_cache::add('123', '123 String');		
		tx_auxo_cache::add('ABC', 'ABC String');		
		tx_auxo_cache::clean();

		self::assertFalse(tx_auxo_cache::has('abc'));
		self::assertFalse(tx_auxo_cache::has('123'));
		self::assertFalse(tx_auxo_cache::has('ABC'));
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