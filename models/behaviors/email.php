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
* @version 0.2
* @license MIT
*/
App::import('Component', 'Email');

class ExtendedEmail extends EmailComponent{
  /**
    * Force Bypass of the template
    */
  function send($content = null, $template = null, $layout = null){
    parent::send($content, null, $layout);
  }
}

class EmailBehavior extends ModelBehavior {

  function setUp(&$Model, $options = array()){
    $this->options = $options;
    
    $Model->Email =& new ExtendedEmail();
  }
}
?>