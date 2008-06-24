<?php
/**
 * @package auxo
 * @subpackage controller
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 2007
 * @version $Version$
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
 **/
 
class tx_auxo_constraint {
	// operators
	const NOT_EQUAL       = 'notEqual';
	const EQUAL 		  = 'equal';
	const GREATER         = 'greater';
	const GREATER_EQUAL   = 'greaterEqual';
	const LESS			  = 'less';
	const LESS_EQUAL      = 'lessEqual'; 
	const CUSTOM 	  	  = 'custom';
	
	protected $rules  = array();
	protected $errors = NULL;
	
	public function __construct($a, $b, $message, $function = 'equal') {
		$this->rules[] = array( 
			'a' => $a,
			'b' => $b,
			'function' => $function,
			'message' => $message
		);
	}
	
	public function getErrors() {
		return $this->errors;
	}
	
	public function getValueFor($data, $required) {
		if ($required instanceof tx_auxo_constraint) {
			return $required->isValid($data);	
		}
		return $data->get($required); 
	}
	
	public function isValid($data) {
		if (!$data instanceof tx_auxo_arrayObject) {
			throw new tx_auxo_coreException('no array object given');
		}
		
		foreach ($this->rules as $rule) {
		    $a = $this->getValueFor($data, $rule['a']);
		    $b = $this->getValueFor($data, $rule['b']);					
			if (! call_user_func(array($this, $rule['function']), $a, $b)) {
			    $this->errors[] = $rule['message'];
			}
		}
		
		return $this->errors ? false : true;
	}

	protected function equal($a, $b) {
		return $a == $b;
	}

	protected function notEqual($a, $b) {
		return $a <> $b;
	}

	protected function less($a, $b) {
		return $a < $b;
	}
	
	protected function lessEqual($a, $b) {
		return $a <= $b;
	}
	
	protected function greater($a, $b) {
		return $a > $b;
	}

	protected function greaterEqual($a, $b) {
		return $a >= $b;
	}
	
}



?>