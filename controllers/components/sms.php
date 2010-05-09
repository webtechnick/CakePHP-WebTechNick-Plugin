<?php
/***************************************************
  * Sms Component
  * 
  * Send SMS messages just like you would the Email component.
  * 
  * @copyright    Copyright 2009, Webtechnick
  * @link         http://www.webtechnick.com
  * @author       Nick Baker
  * @version      1.0
  * @license      MIT
  */
class SmsComponent extends Object {
  
  /***************************************************
    * Load the email component.
    */
  var $components = array('Email');
  
  /***************************************************
    * Associative array of carriers to its email domain.
    * Emails will be sent to number@carrierDomain
    *
    * @var array of carrier domains.
    * @link http://en.wikipedia.org/wiki/SMS_gateway
    * @access public
    */
  var $carrierDomain = array(
    'ATT'           => 'txt.att.net',
    'Boost'         => 'myboostmobile.com',
    'Cellular One'  => 'mobile.celloneusa.com',
    'Cingular'      => 'cingularme.com',
    'Cricket'       => 'sms.mycricket.com',
    'Nextel'        => 'messaging.nextel.com',
    'Sprint'        => 'messaging.sprintpcs.com',
    'Qwest'         => 'qwestmp.com',
    'TMobile'       => 'tmomail.net',
    'Verizon'       => 'vtext.com',
    'Virgin'        => 'vmobl.com'
  );
  
  /***************************************************
    * The from email or number in which to send the text from.
    *
    * @var string of 10 numbers or an email address.
    * @access public
    */
  var $from = null;
  
  /***************************************************
    * The number in which to send the text to.
    *
    * @var string of 10 numbers.
    * @access public
    */
  var $number = null;
  
  /***************************************************
    * The carrier in which to send the text to.
    * @var string of the carrier (Sprint, Verizon, etc..)
    *
    * @access public
    */
  var $carrier = null;
  
  /***************************************************
    * The body text of the SMS message.
    *
    * @var string of the actual text to send
    * @access public
    */ 
  var $text = null;
  
  /***************************************************
    * data and params are the controller data and params
    *
    * @var array
    * @access public
    */
  var $data = array();
  var $params = array();
  
  /***************************************************
    * errors
    * @var array of errors the component comes across.
    * @access public
    */
  var $errors = array();
  
  /***************************************************
    * Initializes FileUploadComponent for use in the controller
    *
    * @param object $controller A reference to the instantiating controller object
    * @return void
    * @access public
    */
  function initialize(&$controller){
    $this->data = $controller->data;
    $this->params = $controller->params;
  }
  
  
  /***************************************************
    * Actually send the SMS.
    *
    * @return boolean true if sms sent, false if missing information
    * @access public
    * @param mixed options ('string of text or array of options (number, text, from, carrier)
    */
  function send($options = array()){
    if(is_string($options)){
      $this->text = $options;
    }
    
    $this->__setupSms($options);
    
    if($this->testSend()){
      $this->Email->to = $this->__buildSmsEmail();
      $this->Email->from = $this->from;
      $this->Email->sendAs = 'text';
      
      $this->Email->send($this->text);
      return true;
    }
    return false;
  }
  
  /***************************************************
    * this function decides if we can send the message or not
    *
    * @return boolean true if it can send the SMS, false if it ran into an error
    * @access public
    */
    function testSend(){
      if($this->__isReady()){
        return true;
      }
      if(!$this->number || strlen($this->number) < 10){
        $this->_error('SMSComponent::number is not set.');
      }
      if(strlen($this->number) < 10){
        $this->_error('SMSComponent::number is too short: must be at least 10 digits long');
      }
      if(!$this->carrier){
        $this->_error('SMSComponent::carrier is not set.');
      }
      if(!array_key_exists($this->carrier, $this->carrierDomain)){
        $this->_error("SMSComponent::carrier -- {$this->carrier} -- is not listed in available SMSComponent::carrierDomain list.");
      }
      if(!$this->text){
        $this->_error('SMSComponent::text is not set.');
      }
      
      return false;
    }
  
  /*************************************************
    * showErrors itterates through the errors array
    * and returns a concatinated string of errors sepearated by
    * the $sep
    *
    * @param string $sep A seperated defaults to <br />
    * @return string
    * @access public
    */
  function showErrors($sep = "<br />"){
    $retval = "";
    foreach($this->errors as $error){
      $retval .= "$error $sep";
    }
    return $retval;
  }
  
  /***************************************************
    * Adds error messages to the component
    *
    * @param string $text String of error message to save
    * @return void
    * @access protected
    */
  function _error($text){
    $message = __($text,true);
    $this->errors[] = $message;
  }
  
  /***************************************************
    * Sets up the class number, carrier, from, and text 
    * based on the options passed in.
    *
    * @return void
    * @access private
    * @param array of options (number, carrier, text)
    */
  function __setupSms($options){
    if(isset($options['number'])){
      $this->number = $options['number'];
    }
    if(isset($options['carrier'])){
      $this->carrier = $options['carrier'];
    }
    if(isset($options['text'])){
      $this->text = $options['text'];
    }
    if(isset($options['from'])){
      $this->from = $options['from'];
    }
  }
  
  /***************************************************
    * Algorythm to deside if we're ready to send an SMS
    *
    * @return boolean true if we're ready, false if not
    * @access private
    */
  function __isReady(){
    if($this->number && $this->carrier && strlen($this->number) >= 10 && array_key_exists($this->carrier, $this->carrierDomain) && $this->text){
      return true;
    }
    return false;
  }
  
  /***************************************************
    * Builds the Sms email to field from the number, carrier, and carrierDomain list
    *
    * @access private
    * @return string of sms email address or null if none found.
    */
  function __buildSmsEmail(){
    if($this->__isReady()){
      return $this->number . "@" . $this->carrierDomain["{$this->carrier}"];
    }
    else {
      return null;
    }
  }
}
?>