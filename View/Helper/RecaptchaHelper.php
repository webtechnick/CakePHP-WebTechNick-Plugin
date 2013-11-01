<?php
/**
* ReCaptcha Helper
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
class RecaptchaHelper extends AppHelper{
	
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
	function __construct(View $View, $settings = array()){
		if(empty($settings)){
			Configure::load('recaptcha');
			$this->_set(Configure::read('recaptcha'));
		}	else {
			$this->_set($settings);
		}
	}
	
	/**
	* Show the captcha
	* @param array of options to load via javascript 
	*/
	function show($options = array()){
		App::import('Vendor','WebTechNick.recaptchalib');
		return recaptcha_get_html($this->public_key);
	}
}