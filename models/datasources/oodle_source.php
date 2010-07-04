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
 * App::import('Core','ConnectionManager');
 * $oodle = ConnectionManager::getDataSource('oodle');
 * $oodle->listings('usa', array('category' => 'housing/sale'));
 *
 *
 * @version 0.1
 * @author Nick Baker <nick@webtechnick.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 */
App::import('Core', 'Xml');
App::import('Core', 'HttpSocket');
class OodleSource extends DataSource{
  /**
    * Description of datasource
    * @access public
    */
  var $description = "Oodle Data Source";
  
  /**
    * Base url
    */
  var $url = 'http://api.oodle.com/api/v2/listings';
  
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
    * List of internal errors that happened at any time.
    * @var array
    * @access public
    */
  var $errorLog = array();
    
  /**
    * Request, combination of query and apikey
    * @access protected
    */
  var $_request = array();
  
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
    $this->Http = new HttpSocket();
    if(!isset($this->config['apikey'])){
      $this->__error();
    }
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
    * @return Oodle results
    */
  function find($region = 'usa', $options = array()){
    if(is_string($options)){
      $options = array(
        'category' => $options
      );
    }
    
    $this->query = array_merge(
      array(
        'region' => $region,
        'format' => $this->_defaultOutput,
        'jsoncallback' => 'none',
      ),
      $options
    );
    
    return $this->__request();
  }
  
  /**
    * get the total count of a perticular set of conditions
    * @param string region (http://developer.oodle.com/regions-list)
    * - canada
    * - uk
    * - usa
    * - india
    * - ireland
    * @return int number of total results.
    */
  function count($region = 'usa', $options = array()){
    if(is_string($options)){
      $options = array(
        'category' => $options
      );
    }
    
    $options = array_merge(
      $options,
      array('start' => 1, 'num' => 1)
    );
    
    $oodle_result = $this->find($region, $options);
    return isset($oodle_result['meta']['total']) ? $oodle_result['meta']['total'] : 0;
  }
  
  /**
    * Find an oodle listing by ID.
    * 
    * @param oodle_id
    * @param region (default usa)
    * @return Oodle results 
    */
  function findById($oodle_id, $region = 'usa'){
    return $this->find($region, array(
      'q' => $oodle_id,
      'num' => 1,
      'start' => 1,
      'assisted_search' => 'yes'
    ));
  }
  
  /**
    * Take note of the error and append it to the errorLog
    * @param string error message
    */
  function __error($msg){
    $this->errorLog[] = __($msg, true);
  }
	
  /**
    * Actually preform the request to AWS
    *
    * @return mixed array of the resulting request or false if unablel to contact server
    * @access private
    */
  function __request(){
    $this->_request = $this->__signQuery();
    $this->__requestLog[] = array('url' => $this->url, 'query' => $this->_request);
    $retval = $this->Http->get($this->url, $this->_request);
    
    switch($this->query['format']){
      case 'json': return json_decode($retval, true);
      case 'xml' : return Set::reverse(new Xml($retval));
      default    : return $retval; 
    }
  }
  
  /**
    * Sign the query, appling the API key to the query
    *
    * @return array query with apikey applied to it.
    */
  function __signQuery(){
    return array_merge(
      $this->query,
      array('key' => $this->config['apikey'])
    );
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