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
*     'username' => 'YOUR_USER_NAME',
*     'pubid' => 'YOUR_PUDID',
*   )
* );
*
* You can also pass in any option when instanciate the helper.
*  $var helpers = array('WebTechNick.AddThis' => array(
*	 		'username' => 'YOUR_USER_NAME',
*     'pubid' => 'YOUR_PUBID',
*			'defaultShow' => array('facebook_like', 'twitter_count', 'addthis_pill')
*  ));
*
* @version 1.2
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
	var $addthisLoader = 's7.addthis.com/js/250/addthis_widget.js'; //#username= or #pubid=
	
	/**
	* The configuration username
	*/
	var $username = null;
	
	/**
	* The configuration pubid
	*/
	var $pubid = null;
	
	/**
	* The configuration ga_property
	*/
	var $ga_property = null;
	
	/**
	* Sharable associative array.
	*/
	var $showable = array(
		'aim' => array('class' => 'addthis_button_aim'),
		'ask' => array('class' => 'addthis_button_ask'),
		'bebo' => array('class' => 'addthis_button_bebo'),
		'buzz' => array('class' => 'addthis_button_buzz'),
		'delicious' => array('class' => 'addthis_button_delicious'),
		'digg' => array('class' => 'addthis_button_digg'),
		'email' => array('class' => 'addthis_button_email'),
		'facebook' => array('class' => 'addthis_button_facebook'),
		'facebook_like' => array('class' => 'addthis_button_facebook_like', 'fb:like:layout' => 'button_count'),
		'favorites' => array('class' => 'addthis_button_favorites'),
		'fark' => array('class' => 'addthis_button_fark'),
		'friendfeed' => array('class' => 'addthis_button_friendfeed'),
		'google' => array('class' => 'addthis_button_google'),
		'google_plusone' => array('class' => 'addthis_button_google_plusone'),
		'google_plusone_medium' => array('class' => 'addthis_button_google_plusone', 'g:plusone:size' => 'medium'),
		'hyves' => array('class' => 'addthis_button_hyves'),
		'linkedin' => array('class' => 'addthis_button_linkedin'),
		'live' => array('class' => 'addthis_button_live'),
		'meneame' => array('class' => 'addthis_button_meneame'),
		'misterwong' => array('class' => 'addthis_button_misterwong'),
		'mixx' => array('class' => 'addthis_button_mixx'),
		'myaol' => array('class' => 'addthis_button_myaol'),
		'myspace' => array('class' => 'addthis_button_myspace'),
		'pinterest' => array('class' => 'addthis_button_pinterest_pinit'),
		'pinterest_vert' => array('class' => 'addthis_button_pinterest_pinit', 'pi:pinit:layout' => 'vertical'),
		'print' => array('class' => 'addthis_button_print'),
		'propeller' => array('class' => 'addthis_button_propeller'),
		'reddit' => array('class' => 'addthis_button_reddit'),
		'segnalo' => array('class' => 'addthis_button_segnalo'),
		'slashdot' => array('class' => 'addthis_button_slashdot'),
		'stumbleupon' => array('class' => 'addthis_button_stumbleupon'),
		'technorati' => array('class' => 'addthis_button_technorati'),
		'twitter_count' => array('class' => 'addthis_button_tweet'),
		'twitter' => array('class' => 'addthis_button_twitter'),
		'viadeo' => array('class' => 'addthis_button_viadeo'),
		'yahoo' => array('class' => 'addthis_button_yahoobkm'),
		'addthis_compact' => array('class' => 'addthis_button_compact'),
		'addthis_pill' => array('class' => 'addthis_counter addthis_pill_style'),
		'addthis_extended' => array('class' => 'addthis_button_extended'),
	);
	
	/**
	* Default share options to show
	*/
	var $defaultShow = array(
		'facebook_like',
		'twitter_count',
		'google_plusone_medium',
		'addthis_pill',
	);
	
	/**
	* Flag if api has been loaded or not.
	*/
	var $loadedApi = false;
	
	/**
	* Load the configurations
	* @param array of settings
	*/ 
	function __construct($View, $settings = array()){
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
		
		if(!$this->username && !$this->pubid){
			trigger_error('AddThis username and pubid not found.  Please create config/addthis.php with the username');
		}
		
		parent::__construct($View);
	}
	
	/**
	* Load javascript
	*/
	function api(){
		$this->loadedApi = true;
		$user = ($this->pubid) ? "#pubid=" . $this->pubid : "#username=" . $this->username;
		$config = array(
			'data_track_clickback' => true,
		);
		if($this->ga_property){
			$config['data_ga_property'] = $this->ga_property;
		}
		$retval = $this->Html->scriptBlock('var addthis_config = '. json_encode($config) .';');
		$retval .= $this->Html->script($this->protocol . $this->addthisLoader . $user);
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