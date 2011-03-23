<?php
/**
  * Google helper loads javascript libraries as well as any other
  * special google code for ease of use.
  *
  * Setup
  * 1) obtain an APIKEY from http://code.google.com/apis/ajaxsearch/signup.html
  * 2)create a config/google.php with the following
  * 
  *  $config = array(
  *    'Google' => array(
  *      'apikey' => 'YOUR KEY HERE'
  *    )
  *  );
  * 
  * @version 0.1.1
  * @author Nick Baker
  * @license MIT
  */
class GoogleHelper extends AppHelper {
  /**
    * Load the HTML helper
    */
  var $helpers = array('Html');
  
  /**
    * Current protocol to avoid conflicts of loading http within https
    */
  var $protocol = 'http://';
  
  /**
    * Google loader url
    */
  var $googleLoader = 'www.google.com/jsapi?';
  
  /**
  * Geoloc data by an address
  */
  var $googleMaps = 'maps.google.com/maps/geo?';
  
  /**
    * The configuration api key
    */
  var $apikey = null;
  
  /**
    * Flag if api has been loaded or not.
    */
  var $loadedApi = false;
  
  /**
  * HttpSocket if we need it.
  */
  var $Http = null;
  
  /**
    * Load the APIKEY
    * @param boolean test. If true do not attempt to load apikey
    */
  function __construct($test = false){
  	if(!$test){
			$this->protocol = env('HTTPS') ? 'https://' : 'http://';
			Configure::load('google');
			if(!$this->apikey = Configure::read('Google.apikey')){
				trigger_error('Google Api Key not found.  Please create config/google.php with apikey');
			}
			if($this->apikey){
				$this->googleMaps .=  'key=' . $this->apikey;
				$this->googleLoader .= 'key=' . $this->apikey;
			}
    }
  }
  
  /**
  * Return a geolocation parsed address of address string
  * @param string address fragment
  * @return mixed result of geoloc lookup.
  */
  function geoLoc($address = null){
  	if($address){
  		$this->__loadHttpSocket();
  		$request = $this->protocol . $this->googleMaps;
  		$result = $this->Http->get($request, array('q' => urlencode($address)));
  		$retval = json_decode($result, true);
  		if($retval['Status']['code'] == 200){
  			return $retval;
  		}
  	}
  	return false;
  }
  
  /**
  * Load the HTTP Socket
  */
  function __loadHttpSocket(){
  	if(!$this->Http){
  		App::import('Core','HttpSocket');
  		$this->Http = new HttpSocket();
  	}
  }
  
  /**
    * Loads the required API library before loading other libraries
    * @return HtmlScript
    */
  function api(){
    $this->loadedApi = true;
    return $this->Html->script($this->protocol . $this->googleLoader);
  }
  
  /**
    * Loads a javascript library based on google load api
    *
    * @see http://code.google.com/apis/libraries/devguide.html
    * @param string library
    * - chrome-frame
    * - dojo
    * - ext-core
    * - jquery
    * - jqueryui
    * - mootools
    * - prototype
    * - scriptaculous
    * - swfobject
    * - yui
    * - webfont
    * @version mixed version number (1 by default)
    * @return HtmlScriptBlock
    */
  function load($library, $version = 1){
    $retval = $this->__loadApi();
    $retval .= $this->Html->scriptBlock("google.load('$library','$version')");
    return $retval;
  }
  
  /**
    * Loads the script tag for a library,
    * this is best guess from documentation, not all libraries will work
    * @param string library
    * @param mixed version must be exact version
    * @param array of options to pass into HtmlHelper::Script
    * @return HtmlScript
    */
  function script($library, $version = 1, $options = array()){
    $retval = $this->__loadApi();
    $retval .= $this->Html->script("{$this->protocol}ajax.googleapis.com/ajax/libs/$library/$version/$library.js", $options);
    return $retval;
  }
  
  /**
    * Append the API loader to the script unless its already loaded.
    * @return mixed HtmlScript or empty string
    */
  function __loadApi(){
    return !$this->loadedApi ? $this->api() : "";
  }
}
?>