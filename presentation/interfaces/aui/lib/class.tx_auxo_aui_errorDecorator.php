<?php
class tx_auxo_aui_errorDecorator {

	protected $origin = NULL;
	protected $errors;
	
	public function __construct($origin, $errors) {
		$this->origin = $origin;
		$this->errors = $errors;	
	}
	
	/**
	 * Renders and decorates a control with errors messages
	 *
	 * @return string $content decorated content
	 */
	public function render() {
		$errorTags = '';
		foreach ($this->errors as $error) {
			$errorTags .= tx_auxo_aui_toolbox::renderTag($this->origin, 'div', array('class' => 'tx-auxo-aui-input-error'), $error);
		}
		return $errorTags . $this->origin->render();
	}
	
	public function __call($method, $parameters) {
		return call_user_func_array(array($this->origin, $method), $parameters);
	}
}
?>