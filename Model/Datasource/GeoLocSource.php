<?php
/**
* GeoLocation datasource.
* @author Nick Baker <nick[at]webtechnick[dot]com>
* @version 1.0
* @license MIT
*
*
* Create a datasource in your config/database.php

var $geoloc = array(
	'datasource' => 'WebTechNick.GeoLocSource',
	'server'     => 'geobyte', //or hostip
	'cache'      => true, //or false, if false a call will be made every time.
	'engine'     => 'default' //Caching config key, default engine,
	'tries'      => '2'
);


App::import('Core','ConnectionManager');
$GeoLoc = ConnectionManager::getDataSource('geoloc');

//GeoLocation data by IP address
$data = $GeoLoc->byIp();
$data = $GeoLoc->byIp('127.0.0.1', array('cache' => false, 'server' => 'hostip'));

//GeoLocation data by address string
$address = $GeoLoc->address('90210', array('cache' => false));

*/
App::uses('HttpSocket', 'Network/Http');
App::uses('CakeSession', 'Model/Datasource');
//App::import('Vendor', 'geoip2.phar');
require_once(APP.'Vendor'. DS . 'geoip2.phar');
use GeoIp2\Database\Reader;
class GeoLocSource extends DataSource {

	/**
	* Description of datasource
	* @access public
	*/
	public $description = "Geolocation Data Source";

	/**
	* Servers to use for geolocation based on IP
	* @access public
	*/
	public $servers = array(
		'hostip' => "http://api.hostip.info/?ip=",
		'geobyte' => "http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress=",
		'maxmind' => 'maxmind?ip='
	);

	/**
	* Google maps used for addres based geolocation lookup
	* @access public
	*/
	public $googleMaps = 'http://maps.googleapis.com/maps/api/geocode/json';

	/**
	* HttpSocket object
	* @access public
	*/
	public $Http = null;

	/**
	* Requests Logs
	* @access private
	*/
	private $__requestLog = array();


	/**
	* Load the HttpSocket
	*/
	public function __construct($config = array()) {
		$this->Http = new HttpSocket();
		$config = array_merge(
			array(
				'server' => 'infosniper',
				'cache' => true,
				'engine' => 'default'
			),
			$config
		);
		parent::__construct($config);
	}

	/**
	* Takes an address and returns geolocation data based on it.
	* @param string address fragment
	* @return mixed result of geolocation from google
	*/
	public function byAddress($address = null, $options = array()) {
		$options = array_merge(
			$this->config,
			$options
		);

		if ($address) {
			$cache_key = "geoloc_" . Inflector::slug($address);
			if ($options['cache'] && $cache = Cache::read($cache_key, $options['engine'])) {
				return $cache;
			}

			$request = $this->googleMaps . '?address=' . urlencode($address) . '&sensor=false';
			$this->__requestLog[] = $request;
			try {
				$result = json_decode($this->Http->get($request), true);
			} catch (Exception $e) {
				return false;
			}
			$retval = array(
				'google' => $result
			);
  		if ($result['status'] == 'OK') {
  			foreach ($result['results'] as $placemark) {
  				$array = array(
  					'address' => $placemark['formatted_address'],
  					'lat' => $placemark['geometry']['location']['lat'],
  					'lon' => $placemark['geometry']['location']['lng'],
  					'state' => '',
  					'city' => '',
  					'country' => '',
  					'zip' => '',
  				);
  				//Get Country, City, State from result.
  				foreach ($placemark['address_components'] as $address_component) {
  					if (in_array('locality', $address_component['types'])) {
  						$array['city'] = $address_component['long_name'];
  					}	elseif (in_array('administrative_area_level_1', $address_component['types'])) {
  						$array['state'] = $address_component['short_name'];
  					}	elseif (in_array('postal_code', $address_component['types'])) {
  						$array['zip'] = $address_component['short_name'];
  					}	elseif (in_array('country', $address_component['types'])) {
  						$array['country'] = $address_component['short_name'];
  					}
  				}
  				$retval['results'][] = $array;
  			}

  			if ($options['cache']) {
					if (!Cache::write($cache_key, $retval, $options['engine'])) {
						$this->log("Error write cache geo_loc cache: $cache_key engine: {$options['engine']}");
					}
				}
  			return $retval;
  		}
  	}
  	return false;
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
	*  - engine boolean (default true) will check cache first before making the call
	*  - tries int (default 2) if empty result, will not cache empty results unless it's up to X tries.
	* @return mixed array of results or null
	*/
	public function byIp($ip = null, $options = array()) {
		$options = array_merge(
			$this->config,
			$options
		);

		$ip = ($ip) ? $ip : $this->getIp();
		$cache_key = "geoloc_" . Inflector::slug($ip);

		if ($options['cache'] && $cache = Cache::read($cache_key, $options['engine'])) {
			return $cache;
		}

		if (!key_exists($options['server'], $this->servers)) {
			$options['server'] = 'geobyte';
		}

		$request = $this->servers[$options['server']] . $ip;
		$this->__requestLog[] = $request;

		switch($options['server']){
			case 'hostip':
				App::uses('Xml', 'Utility');
				$retval = $this->Http->get($request);
				$retval = Set::reverse(new Xml($retval));
				break;
			case 'maxmind':
				try {
					$reader = new Reader( APP . 'Vendor' . DS . 'GeoIP2-City-North-America.mmdb');
					$retval = $reader->city($ip);
				} catch(Exception $e) {
					$this->log("Error reading maxmind database with IP: $ip. Message: " . $e->getMessage());
					$retval = array();
				}
				break;
			default : //geobyte
				$retval = get_meta_tags($request);
				break;
		}

		$retval = $this->parseResult($retval, $options['server']);

		if ($options['cache']) {
			if (array_filter($retval) || $this->itterateTries($ip) > $options['tries']) {
				if (!Cache::write($cache_key, $retval, $options['engine'])) {
					$this->log("Error write cache geo_loc cache: $cache_key engine: {$options['engine']}");
				}
			}
		}

		return $retval;
	}

	/**
	* Itterate Tries based on IP
	*/
	public function itterateTries($ip = null){
		$key = 'GeoLoc.tries_' . Inflector::slug($ip);
		if(!CakeSession::check($key)){
			CakeSession::write($key, 1);
		} else {
			CakeSession::write($key, CakeSession::read($key) + 1);
		}
		return CakeSession::read($key);
	}

	/**
	* Prase the result based on server
	* @param mixed results of find
	* @param string server called from
	* @return array of results parsed so you have at least city, state, and country in return key
	*/
	public function parseResult($result, $server) {
		$retval = array(
			'zip' => null,
			'city' => null,
			'state' => null,
			'country' => null
		);
		if ($server == 'hostip') {
			if (isset($result['HostipLookupResultSet']['FeatureMember']['Hostip']) && $result['HostipLookupResultSet']['FeatureMember']['Hostip']['name'] != '(Private Address)') {
				list($city,$state) = explode(",",$result['HostipLookupResultSet']['FeatureMember']['Hostip']['name']);
				$retval['city'] = trim($city);
				$retval['state'] = trim($state);
				$retval['country'] = trim($result['HostipLookupResultSet']['FeatureMember']['Hostip']['countryAbbrev']);
			}
		}
		if ($server == 'maxmind') {
			//Result is an ojbect.
			if ($city = $result->city->name) {
				$retval['city'] = trim($city);
			}
			if ($zip = $result->postal->code) {
				$retval['zip'] = trim($zip);
			}
			if ($state = $result->mostSpecificSubdivision->isoCode) {
				$retval['state'] = trim($state);
			}
			if ($country = $result->country->isoCode) {
				$retval['country'] = trim($country);
			}
		}
		if ($server == 'geobyte') {
			if (isset($result['city']) && isset($result['regioncode']) && isset($result['internet'])) {
				$retval['city'] = trim($result['city']);
				$retval['state'] = trim($result['regioncode']);
				$retval['country'] = trim($result['internet']);
			}
		}

		return array_merge((array)$result, $retval);
	}

	/**
	* Play nice with the DebugKit
	* @param boolean sorted ignored
	* @param boolean clear will clear the log if set to true (default)
	*/
	public function getLog($sorted = false, $clear = true) {
		$log = $this->__requestLog;
		if($clear){
			$this->__requestLog = array();
		}
		return array('log' => array(),'count' => count($log), 'time' => 'Unknown');
	}

	/**
	* Returns the server IP
	* @return string of incoming IP
	*/
	public function getIp() {
		$check_order = array(
			'HTTP_CLIENT_IP', //shared client
			'HTTP_X_FORWARDED_FOR', //proxy address
			'REMOTE_ADDR', //fail safe
		);

		foreach ($check_order as $key) {
			if (isset($_SERVER[$key]) && !empty($_SERVER[$key])) {
				return $_SERVER[$key];
			}
		}
	}
}