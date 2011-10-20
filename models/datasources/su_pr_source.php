<?php
/**
* Su.pr (StumbleUpon datasource)
* @author Nick Baker <nick[at]webtechnick[dot]com>
* @version 0.1
* @license MIT
* @resource http://www.stumbleupon.com/help/su-pr-api
* @register http://su.pr/
*
* Create a datasource in your config/database.php

var $su_pr = array(
	'api_key'    => 'YOUR_API_KEY',
	'username'   => 'YOUR_USERNAME',
);


App::import('Core','ConnectionManager');
$SuPr = ConnectionManager::getDataSource('su_pr');

//Post a message
$result = $SuPr->post("Message");
$result = $SuPr->post("Message", array(
	'services' => array('twitter','facebook'),
	'timestamp' => '499162800' // Unix timestamp
));

*/
App::import('Core','HttpSocket');
class SuPrSource extends DataSource {
	
	/**
	* Description of datasource
	* @access public
	*/
	var $description = "Su.pr Data Source";

	/**
	* Rest API url
	* @access public
	*/
	var $supr_api = 'http://su.pr/api/';
	
	/**
	* Error code to readable message
	* @access public
	*/
	var $errors = array(
		'0' => 'Success',
		'203' => 'Su.pr Authentication failed',
		'1204' => 'Invalid su.pr Hash',
		'1206' => 'Invalid URL',
		'1301' => 'Twitter Authentication Failed',
		'1302' => 'Facebook Authentication Failed',
		'1303' => 'Invalid timestamp',
		'1304' => 'Your message must be 140 character or less',
	);
	
	/**
	* HttpSocket object
	* @access public
	*/
	var $Http = null;
	
	/**
	* Requests Logs
	* @access private
	*/
	var $__requestLog = array();
	
	
	/**
	* Load the HttpSocket
	*/
	function __construct($config = array()){
		$this->Http = new HttpSocket();
		parent::__construct($config);
	}
	
	/**
	* Post a message to stumbleupon
	* @param message text
	* @param array of options
	* - services array
	* - timestamp
	* @example $SuPr->post("Message", array('services' => array('twitter','facebook')));
	* @return result
	*/
	function post($message, $options = array()){
	  if(!$message){
	  	return false;
	  }
	  $options = array_merge(array(
	  	'msg' => $message
	  ),$options);
		return $this->__request('post', $options);
	}
	
	/**
	* Shorten a url using the REST API and login tracking
	* @param string url to shorten
	* @return result 
	*/
	function shorten($url = null){
		if(!$url){
			return false;
		}
		return $this->__request('shorten', array('longUrl' => $url)); 
	}
	
	/**
	* Simple shorten, no need for login/key, just the url
	* @param url to shorten
	* @return simple string back
	*/
	function simpleShorten($url = null){
		return $this->Http->get($this->supr_api . "simpleshorten?url=$url");
	}
	
	/**
	* Make the request and parse the result.
	* @param string method
	* @param array of options to post
	* @return array result
	*/
	function __request($method, $options = array()){
		$options = array_merge(array(
			'apiKey' => $this->config['api_key'],
			'login' => $this->config['username']
		), $options);
		$retval = $this->Http->get($this->supr_api . $method, $options);
		$this->__requestLog[] = $this->Http->request;
		return json_decode($retval, true);
	}
	
	/**
	* Play nice with the DebugKit
	* @param boolean sorted ignored
	* @param boolean clear will clear the log if set to true (default)
	*/
	function getLog($sorted = false, $clear = true){
		$log = $this->__requestLog;
		if($clear){
			$this->__requestLog = array();
		}
		return array('log' => $log, 'count' => count($log), 'time' => 'Unknown');
	}
}
?>