<?php

/**
 * Test Schema 
 *
 * @package auxo
 * @subpackage testcases
 * @author Andreas Horn
 * @copyright 2007
 * @version $Id$
 * @access public
 */

require_once(t3lib_extMgm::extPath('auxo') . 'class.tx_auxo.php');


class tx_auxo_schema_example extends tx_auxo_schemabase {
	
	public $extension = 'tx_auxo';
	
	public function __construct() {
		/** add your tables here **/		
		$this->addTable('tx_auxo_people');
		$this->addTable('tx_auxo_group');
		$this->addTable('tx_auxo_address');
		$this->addTable('tx_auxo_project');
		$this->addTable('tx_auxo_notes');
		
		/** add your associations here **/
		$this->associate('tx_auxo_people',  self::BELONGS_TO, 'tx_auxo_group');
		$this->associate('tx_auxo_people',  self::HAS_ONE, 'tx_auxo_address');
	    $this->associate('tx_auxo_people',  self::HAS_AND_BELONGS_TO_MANY, 'tx_auxo_project', array('join_table' => 'tx_auxo_people_project'));
		$this->associate('tx_auxo_address', self::BELONGS_TO, 'tx_auxo_people');
		$this->associate('tx_auxo_group',   self::HAS_MANY, 'tx_auxo_people');
	    $this->associate('tx_auxo_project', self::HAS_AND_BELONGS_TO_MANY, 'tx_auxo_people', array('join_table' => 'tx_auxo_people_project'));
		$this->associate('tx_auxo_project', self::HAS_MANY, 'tx_auxo_notes', array('inline' => true));
		
		/** behaviour plugins **/
		$this->behaveLike('tx_auxo_project', 'behaveAsRateable');
		$this->behaveLike('tx_auxo_notes', 'behaveAsSortable');
	}
}

?>