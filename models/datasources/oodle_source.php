<?php
/**
 * A CakePHP datasource for Oodle API.
 *
 * Create a datasource in your config/database.php
 *  var $oodle = array(
 *    'datasource' => 'WebTechNick.OodleSource',
 *    'apikey' => 'PUBLIC KEY',
 *  ); 
 *
 * @version 0.1
 * @author Nick Baker <nick@webtechnick.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Xml');
class OodleSource extends DataSource{
  /**
    * Description of datasource
    * @access public
    */
  var $description = "Oodle Data Source";
  
  /**
    * Base url
    */
  var $url = 'http://api.oodle.com/api/v2/';
  
  /**
    * defaultOutput, json.
    * xml/json
    */
  var $_defaultOutput = 'json';
  
  /**
    * Query array
    * @access public
    */
  var $query = null;
  
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
    * Append HttpSocket to Http
    */
  function __construct($config) {
    parent::__construct($config);
    App::import('HttpSocket');
    $this->Http = new HttpSocket();
  }
  
  /**
    * Get listings from oodle
    *
    * @param string region (http://developer.oodle.com/regions-list)
    * - canada
    * - uk
    * - usa
    * - india
    * - ireland
    * @param array of options
    */
  function listings($region = 'usa', $options = array()){
    if(is_string($options)){
      $options = array(
        'category' => $options
      );
    }
    
    $this->query = array_merge(
      array(
        'region' => $region,
        'format' => $this->_defaultOutput
      ),
      $options
    );
    
    return $this->__request();
  }
	
  /**
    * Actually preform the request to AWS
    *
    * @return mixed array of the resulting request or false if unablel to contact server
    * @access private
    */
  function __request(){
    $this->__requestLog[] = array('url' => $this->url, 'query' => $this->query);
    $retval = $this->Http->get($this->url, $this->query);
    
    switch($this->query['format']){
      case 'json': return json_decode($retval);
      case 'xml' : return Set::reverse(new Xml($retval));
      default    : return $retval; 
    }
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