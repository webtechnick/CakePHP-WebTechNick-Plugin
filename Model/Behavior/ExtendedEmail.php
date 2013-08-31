<?php
/**
* Email Behavior attaches the Email Component to the Model's Email property.
* This is useful for sending quick emails based off a model and for testing
* purposes.
*
* Usage: 
* var $actsAs = array('WebTechNick.Email');
*
* @author Nick Baker nick[at]webtechnick[dot]com
* @link http://www.webtechnick.com
* @version 0.3
* @license MIT
*/
App::import('Component', 'Email');

class ExtendedEmail extends EmailComponent{

  /**
    * Overwrite the email component's send function
    */
  function send($content = null, $template = null, $layout = null){
    $this->template = null;
    parent::send($content, null, $layout);
  }
  
  /**
    * Overwrite the email component's __renderTemplate function 
    * Use a generic view instead of the controller's
    */
  /*function __renderTemplate($content){
    $View = ClassRegistry::getObject('view');
    $View->layout = $this->layout;
    
    $msg = array();

		$content = implode("\n", $content);

		if ($this->sendAs === 'both') {
			$htmlContent = $content;
			if (!empty($this->attachments)) {
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: multipart/alternative; boundary="alt-' . $this->__boundary . '"';
				$msg[] = '';
			}
			$msg[] = '--alt-' . $this->__boundary;
			$msg[] = 'Content-Type: text/plain; charset=' . $this->charset;
			$msg[] = 'Content-Transfer-Encoding: 7bit';
			$msg[] = '';

			$content = $View->element('email' . DS . 'text' . DS . $this->template, array('content' => $content), true);
			$View->layoutPath = 'email' . DS . 'text';
			$content = explode("\n", str_replace(array("\r\n", "\r"), "\n", $View->renderLayout($content)));
			$msg = array_merge($msg, $content);

			$msg[] = '';
			$msg[] = '--alt-' . $this->__boundary;
			$msg[] = 'Content-Type: text/html; charset=' . $this->charset;
			$msg[] = 'Content-Transfer-Encoding: 7bit';
			$msg[] = '';

			$htmlContent = $View->element('email' . DS . 'html' . DS . $this->template, array('content' => $htmlContent), true);
			$View->layoutPath = 'email' . DS . 'html';
			$htmlContent = explode("\n", str_replace(array("\r\n", "\r"), "\n", $View->renderLayout($htmlContent)));
			$msg = array_merge($msg, $htmlContent);
			$msg[] = '';
			$msg[] = '--alt-' . $this->__boundary . '--';
			$msg[] = '';

			return $msg;
		}

		if (!empty($this->attachments)) {
			if ($this->sendAs === 'html') {
				$msg[] = '';
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: text/html; charset=' . $this->charset;
				$msg[] = 'Content-Transfer-Encoding: 7bit';
				$msg[] = '';
			} else {
				$msg[] = '--' . $this->__boundary;
				$msg[] = 'Content-Type: text/plain; charset=' . $this->charset;
				$msg[] = 'Content-Transfer-Encoding: 7bit';
				$msg[] = '';
			}
		}

		$content = $View->element('email' . DS . $this->sendAs . DS . $this->template, array('content' => $content), true);
		$View->layoutPath = 'email' . DS . $this->sendAs;
		$content = explode("\n", str_replace(array("\r\n", "\r"), "\n", $View->renderLayout($content)));
		$msg = array_merge($msg, $content);

		return $msg;
  }*/
}

class EmailBehavior extends ModelBehavior {

  function setUp(&$Model, $options = array()){
    $this->options = $options;
    
    if(PHP5){
      $Model->Email = new ExtendedEmail();
    }
    else {
      $Model->Email = new ExtendedEmail();
    }
  }
}
?>