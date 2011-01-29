<?php
/**
	* AddThis Helper loads the javascript library as well
	* as set custom share layouts and features.
	*
	* Setup
	* 1) Obtain a username by signing up for an addthis account http://www.addthis.com
	* 2) Create a config/addthis.php with the following
	*
	* $config = array(
	*   'AddThis' => array(
	*     'username' => 'YOUR_USER_NAME'
	*   )
	* );
	*
	* You can also pass in any option when instanciate the helper.
	*  $var helpers = array('WebTechNick.AddThis' => array(
	*	 		'username' => 'YOUR_USER_NAME'
	*			'defaultShow' => array('facebook_like', 'twitter_count', 'addthis_pill')
  *  ));
	*
	* @version 1.0
	* @author Nick Baker
	* @license MIT
	*/
class AddThisHelper extends AppHelper{
	/**
	* Load the HTML Helper
	*/
  var $helpers = array('Html');
  
  /**
    * Current protocol to avoid conflicts of loading http within https
    */
  var $protocol = 'http://';
  
  /**
    * Addthis loader url
    */
  var $addthisLoader = 's7.addthis.com/js/250/addthis_widget.js#username=';
  
  /**
    * The configuration username
    */
  var $username = null;
  
  /**
  	* Sharable associative array.
  	*/
  var $showable = array(
  	'facebook_like' => array('class' => 'addthis_button_facebook_like', 'fb:like:layout' => 'button_count'),
  	'twitter_count' => array('class' => 'addthis_button_tweet'),
  	'facebook' => array('class' => 'addthis_button_facebook'),
  	'twitter' => array('class' => 'addthis_button_twitter'),
  	'email' => array('class' => 'addthis_button_email'),
  	'google' => array('class' => 'addthis_button_google'),
  	'addthis_compact' => array('class' => 'addthis_button_compact'),
  	'addthis_pill' => array('class' => 'addthis_counter addthis_pill_style'),
  );
  
  /**
  	* Default share options to show
  	*/
  var $defaultShow = array(
  	'facebook_like',
  	'twitter_count',
  	'addthis_pill'
  );
  
  /**
    * Flag if api has been loaded or not.
    */
  var $loadedApi = false;
  
  /**
  	* Load the configurations
  	* @param array of settings
  	*/ 
  function __construct($settings = array()){
  	if(empty($settings)){
  		Configure::load('addthis');
  		$this->_set(Configure::read('AddThis'));
  	}
  	else {
  		$this->_set($settings);
  	}
  	
  	if(!$this->protocol){
  		$this->protocol = env('HTTPS') ? 'https://' : 'http://';
  	}
  	
  	if(!$this->username){
  		trigger_error('AddThis username not found.  Please create config/addthis.php with the username');
  	}
  	
  	parent::__construct();
  }
  
  /**
  	* Load javascript
  	*/
	function api(){
		$this->loadedApi = true;
		$retval = $this->Html->scriptBlock('var addthis_config = {"data_track_clickback":true};');
		$retval .= $this->Html->script($this->protocol . $this->addthisLoader . $this->username);
		return $retval;
	}
	
	/**
		* return the share settings
		* @param array of options
		*  - show mixed single button, or array of buttons to show, must be in showable
		*  - url string url to have all the links share to
		*  - title string title to have all the links title tag
		*  - load boolean load the javascript (if it hasn't been loaded already)
		* @return HtmlDiv string of addthis element
		*/
	function share($options = array()){
		$options = array_merge(
			array(
				'show' => $this->defaultShow,
				'divClass' => array(
					'addthis_toolbox',
					'addthis_32x32_style',
					'addthis_default_style'
				),
				'divOptions' => array(),
				'load' => true
			),
			$options
		);
		
		if(!is_array($options['show'])){
			$options['show'] = array($options['show']);
		}
		
		if(isset($options['url'])){
			$options['divOptions']['addthis:url'] = $options['url']; 
		}
		
		$buttons = "";
		foreach($options['show'] as $show){
			if(array_key_exists($show, $this->showable)){
				$buttons .= $this->Html->link('', '', $this->showable[$show]); 
			}
		}
		
		$retval = $options['load'] ? $this->__loadApi() : "";
		return $this->Html->div(
			implode(" ",$options['divClass']),
			$buttons,
			$options['divOptions']
		) . $retval;
	}
	
	/**
    * Append the API loader to the script unless its already loaded.
    * @return string HtmlScript or empty string
    */
  private function __loadApi(){
    return !$this->loadedApi ? $this->api() : "";
  }
}
?>