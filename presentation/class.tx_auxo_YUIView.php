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
 
class tx_auxo_YUIview extends tx_auxo_view {

	/**
	 * Sets a template path
	 *
	 * @param string $templatePath
	 */
	public function setTemplatePath($templatePath) {
		$this->templatePath = $templatePath;
	}
	
	/**
	 * Returns the name of a template path
	 *
	 * @return string $templatePath
	 */
	public function getTemplatePath() {
		return $this->templatePath;
	}
		
	/**
	 * Sets a template file
	 *
	 * @param string $template
	 */
	public function setTemplate($template) {
		$this->template = $template . '.php';
		$this->setTemplatePath($this->findTemplatePath());
	}
	
	/**
	 * Returns the name of a template file
	 *
	 * @return string $template
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Render the PHP template and return the output as string.
	 *
	 * @return	string		typically an (x)html string
	 */
	public function render() {
		$fullpath = $this->getTemplatePath() . '/' . $this->getTemplate();
		
		if (!is_readable($fullpath)) {
			throw new tx_auxo_presentationException(sprintf('Template file %s is missing', $fullpath));
		}
		
		require_once($fullpath);
		$templateClassName = basename($this->getTemplate(), '.php');
		
		if (!class_exists($templateClassName)) {
			throw new tx_auxo_presentationException(sprintf('Template file %s does not class %s', $this->getTemplate(), $templateClassName));
		}
				
		$instance = new $templateClassName();

		if (!$instance instanceof tx_auxo_aui_template) {
			throw new tx_auxo_presentationException(sprintf('Class %s->build() does not return container', get_class($this)));
		}
		
		$instance->exchangeArray($this->getData());
		$container = $instance->build();
		
		if (!$container || !is_object($container)) {
			throw new tx_auxo_presentationException('no container returned by build()');
		}

		return $container->render();
	}
}
?>