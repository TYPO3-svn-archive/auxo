<?php
/**
 * @package auxo
 * @subpackage models
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $WCREV$
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
 */
 
/**
 * Validator
 *
 * This class represents a validator that offers methods for a lot of standard 
 * validation requirements. Validators are used together with business logic classes
 * to defined complex verification rules that might be encapsulated in 
 * separate classes. Moreover, business logic classes are used in models to
 * implement an easy to use auto validation.
 * 
 * Following rules are implemented currently:
 * 
 * - Pattern 		validates data based on a given regexp pattern
 * - Size 			validates a minimum and/or maximum required size
 * - Email        	validates given email addresses
 * - Decimals     	validates values as decimal
 * - Amount       	validates values as amount
 * - Date         	validates dates based on a given format
 * - Mandatory    	validates if an value ist set
 * - Eval         	validates and expression 
 *
 * <b>Example:</b>
 *
 * // create a new validator instance 
 * $validator = new tx_auxo_validator();
 * // add rules that should be verified
 * $validator->addRule(TX_AUXO_VALIDATOR::RULE_MANDATORY, 'Field not filled');
 * $validator->addRule(TX_AUXO_VALIDATOR::RULE_DECIMALS, 'Value is not a valid Number');
 * $validator->addRule(TX_AUXO_VALIDATOR::RULE_EVAL, '$value >= 0 AND $value <= 30', 'only values 0 to 30 possible');
 *
 * // execute validation
 * if (!$validator->validate($input)) {
 *    // handle exceptions somehow 
 *	  foreach($validator->execeptions AS $exception) {
 *        echo $exception;
 *    }
 * }
 * 
 * // if you would like to apply only one rule it is easier to use the constructor
 * $emailValidator = new tx_auxo_validator(TX_AUXO_VALIDATOR::RULE_EMAIL, 'Invalid email address');
 *
 * @package auxo
 * @subpackage models
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 * @see tx_auxo_businesslogic
 * @see tx_auxo_modelbase
 */
class tx_auxo_validator {
	const PATTERN_AMOUNT        = '!^[+-]?[0-9,\.]*$!';
	const PATTERN_DECIMALS 		= '!^[+-]?[0-9]*\.?[0-9]+$!';
	const PATTERN_NUMERIC 		= '!^[+-]?[0-9]+!';
	const PATTERN_ALPHA 		= '![A-za-z]*!';
	const PATTERN_ALPHANUMERIC 	= '![0-9A-Za-z]*!';
	const PATTERN_EMAIL 		= '!^\w[\w|\.|\-]+@\w[\w|\.|\-]+\.[a-zA-Z]{2,4}$!';
	const PATTERN_DAY 			= '!^[012]?[0-9]|3[01]+$!';
	const PATTERN_MONTH 		= '!^[012]?[0-9]|3[01]+$!';
	const PATTERN_YEAR 			= '!^[0-9]{2}|[0-9]{4})$!';
	const PATTERN_DATE 			= '!\d[/.-]?\d[/.-]\d!';
	const PATTERN_TIME 			= '!^[01]?[0-9]+|2[0-3]+:[0-5]+[0-9]+$!';
	const PATTERN_BOOLEAN 		= '![01]+';
	
	const RULE_PATTERN 		= 1;
	const RULE_SIZE 		= 2;
	const RULE_MANDATORY 	= 3;
	const RULE_EVAL			= 4;
	const RULE_DATE      	= 5;
	const RULE_EMAIL      	= 6;
	const RULE_DECIMALS    	= 7;
	const RULE_AMOUNT       = 8;
	const RULE_UNIQUE       = 9;
	
	protected $rules = array();
	protected $exceptions = array();
	protected $valid = true;	
	protected $dateFormat = '%d.%m.%Y';
	
  /**
   * Builds a new validator and could be used as shortcut if a 
   * validator just consist of a single rule at the same time. 
   *
   * @param string $rule rule that has to be applied see RULE_...
   * @param string $exception error message if invalid
   * @param mixed $options more options see documentation
   * @return void 
   */
	function __construct($rule='', $exception='', $options=array()) {
		if ($rule) {		
			$this->addRules($rule, $exception, $options);
		}
	} 
	
  /**
   * Defines a rule for this validator.
   *
   * Following rules are:
   * - RULE_PATTERN 	regexp
   * - RULE_SIZE 		minimum/maxmimum size of an value
   * - RULE_MANDATORY 	mandatory (no empty)
   * - RULE_EVAL		expression that results in true/false
   * - RULE_DATE      	date based on a defined format
   * - RULE_EMAIL      	email address
   * - RULE_DECIMALS    decimals
   * - RULE_AMOUNT      amounts
   *
   * options:
   * - min, max 	    to defined a size
   * - decimals         to defined decimals for amount/decimals
   * - format           to defined a date format like strftime
   * - pattern          to defined a regexp pattern
   * - domain           to defined if an domain check should be performed
   *
   * @param string $rule rule that has to be applied see RULE_...
   * @param string $exception error message if invalid
   * @param mixed $options more options see documentation
   * @return void 
   */
	function addRules($rule, $exception, $options=array()) {
		$options['exception'] = $exception;
	    $this->rules[$rule] = $options;
	    return void;
	}
	
  /**
   * tx_auxo_validator::exceptions()
   *
   * @return	array
   */
	function exceptions() {
		return $this->exceptions;
	}

  /**
   * tx_auxo_validator::isValid()
   *
   * @return	boolean
   */
	function isValid() {
		return $this->valid;
	}
	
  /**
   * tx_auxo_validator::isValidAmount()
   *
   * @param 	mixed 	$amount
   * @param 	integer $decimals
   * @return	bool
   */
   function isValidAmount($amount, $decimals=0) {
	   	// check character set first
	   	if (!preg_match(self::PATTERN_AMOUNT, $amount)) {
			 return FALSE;
		}
		// get local monetary settings
	    $lcEnv = localeconv();	
		$decimal_point = $lcEnv['mon_decimal_point'] ? $lcEnv['mon_decimal_point'] : $lcEnv['decimal_point'];
		$thousand_sep = $lcEnv['mon_thousand_sep'] ? $lcEnv['mon_thousand_sep'] : $lcEnv['thousand_sep'];
		// remove thousands separator
		if ($thousand_sep) {
	    	$amount = str_replace($thousand_sep, '', $amount);
	    }
	    // get number and fractal
	    if (!is_array($parts = explode($decimal_point, $amount))) {
			return FALSE;
		}
		// check parts
		if (count($parts) > 2) {
			return FALSE;
		}		
		// check number of decimals
		if (strlen(trim($parts[1])) <> $decimals) {
			return FALSE;
		}
		
		foreach($parts as $value) {
			if (!preg_match('!^[+-]?[0-9]+$!', $value)) {
				return FALSE;
			}
		}
	    return TRUE;      
   }
   
  /**
   * tx_auxo_validator::isValidDate()
   *
   * @param 	mixed $date
   * @param 	mixed $format
   * @return	bool  $valid
   */
	function isValidDate($date, $format = '') {
		if (!$format) {
			$format = $this->dateFormat;
		}
		   
		if (!is_array($formats = split('[/.-]', $format)) OR (!is_array($values = split('[/.-]', $date)))) {
			return FALSE;
		}

		$year = $month = $day = 0; 

		foreach ($formats as $key => $item) {
			switch ($item) {
				case '%y':
				case '%Y':
				     $year = $values[$key];
				     break;
				case '%m':
				     $month = $values[$key];
				     break;
				case '%d':
				     $day = $values[$key];
				     break;
			}
		}

        return checkdate($month, $day, $year);
	}

  /**
   * tx_auxo_validator::isValidEmail()
   *
   * @param mixed $email
   * @return
   */
	function isValidEmail($email, $options=array()) {
		if (!preg_match(self::PATTERN_EMAIL, $email)) {
			return false;
		}
		
		if ($options['domain'] && function_exists('checkdnsrr')) {
	      $tokens = explode('@', $email);
	      if (!checkdnsrr($tokens[1], 'MX') && !checkdnsrr($tokens[1], 'A')) {
	           return false;
	      }
	    }		
	    
	    return true;
	}
		
  /**
   * Verifies if a value is unique in a model table
   *
   * @param mixed $value
   * @param array $options
   * @return boolean $unqiue
   */
	function isUnqiue($value, $options) {
		$query = new tx_auxo_query();
		$query->addWhere($options['fieldname'], $value);
		return !call_user_func(array($options['class'], 'select'), $query);
	}
	
  /**
   * tx_auxo_validator::validate()
   *
   * @param 	mixed 	$value
   * @return 	boolean
   */
	function validate($value) {
		$valid = true;
		
		foreach ($this->rules as $rule => $options) {
			switch ($rule) {
				case self::RULE_UNIQUE:
					$valid = self::isUnqiue($value, $options);
					break;
					
				case self::RULE_EMAIL:
				    $valid = self::isValidEmail($value, $options);
				    break;
				    
				case self::RULE_DECIMALS:
				    $valid = preg_match(self::PATTERN_DECIMALS, $value) > 0 ? TRUE : FALSE;
				    break;
				    
				case self::RULE_PATTERN:
					$valid = preg_match($options['pattern'], $value) > 0 ? TRUE : FALSE;
					break;

				case self::RULE_SIZE:
					if (isset($options['min'])) {
						if (strlen($value) < $options['min']) $valid = false;
					}
					if (isset($options['max'])) {
						if (strlen($value) > $options['max']) $valid = false;
					}					
					break;
			
				case self::RULE_DATE:
				    if (isset($options['format'])) {
						$valid = self::isValidDate($value, $options['format']);
					}
					else {		
						$valid = self::isValidDate($value);
					}
					break;
					
				case self::RULE_MANDATORY:
					$valid = strlen($value) <> 0;
					break;

				case self::RULE_AMOUNT:
					$valid = $this->isValidAmount($value, $options['decimals']);
					break;
										
				case self::RULE_EVAL:
				    if (isset($options['eval'])) {
					    $statement = '$valid = '.$options['eval'].' ? TRUE : FALSE;';
						eval($statement);
					}
					break;
					
			}
			if (!$valid) $this->exceptions[] = $options['exception'];			
		}
		
		$this->valid = count($this->exceptions) > 0 ? FALSE : TRUE; 
     	return $this->valid;
	}	

	/**
	 * Resets all exceptions
	 * 
	 * @return void
	 */
	function reset() {
		$this->exceptions = array();
	}
}
?>