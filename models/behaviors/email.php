<?php
/**
* Email Behavior attaches the Email Component to the Model's Email property.
* This is useful for sending quick emails based off a model and for testing
* purposes.
*
* @author Nick Baker nick [at] webtechnick.com
* @link http://www.webtechnick.com
* @version 0.1
* @license MIT
*/
App::import('Component', 'Email');
class EmailBehavior extends ModelBehavior {

  function setUp(&$Model, $options = array()){
    $this->options = $options;
    
    $Model->Email =& new EmailComponent();
  }
}
?>