<?php
/**
* ReCaptcha Component, verify the validity
*
* Setup
* 1) Goto recaptcha.net and register
* 2) Create a config/recaptcha.php with the following
*
* $config = array(
*   'recaptcha' => array(
*     'public_key' => 'YOUR PUBLIC KEY',
*     'private_key' => 'YOUR PRIVATE KEY',
*   )
* );
*
* @version 1.1
* @author Nick Baker
* @license MIT
*/
class RecaptchaComponent extends Component {
  
  /**
	* Public Key
	*/
	var $public_key = null;
	
	/**
	* Private key
	*/
	var $private_key = null;
	
	/**
	* Load the private and public key from the configuration file
	*/
	function initialize(&$Controller, $settings = array()){
		$this->Controller = $Controller;
		if(empty($settings)){
			Configure::load('recaptcha');
			$this->_set(Configure::read('recaptcha'));
		}
		else {
			$this->_set($settings);
		}
	}
	
	/**
	* Verify a captcha was sent and that it is valid
	* @return boolean
	*/
	function isValid(){
		App::import('Vendor','WebTechNick.recaptchalib');
		if(isset($this->Controller->params['form']['recaptcha_response_field'])){
			$response = recaptcha_check_answer(
				$this->private_key,
				$_SERVER['REMOTE_ADDR'],
				$this->Controller->params['form']['recaptcha_challenge_field'],
				$this->Controller->params['form']['recaptcha_response_field']
			);
			return $response->is_valid;
		}
		return false;
	}
}
?>