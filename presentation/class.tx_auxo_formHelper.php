<?php
/**
 * @package auxo
 * @subpackage view
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
 * Auxo
 * 
 * Class that enables autoloading of all auxo classes.
 * 
 * @package auxo
 * @subpackage view
 * @author Andreas Horn <Andreas.Horn@extronaut.de>
 * @access public
 */

/**
 *  Form Helper
 *
 */
class tx_auxo_formHelper {
	 /**
	   * method		printInputText
	   *
	   * @param unknown_type $id
	   * @param unknown_type $size
	   * @param unknown_type $value
	   */
	  function printInputText($id, $size, $value='', $tooltip='', $className='') {
	  	 $wrapId = $this->addTooltip($id, $tooltip);	  	
	  	 $name = $this->getDesignator().'['.$id.']';
	  	 if ($className) {
	  	 	$classTag = 'class="'.$className.'"';
	  	 }
	  	 printf('<dd id="%s"><input type="text" name="%s" id="%s" size="%d" value="%s" %s /></dd>', $wrapId, $name, $id, $size, $value, $classTag);	  	 
	  }
	  
	  /**
	   * method		printInputTextbox
	   *
	   * @param unknown_type $id
	   * @param unknown_type $cols
	   * @param unknown_type $rows
	   * @param unknown_type $value
	   */
	  function printInputTextbox($id, $cols, $rows, $value='', $tooltip='') {
	  	 $wrapId = $this->addTooltip($id, $tooltip);	  	
	  	 $name = $this->getDesignator().'['.$id.']';
	  	 printf('<dd id="%s"><textarea type="area" name="%s" id="%s" cols="%d" rows="%d">%s</textarea></dd>', $wrapId, $name, $id, $cols, $rows, $value);
	  }

	  /**
	   * method		printInputDate
	   *
	   */

	  function printInputDate($id, $value='', $tooltip='') {
          tx_rlmpdateselectlib::includelib();
	  	  $wrapId = $this->addTooltip($id, $tooltip);	  	
	  	  $name = $this->getDesignator().'['.$id.']';
	  	  $button = tx_rlmpdateselectlib::getInputButton($id, $dateSelectorConf);
	  	  // convert date/timestamp into string
	  	  if ($value) {
	  	     $value = date('%d/%m/%Y', $value);
	  	  }
	  	  printf('<dd id="%s"><input type="text" size="10" name="%s" id="%s" class="%s" />%s', $id, $name, $value, 'tx-exolodges-input-date', $button);
	  }
	  
	  /**
	   * method		printInputAutoComplete
	   *
	   * @param unknown_type $id
	   * @param unknown_type $size
	   * @param unknown_type $url
	   * @param unknown_type $value
	   */
	  function printInputAutoComplete($id, $size, $url, $value='', $tooltip='') {
	  	 $this->printInputText($id, $size, $value, $tooltip);
	  	 $autocompleteId = 'autocomplete_'.$id;
	     $this->htmlCode[] = '<div id="'.$autocompleteId.'" class="autocomplete"></div>';
	     $this->scriptCode[] = 'new Ajax.Autocompleter("'.$id.'", "'.$autocompleteId.'", "'.$url.'", {});';	 
	  }
	  
	  /**
	   * method 	printInputNumber
	   *
	   * @param unknown_type $id
	   * @param unknown_type $low
	   * @param unknown_type $high
	   * @param unknown_type $value
	   */
      function printInputNumber($id, $low, $high, $value='', $tooltip='') {
      	 $length = strlen($high);
      	 $this->printInputText($id, $length, $value, $tooltip, 'tx-exolodges-input-number');
      }
      
      /**
       * method		printInputSelect
       *
       * @param unknown_type $type
       * @param unknown_type $name
       */
	  function printInputSelect($type, $name, $value='', $tooltip='') {
        $data = $this->get($type);
		
        for($data->rewind(); $data->valid(); $data->next()) {
        	$options .= $this->getSelectOption( $data, $data->key() == $value);
        }
		print $this->getSelectStatement($name, $options, $tooltip);
      }
      
      /*
       * method		getSelectOption
       */
      function getSelectOption($data, $selected) {
        if ($selected) {	
      	   return '<option value="'.$data->key().'" selected="selected">'.$data->current().'</option>';	
        }
        else {
      	  return '<option value="'.$data->key().'">'.$data->current().'</option>';	
        }
      }

      /*
       * method		getSelectStatement
       */     
      function getSelectStatement($id, $options) {
	  	 $wrapId = $this->addTooltip($id, $tooltip);	  	
      	 return '<dd id='.$wrapId.'><select name="'.$this->getDesignator().'['.$id.']" id="'.$id.'">'.$options.'</dd></select>';      	
      }
   
      
      /*
       * method		printActionButton
       */
      function printActionButton($name, $title, $actions) {
      	$path = t3lib_div::getFileAbsFileName($this->controller->configurations->get($name));
        $path = substr($path,strlen(PATH_site));
        print $this->renderLink($this->renderImage($path, $title, $title), true, '_button', $actions );
      }
      
      /*
       * method		printFormButton
       */      
      function printFormButton($name, $title, $action) {      	
      	$path = t3lib_div::getFileAbsFileName($this->controller->configurations->get($name));
        $path = substr($path,strlen(PATH_site));	
    	print '<input class="tx_'.$this->getDesignator().'_button" type="image" src="'.$path.'" alt="'.$title.'" onClick="this.form.action="'.$action.'" />';
      }

      /*
       * method		printError
       */
      function printError($message) {
      	if(!($error = $this->get('error'))) {
      		return;
      	}
      	print '<p class="error">'.$message.'</p>';
      }   
      
      /**
       * method		addTooltip
       *
       * @param 	String $id
       * @param 	String $tooltip
       * @return 	String $wrapId
       */
      function addTooltip($id, $tooltip) {
     	$wrapId = $id.'_tip';
      	if ($tooltip) {
	        $script='new Effect.Tooltip("'.$wrapId.'", "'.$tooltip.'", {title:"'.$id.'", className:"tx_exo_lodges_toolTip", offset:{x:100, y:15}});';
 	  	 	$view->addJSlibrary('libraries/tooltip.js');		    	
	        $view->addScriptCode($script);
 	  	 }      	
	  	 return $wrapId;
      }
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_formHelper.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/auxo/class.tx_auxo_formHelper.php']);
}
?>