<?php
/**
 * @package auxo
 * @subpackage prensentation
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
 
require_once( PATH_t3lib . 'class.t3lib_htmlmail.php');
/**
 * Mailer
 * 
 * This classes is a wrapper of t3lib_HTMLmail and offers several setters
 * and translation of subject, plain and html content.
 *
 */
class tx_auxo_mailer extends t3lib_HTMLmail {

	protected $placeholders = array();
	protected $translator   = NULL;
	
	/**
	 * Creates and Instance of this mailer
	 *
	 * @param string $languagePath path to language file for translations
	 */
	public function __construct($languagePath='') {
		parent::t3lib_htmlmail();
		$this->start();
		if ($languagePath) {
		   $this->translator = new tx_lib_translator();
		   $this->translator->setPathToLanguageFile($languagePath);		
		}
	}

	/**
	 * Sets an array of placeholders and values which has to be
	 * substituted after translation in mail subject or body.
	 *
	 * @param array $placeholder
	 * @return void
	 */
	public function setPlaceholder($placeholders) {
		$this->placeholders = $placeholders;
	}
	
	/**
	 * Sets your organisation
	 *
	 * @param string $organisation
	 */
	public function setOrganisation($organisation) {
		$this->organisation = $organisation;
	}

	/**
	 * Sets senders name and email adress
	 *
	 * @param string $name sender name
	 * @param string $email sender email
	 */
	public function setSender($name, $email) {
		$this->from_email = $email;
		$this->from_name = $name;
	}
	
	/**
	 * Set Recipients as comma separated string that have to
	 * get a copy of this mail
	 *
	 * @param string $recipientCopy comma separated list of recipients
	 */
	public function setRecipientCopy($recipientCopy) {
		$this->recipient_copy = $recipientCopy;
	}
	
	/**
	 * Sets default reply to name and email
	 *
	 * @param string $name name to reply to
	 * @param string $email email to reply to
	 */
	public function setReplyTo($name, $email) {
		$this->replyto_email = $email;
		$this->replyto_name = $name;
	}
	
	/**
	 * Sets a plain string message for this mail that is 
	 * translated if applicable.
	 *
	 * @param string $content plain text message
	 */
	public function setPlainContent($content) {
		$this->addPlain($this->getTranslatedString($content));
	}
	
	/**
	 * Sets a HTML content as message for this mail that 
	 * is translated if applicable.
	 *
	 * @param string $content HTML content
	 */
	public function setHTMLContent($content) {
		$this->addHTML($this->getTranslatedString($content));
	}
	
	/**
	 * Sets a mail subject which is translated if applicable
	 *
	 * @param string $subject subject of this mail
	 */	
	public function setSubject($subject) {
		$this->subject = $this->getTranslatedString($subject);
	}

	/**
	 * Defines the priority for this mail
	 *
	 * @param int $priority mail priority
	 */
	public function setPriority($priority) {
		$this->priority = $priority;
	}

	/**
	 * Translates a given string
	 *
	 * @param string $content text that has to be translated
	 * @return string $content translated text
	 */
	private function getTranslatedString($content) {
		debug($content);
		$result = $this->translator ? $this->translator->translate($content) : $content;
		debug($result);
		return count($this->placeholders) ? $this->substitutePlaceholder($result) : $result;
	}
	
	/**
	 * Substitute an array of placeholders with its content in a string
	 *
	 * @param string $content
	 * @param array $placeholders
	 * @return string $content string with replaced placeholders
	 */
	private function substitutePlaceholder($content) {
		foreach ($this->placeholders as $marker => $replace) {
			$identifier = '###' . $marker . '###';
			$substituted = str_replace($identifier, $replace, $content);
			$content = $substituted;
		}
		
		return $substituted;
	}
	
}
?>