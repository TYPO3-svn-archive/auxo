<?php
/**
 * @package auxo
 * @subpackage presentation
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
 
class tx_auxo_aui_gridLayout extends tx_auxo_aui_layout {
	/**
	 *
	 */
	
	const	GRID_LAYOUT = 'grid-layout';
	
	public function __construct($rows, $columns) {
		$this->rows = $rows;
		$this->columns = $columns;
		$this->type = self::GRID_LAYOUT;
	}
	
	public function setRows($rows) {
		$this->rows = $rows;
	}

	public function setColumns($columns) {
		$this->rows = $columns;
	}
	
	public function render(tx_auxo_aui_renderer $renderer, $items) {		
		$content = '';
		$row = 1;
		$column = 1;
		$index = 0;
		$placeholder = new tx_auxo_aui_null();

		while ($row <= $this->rows) {
			$column = 1;
			$renderedColumns = '';
			while ($column <= $this->columns) {
			    $item = $items[$index++];	
				if (!isset($item)) {
					$item = $placeholder;
				}
				if ($item->getType() == tx_auxo_aui_hiddenField::HIDDEN_FIELD) {
					continue;	
				}
				$renderedColumns .= $this->renderColumn($renderer, $item, $column++);		
			}

			$content .= $this->renderRow($renderer, $renderedColumns, $row++);				
		}
		return $renderer->renderTag($this, 'table', array('class' => 'tx-auxo-aui-'. $this->type), $content);
	}	

	/**
	 * Renders a single column 
	 *
	 * @param object 	$item 	control object
	 * @param int		$column	column number
	 * @return string	$content rendered column as string
	 */
	protected function renderColumn(tx_auxo_aui_renderer $renderer, $item, $column) {
		$options['class'] = array('tx-auxo-aui-grid-column');
		$options['class'][] = $column % 2 == 0 ? 'tx-auxo-aui-grid-column-even' : 'tx-auxo-aui-grid-column-odd';	
		if ($column == 1) $options['class'][] = 'tx-auxo-aui-grid-column-first';
		if ($column == $this->columns) $options['class'][] = 'tx-auxo-aui-grid-column-last';
		
		return $renderer->renderTag($this, 'td', $options, $item->render($renderer));
	}

	/**
	 * Renders a single row
	 *
	 * @param string 	$content	content be be enclosed
	 * @param int		$row		row number
	 * @return string	$content	rendered row as string
	 */
	protected function renderRow(tx_auxo_aui_renderer $renderer, $content, $row) {
	    $options['class'] = array('tx-auxo-aui-grid-row');				
		$options['class'][] = $row % 2 == 0 ? 'tx-auxo-aui-grid-row-even' : 'tx-auxo-aui-grid-row-odd';
		if ($row == 1) $options['class'][]= 'tx-auxo-aui-grid-row-first';
		if ($row == $this->rows) $options['class'][] = 'tx-auxo-aui-grid-row-last';
		
		return $renderer->renderTag($this, 'tr', $options, $content);
	}
}
?>