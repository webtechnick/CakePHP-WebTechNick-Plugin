<?php
/**
* GeoLocation datasource.
* @author Nick Baker <nick[at]webtechnick[dot]com>
* @version 0.3
* @license MIT
*
*
* Create a datasource in your config/database.php

var $geoloc = array(
	'datasource' => 'WebTechNick.GeoLocSource',
	'server'     => 'geobyte', //or hostip
	'cache'      => true, //or false, if false a call will be made every time.
	'engine'     => 'default' //Caching config key, default engine
);


App::import('Core','ConnectionManager');
$GeoLoc = ConnectionManager::getDataSource('geoloc');
$data = $GeoLoc->data();
$data = $GeoLoc->data('127.0.0.1', array('cache' => false, 'server' => 'hostip'));

*/
App::import('Core','HttpSocket');
class GeoLocSource extends DataSource {
	
	/**
	* Description of datasource
	* @access public
	*/
	var $description = "Geolocation Data Source";
	
	/**
	* Servers to use for geolocation based on IP
	* @access public
	*/
	var $servers = array(
		'hostip' => "http://api.hostip.info/?ip=",
		'geobyte' => "http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress="
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
		$config = array_merge(
			array(
				'server' => 'geobyte',
				'cache' => true,
				'engine' => 'default'
			),
			$config
		);
		parent::__construct($config);
	}
	
	/**
	* Takes an IP and returns geolocation data using either hostip or geobyte.
	* configuratble either in the config/database.php or on the fly. Results 
	* will be cached unless otherwise specified
	*
	* @param string ip
	* @param array of options
	*  - server (hostip|geobyte) geobyte default
	*  - cache boolean (default true) will check cache first before making the call
	*  - engone boolean (default true) will check cache first before making the call
	* @return mixed array of results or null
	*/
	function data($ip = null, $options = array()){
		$options = array_merge(
			$this->config,
			$options
		);
		
		$ip = ($ip) ? $ip : $this->getIp();
		$cache_key = "geoloc_" . Inflector::slug($ip);
		
		if($options['cache'] && $cache = Cache::read($cache_key, $options['engine'])){
			return $cache;
		}
		
		if(!key_exists($options['server'], $this->servers)){
			$options['server'] = 'geobyte';
		}

		$request = $this->servers[$options['server']] . $ip;
		$this->__requestLog[] = $request;
		
		switch($options['server']){
			case 'hostip':
				App::import('Core','Xml');
				$retval = $this->Http->get($request);
				$retval = Set::reverse(new Xml($retval));
				break;
			default : //geobyte
				$retval = get_meta_tags($request);
				break;
		}
		
		$retval = $this->parseResult($retval, $options['server']);
		
		if($options['cache']){
			if(!Cache::write($cache_key, $retval, $options['engine'])){
				$this->log("Error write cache geo_loc cache: $cache_key engine: {$options['engine']}");
			}
		}
		
		return $retval;
	}
	
	/**
	* Prase the result based on server
	* @param mixed results of find
	* @param string server called from
	* @return array of results parsed so you have at least city, state, and country in return key
	*/
	function parseResult($result, $server){
		$retval = array(
			'city' => null,
			'state' => null,
			'country' => null
		);
		if($server == 'hostip'){
			if(isset($result['HostipLookupResultSet']['FeatureMember']['Hostip'])){
				list($city,$state) = explode(",",$result['HostipLookupResultSet']['FeatureMember']['Hostip']['name']);
				$retval['city'] = $city;
				$retval['state'] = $state;
				$retval['country'] = $result['HostipLookupResultSet']['FeatureMember']['Hostip']['countryAbbrev'];
			}
		}
		if($server == 'geobyte'){
			if(isset($result['city']) && isset($result['regioncode']) && isset($result['internet'])){
				$retval['city'] = $result['city'];
				$retval['state'] = $result['regioncode'];
				$retval['country'] = $result['internet'];
			}
		}
		
		return array_merge($result, $retval);
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
	
	/**
	* Returns the server IP
	* @return string of incoming IP
	*/
	function getIp(){
		$check_order = array(
			'HTTP_CLIENT_IP', //shared client
			'HTTP_X_FORWARDED_FOR', //proxy address
			'REMOTE_ADDR', //fail safe
		);
		
		foreach($check_order as $key){
			if(isset($_SERVER[$key]) && !empty($_SERVER[$key])){
				return $_SERVER[$key];
			}
		}
	}
	
}
?>