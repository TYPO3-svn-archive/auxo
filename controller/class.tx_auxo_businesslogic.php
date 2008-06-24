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
 **/
 

/**
 * Businesslogic
 *
 * This class represents a certain business logic which can be
 * validated against a given model object. It could be understand as a
 * collection of validators that should be executed with current 
 * values of an given client object. It encapsulates a set or subset 
 * in one class perfectly and therefore can be flexible and might be
 * reused in different contextes. Moreover, business logics might be nested.
 *
 * Just keep DRY (<b>D</b>on't <b>R</b>epeat <b>Y</b>ourself) and implement
 * a certain business logic only once.
 *
 * Example:
 *
 * $logic = new tx_auxo_businesslogic();
 * $logic->set('age', $AgeValidator);
 * $logic->set('email', new tx_auxo_validator(TX_AUXO_VALIDATOR::RULE_EMAIL, 'bad email address'));
 * ...
 * $logic->set('iban', $myValidator);
 * 
 * $person = tx_auxo_models_person();
 * ...
 * if (!$logic->validate($person)) {
 * 	   foreach($logic->exceptions() as $exception) {
 *       echo $exception;
 * }
 *
 * To implement a reusable businesslogic just create a sub class add in 
 * your construct method all validators that should be used. Moreover, 
 * business logic might be used in model classes to implement automated
 * object validation (see tx_auxo_modelbase).
 *
 * @package auxo
 * @subpackage models
 * @author Andreas Horn
 * @access public
 */
 
class tx_auxo_businesslogic extends tx_auxo_arrayObject {

    protected $exceptions = array();
	protected $constraints = array();
		
	/**
	 * Add a constraint to this business logic
	 *
	 * @param object $constraint
	 */
	public function addConstraint($constraint) {
		$this->constraints[] = $constraint;
	}
	
  /**
   * tx_auxo_businesslogic::validate()
   *
   * @param mixed $data
   * @return boolean $valid
   */
	public function validate($data) {
	   if (!$data instanceof tx_auxo_arrayObject) {
	   	   throw new tx_auxo_coreException('no instance of array object provided');
	   }
	   $iterator = $this->getIterator();
	   while($iterator->valid()) {
	   	  if ($data->has($iterator->key())) {
	   	  	 $validator = $iterator->current();
	   	  	 $validator->reset();
			 if (!$validator->validate($data->get($iterator->key()))) {
				$this->exceptions[$iterator->key()] = $validator->exceptions(); 
			 }
		  }	
		  $iterator->next();
	   }

	   if (count($this->exceptions) > 0) {
	   	  return false;
	   }
	   
	   foreach ($this->constraints as $constraint) {
	   	   if (!$constraint->isValid($data)) {
	   	       $this->exceptions = array_merge($this->exceptions, $constraint->getErrors());
	   	   }
	   }

	   return count($this->exceptions) ? false : true;	   
	}
  /**
   * tx_auxo_businesslogic::getErrors()
   *
   * @return array raised exceptions as text array
   */
	public function exceptions() {
		return $this->exceptions;
	}

  /**
   * tx_auxo_businesslogic::reset()
   *
   * @return void
   */
	function reset() {
		$this->exceptions = array();
	}
	
  /**
   * tx_auxo_businesslogic::isValid()
   *
   * @return boolean returns result of last validation
   */
	function isValid() {
      	return count($this->exceptions) > 0? false : true;		
	}
}
?>