<?php
// Fix part to set before class definition
error_reporting (E_ALL ^ E_NOTICE);

require_once(t3lib_extMgm::extPath('t3unit') . 'class.tx_t3unit_testcase.php');
require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo_validator.php');

$class = 'tx_auxo_validator_testcase';
/**
 * testcase_validator
 *
 * This test class covers tests for: 
 * date, mandatory, size, range, email, time
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $WCREVS$
 * @access public
 */
class tx_auxo_validator_testcase extends tx_t3unit_testcase {

  /**
   * tx_auxo_validator_testcase::testDate()
   *
   * @return
   */
	function testDate() {
		self::assertTrue($this->validateDate('12.12.2007'), 'correct date failed');
		self::assertFalse($this->validateDate('2007.12.12', array('format' => '%d.%m.%Y')), 'wrong date failed');
		self::assertTrue($this->validateDate('12.12.07'), 'correct date failed');
		self::assertTrue($this->validateDate('2007.12.12', array('format' => '%Y.%m.%d')), 'correct date failed');
		self::assertFalse($this->validateDate('50.12.12', array('format' => '%d.%m.%Y')), 'wrong date failed');
		
		self::assertTrue($this->validateDate('12-12-2007'));
		self::assertTrue($this->validateDate('2007-12-12', array('format' => '%Y-%m-%d')), 'correct date failed');
		self::assertTrue($this->validateDate('12-12-07', array('format' => '%y-%m-%d')), 'correct date failed');
		self::assertTrue($this->validateDate('07-12-12', array('format' => '%y-%m-%d')), 'correct date failed');
		
		self::assertTrue($this->validateDate('12/12/2007'));
		self::assertTrue($this->validateDate('2007/12/12', array('format' => '%Y/%m/%d')), 'correct date failed');
		self::assertTrue($this->validateDate('12/12/07', array('format' => '%d/%m/%y')), 'correct date failed');
		self::assertTrue($this->validateDate('07/12/12', array('format' => '%y/%m/%d')), 'correct date failed');

		self::assertFalse($this->validateDate('2007/99/99', array('format' => '%y/%m/%d')), 'wrong month failed');
		self::assertFalse($this->validateDate('12/13/07', array('format' => '%d/%m/%y')), 'wrong month failed');
		self::assertFalse($this->validateDate('07/05/13', array('format' => '%y/%d/%m')), 'wrong month failed');
		
		self::assertFalse($this->validateDate('2007/11/31'), 'wrong day failed');
		self::assertFalse($this->validateDate('31/11/07'), 'wrong day failed');
		self::assertFalse($this->validateDate('07/02/30', array('format' => '%y/%m/%d')), 'wrong day failed');
	}
	
  /**
   * tx_auxo_validator_testcase::testMaxSize()
   *
   * @return
   */
	function testMaxSize() {
	    self::assertFalse($this->validateMaxSize('abcd', 1), 'Size = 1 -> Wrong');
	    self::assertTrue($this->validateMaxSize('abcd', 4), 'Size = 4 -> Okay');
	    self::assertTrue($this->validateMaxSize('abcd', 5), 'Size = 5 -> Okay');
	}

  /**
   * tx_auxo_validator_testcase::testMinSize()
   *
   * @return
   */
	function testMinSize() {
	    self::assertTrue($this->validateMinSize('abcde', 1), 'MinSize = 1 failed');
	    self::assertTrue($this->validateMinSize('abcd', 4), 'MinSize = 4 failed');
	    self::assertFalse($this->validateMinSize('abcd', 5), 'MinSize = 5 -> failed');
	}	
	
  /**
   * tx_auxo_validator_testcase::testSizeRange()
   *
   * @return
   */
	function testSizeRange() {    
	    self::assertTrue($this->validateSizeRange('abcd', 2, 4));
        self::assertTrue($this->validateSizeRange('ab', 1, 5));	 
	    self::assertFalse($this->validateSizeRange('abcd', 1, 3));
	    self::assertFalse($this->validateSizeRange('ab', 3, 5));
	}
	
  /**
   * tx_auxo_validator_testcase::testMandatory()
   *
   * @return
   */
	function testMandatory() {
	    self::assertTrue($this->validateMandatory('abcde'));
	    self::assertFalse($this->validateMandatory(''));
	}
	
  /**
   * tx_auxo_validator_testcase::testEmail()
   *
   * @return
   */
	function testEmail() {
	    self::assertTrue($this->validateEmail('anton.berta@cesar.de'));
	    self::assertTrue($this->validateEmail('anton@cesar.de'));
	    self::assertTrue($this->validateEmail('anton_berta@cesar-dora.org'));
	    self::assertFalse($this->validateEmail('@cesar.de'));
	    self::assertFalse($this->validateEmail('anton@berta'));
	    self::assertTrue($this->validateEmail('anton12@berta.orga'));
	    self::assertFalse($this->validateEmail('anton@berta.d'));
	    self::assertFalse($this->validateEmail('anton@berta.office'));
	    self::assertFalse($this->validateEmail('anton.de'));
	    self::assertFalse($this->validateEmail('antaon@'));
	}
	
  /**
   * tx_auxo_validator_testcase::testDecimals()
   *
   * @return
   */
	function testDecimals() {
		self::assertTrue($this->validateDecimals('1234.56'), 'corect decimals failed');
		self::assertTrue($this->validateDecimals('-1234.56'), 'corect decimals failed');
		self::assertTrue($this->validateDecimals('+1234.56'), 'corect decimals failed');	
		self::assertFalse($this->validateDecimals('1234.56-'), 'wrong decimals failed');				
	}
	
  /**
   * tx_auxo_validator_testcase::testAmount()
   *
   * @return
   */
	function testAmount() {
		self::assertFalse($this->validateAmount('1234.5678.12', 2), 'correct amount failed');
		self::assertTrue($this->validateAmount('5678.12', 2), 'correct amount failed');
		self::assertTrue($this->validateAmount('5678'), 'correct amount failed');
		self::assertTrue($this->validateAmount('+5678'), 'correct amount failed');
		self::assertFalse($this->validateAmount('5678.12-', 2), 'wrong amount failed');		
		self::assertFalse($this->validateAmount('1234,5678,12', 2), 'wrong amount failed');
		self::assertFalse($this->validateAmount('5678,132', 2), 'wrong amount failed');
		self::assertFalse($this->validateAmount('5678,3'), 'wrong amount failed');	
	}
	
  /**
   * tx_auxo_validator_testcase::testEval()
   *
   * @return void
   */
	function testEval() {
		self::assertTrue($this->validateEval('35', '$value <> 12'));
		self::assertTrue($this->validateEval('35', '$value <> 12 AND $value < 40'));
		self::assertTrue($this->validateEval('35', '$value * 3 > 100'));
		self::assertTrue($this->validateEval(strtotime('2007-10-05'), '$value < strtotime("2007-10-10")'));
		self::assertFalse($this->validateEval(strtotime('2007-10-05'), '$value > strtotime("2007-10-10")'));
		self::assertFalse($this->validateEval('35', '$value <> 35'));
		self::assertFalse($this->validateEval(35, '$value < 20'));
		self::assertFalse($this->validateEval('35', '$value * 3 > 120'));
	}
	
  /**
   * tx_auxo_validator_testcase::validateDate()
   *
   * @param mixed $value
   * @param mixed $options
   * @return
   */
	function validateDate($value, $options=array()) {
		$validator = new tx_auxo_VALIDATOR();
		$validator->addRules(tx_auxo_VALIDATOR::RULE_DATE, 'bad date', $options);
		return $validator->validate($value);
	}
	
  /**
   * tx_auxo_validator_testcase::validateMinSize()
   *
   * @param mixed $value
   * @param mixed $size
   * @return
   */
	function validateMinSize($value, $size) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_SIZE, 'bad size', array('min' => $size));
		return ($validator->validate($value));
	}

  /**
   * tx_auxo_validator_testcase::validateMaxSize()
   *
   * @param mixed $value
   * @param mixed $size
   * @return
   */
	function validateMaxSize($value, $size) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_SIZE, 'bad size', array('max' => $size));
		return ($validator->validate($value));
	}
	
  /**
   * tx_auxo_validator_testcase::validateSizeRange()
   *
   * @param mixed $value
   * @param mixed $min
   * @param mixed $max
   * @return
   */
	function validateSizeRange($value, $min, $max) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_SIZE, 'bad size', array('min' => $min, 'max' => $max));
		return ($validator->validate($value));
	}

  /**
   * tx_auxo_validator_testcase::validateMandatory()
   *
   * @param mixed $value
   * @return
   */
	function validateMandatory($value) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_MANDATORY, 'is empty');
		return ($validator->validate($value));	    
	}
	
  /**
   * tx_auxo_validator_testcase::validateEmail()
   *
   * @param mixed $value
   * @return
   */
	function validateEmail($value) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_EMAIL, 'bad email');
		return ($validator->validate($value));	    
	}

  /**
   * tx_auxo_validator_testcase::validateDecimals()
   *
   * @param mixed $value
   * @param integer $decimals
   * @return
   */
	function validateDecimals($value, $decimals=0) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_DECIMALS, 'bad decimals', array('decimals' => $decimals));
		return ($validator->validate($value));	    
	}

  /**
   * tx_auxo_validator_testcase::validateAmount()
   *
   * @param mixed $value
   * @param integer $decimals
   * @return
   */
	function validateAmount($value, $decimals=0) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_AMOUNT, 'bad decimals', array('decimals' => $decimals));
		return ($validator->validate($value));	    
	}

  /**
   * tx_auxo_validator_testcase::validateEval()
   *
   * @param mixed $value
   * @param integer $decimals
   * @return
   */
	function validateEval($value, $eval) {
		$validator = new tx_auxo_VALIDATOR();	
		$validator->addRules(tx_auxo_VALIDATOR::RULE_EVAL, 'bad expression', array('eval' => $eval));
		return ($validator->validate($value));	    
	}		
    /****************************************************************
     * main, setUP, tearDown
     ****************************************************************/

  /**
   * tx_auxo_validator_testcase::__construct()
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
   * tx_auxo_validator_testcase::setUp()
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
   * tx_auxo_validator_testcase::tearDown()
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
   * tx_auxo_validator_testcase::main()
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