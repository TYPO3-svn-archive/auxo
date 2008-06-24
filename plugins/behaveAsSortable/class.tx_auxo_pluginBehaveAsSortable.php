<?php
/**
 * @package 	auxo
 * @subpackage 	plugins
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 * @copyright 	2007
 * @version 	$WCREV$
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

require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');

/**
 * Query
 *
 * This class represents a model plugin that enables sorting 
 * of data records.
 *
 * @package 	auxo
 * @subpackage 	models
 * @author 		Andreas Horn <Andreas.Horn@extronaut.de>
 */
class tx_auxo_pluginBehaveAsSortable {
	public $sortingFieldname = 'sorting';
	
  /**
   * tx_auxo_pluginBehaveAsSortable::delete()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return
   */
	public function delete($classname, $object) {
		if (!$this->getPosition($object) OR $object->isNew()) {
			return true;
		}
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::moveUp()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return
   */
	public function moveUp($classname, $object) {
		$query = tx_auxo_query();
		$query->addWhere($this->sortingFieldname, TX_AUXO_QUERY::LESS);
		if (($predecessor = $object->selectSingle($query))) {
			$this->swapPosition($classname, $object, $predecessor);
			$object->save();
			$predecessor->save();
		}		
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::moveDown()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return
   */
	public function moveDown($classname, $object) {
		$query = tx_auxo_query();
		$query->addWhere($this->sortingFieldname, TX_AUXO_QUERY::GREATER);
		if (($successor = $object->selectSingle($query))) {
			$this->swapPosition($classname, $object, $successor);
			$object->save();
			$successor->save();
		}
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::moveTop()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return 
   */
	public function moveTop($classname, $object) {
		return $this->moveAt($classname, $object, 1);
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::moveLast()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return
   */
	public function moveLast($classname, $object) {
		$query = tx_auxo_query();
		$query->addSorting($this->sortingFieldname, TX_AUXO_QUERY::DESC);
        if (($last = $object->selectSingle($query))) {
        	$position = $last->getPosition($classname, $last);
			if ($this->getPosition($classname, $object) <> $position) {
                $this->setPosition($classname, $position + 1);
				$object->save();
				$last->save();
			}			
		} 
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::moveAt()
   *
   * @param mixed $classname
   * @param mixed $object
   * @param mixed $position
   * @return 
   */
	public function moveAt($classname, $object, $position) {
		$query = tx_auxo_query();
		$query->addWhere($this->sortingFieldname, $position, TX_AUXO_QUERY::GREATER_EQUAL);
		$query->addSorting($this->sortingFieldname, TX_AUXO_QUERY::DESC);
		// move all existing successors down
		if (($successors = $object->select($query))) {
			// increment number of last record
			$previous = $successors->current()->get($this->sortingFieldname) + 1;
			// set number of predecessor for all following objects
			while ($successors->valid()) {
				$current = $successors->current()->get($this->sortingFieldname);
			    $this->current()->set($this->sortingFieldname, $previous);
			    $this->current()->save();
				$previous = $current;
			    $successors->next();
			} 
		}		
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::insertBefore()
   *
   * @param mixed $classname
   * @param mixed $object
   * @param mixed $successor
   * @return intenger $success
   */
	public function insertBefore($classname, $object, $successor) {
		$position = $this->getPosition($classname, $successor) - 1;
		return $this->insertAt($classname, $object, $position);
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::insertAfter()
   *
   * @param mixed $classname
   * @param mixed $object
   * @param mixed $predecessor
   * @return integer $success
   */
	public function insertAfter($classname, $object, $predecessor) {
		$position = $this->getPosition($classname, $predecessor);
		return $this->insertAt($classname, $object, $position);
	}

  /**
   * tx_auxo_pluginBehaveAsSortable::insertAt()
   *
   * @param mixed $class
   * @param mixed $object
   * @param mixed $position
   * @return
   */
    public function insertAt($class, $object, $position) {
		$query = tx_auxo_query();
		$query->addWhere($this->sortingFieldname, $position, TX_AUXO_QUERY::GREATER);
		$query->addSorting($this->sortingFieldname, TX_AUXO_QUERY::DESC);		
		// move all existing successors down
		if (($successors = $object->select($query))) {
			$successors->append($object);
			// increment number of last record
			$previous = $successors->current()->get($this->sortingFieldname) + 1;
			// set number of predecessor for all following objects
			while ($successors->valid()) {
				$current = $successors->current()->get($this->sortingFieldname);
			    $this->current()->set($this->sortingFieldname, $previous);
			    $this->current()->save();
			    $previous = $current;
			    $successors->next();
			} 
		}
		else {
			$this->insertBottom($classname, $object);
		}		
	}	
	
  /**
   * tx_auxo_pluginBehaveAsSortable::insertTop()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return integer $success
   */
	public function insertTop($classname, $object) {
		return $this->insertAt($classname, $object, 0);
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::insertBottom()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return
   */
	public function insertBottom($classname, $object) {
		$query = tx_auxo_query();
		$query->addSorting($this->sortingFieldname, TX_AUXO_QUERY::DESC);
        $last = $object->selectSingle($query);
        if ($last) {
			$position = $this->getPosition($classname, $last->current());
		} 
		else {
			$position = 1;
		}
		$object->setPosition($classname, $object, $position);
		return $object->save();
 	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::swapPosition()
   *
   * @param string $classname
   * @param mixed $a
   * @param mixed $b
   * @return void
   */
	public function swapPosition($classname, $a, $b) {
		$position = $a->get($this->sortingFieldname);
		$a->set($b->get($this->sortingFieldname));
		$b->set($position);		
	}
	
  /**
   * tx_auxo_pluginBehaveAsSortable::getPosition()
   *
   * @param mixed $classname
   * @param mixed $object
   * @return mixed $position
   */
	public function getPosition($classname, $object) {
		return $object->get($this->sortingFieldname);
	}

  /**
   * tx_auxo_pluginBehaveAsSortable::setPosition()
   *
   * @param mixed $classname
   * @param mixed $object
   * @param mixed $position
   * @return void
   */
	public function setPosition($classname, $object, $position) {
		$object->set($this->sortingFieldname, $position);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_pluginBehaveAsSortable.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_pluginBehaveAsSortable.php']);
}
?>