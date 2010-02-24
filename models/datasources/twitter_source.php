<?php
/**
 * Twitter Datasource 0.2
 *
 * Twitter datasource to communicate with the Twitter API.
 *
 * References(Credits):
 * http://bakery.cakephp.org/articles/view/paypal-datasource
 * http://bakery.cakephp.org/articles/view/twitter-component
 * http://debuggable.com/posts/new-google-analytics-api:480f4dd6-c59c-445f-8ce0-4202cbdd56cb
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * @author Alexandru Ciobanu <ics@4algorithms.com>
 * @copyright (c) 2009 Alexandru Ciobanu
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://bakery.cakephp.org/articles/view/twitter-datasource
 * @created May 4, 2009
 * @version 0.2 *
 */
App::import('Core', array('Xml', 'HttpSocket'));

class TwitterSource extends DataSource
{
	var $username = "";
	var $password = "";
	var $description = "Twitter API";
	var $Http = null;

	function __construct($config) {
		parent::__construct($config);
		$this->Http =& new HttpSocket();
		$this->username = $this->config['username'];
		$this->password = $this->config['password'];
	}


	/**
	 * Returns a single status, specified by the id parameter below.
	 * The status's author will be returned inline.
	 *
	 * @param string id Required. The numerical ID or username of the status you're trying to retrieve.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_show($id) {
		$url = "http://twitter.com/statuses/show/{$id}.xml";
		return $this->__process($this->Http->get($url));
	}

	function user_show($id=false, $params = array()) {
		$url = "http://twitter.com/users/show";
		if($id != false) {
			$url .= "/{$id}.xml";
		} else {
			$url .= ".xml";
		}
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Returns the 20 most recent statuses from non-protected users
	 * who have set a custom user icon.  Does not require authentication.
	 *
	 * @param array params Optional. parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_public_timeline($params=array()) {
		$url = 'http://twitter.com/statuses/public_timeline.xml';
		return $this->__process($this->Http->get($url, $params));
	}

	/**
	 * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating
	 * user and that user's friends.  It's also possible to request another user's
	 * friends_timeline via the id parameter below.
	 *
	 * @param string id Optional. Specifies the ID or screen name of the user for whom to return the friends_timeline
	 * @param array params Optional. parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_friends_timeline($id=false, $params = array()) {
		$url = 'http://twitter.com/statuses/friends_timeline';
		if($id != false) {
			$url .= "/{$id}.xml";
		} else {
			$url .= ".xml";
		}
		return $this->__process($this->Http->get($url, $params));
	}

	/**
	 * Returns the 20 most recent statuses posted in the last 24 hours from the authenticating user.
	 * It's also possible to request another user's timeline via the id parameter below.
	 *
	 * @param string id Optional. Specifies the ID or screen name of the user for whom to return the friends_timeline.
	 * @param array params Optional. parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_user_timeline($id=false, $params = array()) {
		$url = 'http://twitter.com/statuses/user_timeline';
		if($id != false) {
			$url .= "/{$id}.xml";
		} else {
			$url .= ".xml";
		}
		
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
		//return $this->__process($this->Http->get($url, $params));
	}

	/**
	 * Updates the authenticating user's status.  Requires the status parameter specified below.
	 *
	 * @param string status Required.  The text of your status update.
	 *              Be sure to URL encode as necessary.  Must not be more than 160 characters
	 *              and should not be more than 140 characters to ensure optimal display.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_update($status) {
		$url = "http://twitter.com/statuses/update.xml";
		return $this->__process($this->Http->post($url, array('status' => $status), $this->__getAuthHeader()));
	}

	/**
	 * Returns the 20 most recent replies
	 * (status updates prefixed with @username posted by users who
	 * are friends with the user being replied to)
	 * to the authenticating user.
	 *
	 * @param array params Optional. Parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_replies($params = array()) {
		$url = "http://twitter.com/statuses/replies.xml";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Destroys the status specified by the required ID parameter.
	 *
	 * @param string id Required.  The ID of the status to destroy.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function status_destroy($id) {
		$url = "http://twitter.com/statuses/destroy/{$id}.xml";
		return $this->__process($this->Http->post($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Returns up to 100 of the authenticating user's friends who have most recently updated, each with current status inline.
	 * It's also possible to request another user's recent friends list via the id parameter below.
	 *
	 * @param string id Optional.  The ID or screen name of the user for whom to request a list of friends.
	 * @param array params Optional. Parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function user_friends($id=false, $params = array()) {
		$url = "http://twitter.com/statuses/friends";
		if($id != false) {
			$url .= "/{$id}.xml";
		} else {
			$url .= ".xml";
		}
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Returns the authenticating user's followers, each with current status inline.
	 *
	 * @param array params Optional. Parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function user_followers($params = array()) {
		$url = "http://twitter.com/statuses/followers.xml";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Returns a list of the users currently featured on the site with their current statuses inline.
	 *
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function user_featured() {
		$url = "http://twitter.com/statuses/featured.xml";
		return $this->__process($this->Http->get($url));
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent to the authenticating user.
	 *
	 * @param array params Optional. Parameters passed to the query.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function direct_messages($params = array()) {
		$url = "http://twitter.com/direct_messages.xml";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Returns a list of the 20 most recent direct messages sent by the authenticating user.
	 *
	 * @param array params Optional. Parameters passed to the query.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function direct_messages_sent($params = array()) {
		$url = "http://twitter.com/direct_messages/sent.xml";
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Sends a new direct message to the specified user from the authenticating user.
	 *
	 * @param string user Required.  The ID or screen name of the recipient user.
	 * @param string text Required.  The text of your direct message.
	 *              Be sure to URL encode as necessary, and keep it under 140 characters.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function direct_messages_new($user, $text) {
		$url = "http://twitter.com/direct_messages/new.xml";
		$params = array('user' => $user, 'text' => $text);
		return $this->__process($this->Http->post($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Destroys the direct message specified in the required ID parameter.
	 *
	 * @param string id Required.  The ID of the direct message to destroy.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function direct_messages_destroy($id) {
		$url = "http://twitter.com/direct_messages/destroy/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Befriends the user specified in the ID parameter as the authenticating user.
	 *
	 * @param string id Required.  The ID or screen name of the user to befriend.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function friendship_create($id) {
		$url = "http://twitter.com/friendships/create/{$id}.xml";
		return $this->__process($this->Http->post($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Discontinues friendship with the user specified in the ID parameter as the authenticating user.
	 *
	 * @param string id Required.  The ID or screen name of the user with whom to discontinue friendship.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function friendship_destroy($id) {
		$url = "http://twitter.com/friendships/destroy/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Returns an HTTP 200 OK response code and a format-specific response if authentication was successful.
	 *
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function account_verify_credentials() {
		$url = "http://twitter.com/account/verify_credentials.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Ends the session of the authenticating user, returning a null cookie.
	 *
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function account_end_session() {
		$url = "http://twitter.com/account/end_session";
		$this->Http->get($url, null, $this->__getAuthHeader());
	}

	/**
	 * Returns 200 statuses per page for the authenticating user, ordered by descending date of posting.
	 *
	 * @param integer page Optional. Retrieves the 200 next most recent statuses.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function account_archive($page=1) {
	  $url = 'http://twitter.com/statuses/user_timeline.xml?';  //updated from deprecation notice
		//$url = 'http://twitter.com/account/archive.xml';
		return $this->__process($this->Http->get($url, array('page' => $page, 'count' => 200), $this->__getAuthHeader()));
	}

	/**
	 * Updates the location attribute of the authenticating user,
	 * as displayed on the side of their profile and returned in various API methods.
	 *
	 * @param string location The location of the user.  Please note this is not normalized, geocoded, or translated to latitude/longitude at this time.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function account_update_location($location) {
		$url = 'http://twitter.com/account/update_location.xml';
		return $this->__process($this->Http->get($url, array('location' => $location), $this->__getAuthHeader()));
	}

	/**
	 * Sets which device Twitter delivers updates to for the authenticating user.
	 * Sending none as the device parameter will disable IM or SMS updates.
	 *
	 * @param string device  Must be one of: sms, im, none.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function account_update_delivery_device($device) {
		$url = 'http://twitter.com/account/update_delivery_device.xml';
		return $this->__process($this->Http->get($url, array('device' => $device), $this->__getAuthHeader()));
	}

	/**
	 * Returns the 20 most recent favorite statuses for the authenticating user
	 * or user specified by the ID parameter in the requested format.
	 *
	 * @param string id Optional.  The ID or screen name of the user for whom to request a list of favorite statuses.
	 * @param array params Optional. Parameters passed to the query
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function favorites($id = false, $params = array()) {
		$url = "http://twitter.com/favorites";
		if($id != false) {
			$url .= "/{$id}.xml";
		} else {
			$url .= ".xml";
		}
		return $this->__process($this->Http->get($url, $params, $this->__getAuthHeader()));
	}

	/**
	 * Favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param string id Required.  The ID of the status to favorite.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function favorites_create($id) {
		$url = "http://twitter.com/favorites/create/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Un-favorites the status specified in the ID parameter as the authenticating user.
	 *
	 * @param string id Required.  The ID of the status to un-favorite.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function favorites_destroy($id) {
		$url = "http://twitter.com/favorites/destroy/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}
	/**
	 * Search for keyword using the twitter search API
	 *
	 * @param string $keyword
	 * @param string $language
	 * @see http://search.twitter.com/api
	 */
	function search($keyword, $language = 'all', $rpp = '10'){
		$url = "http://search.twitter.com/search.atom?q=$keyword&lang=$language&rpp=$rpp";
		return $this->__process($this->Http->get($url));
	}

	/**
	 * Enables notifications for updates from the specified user to the authenticating user.
	 *
	 * @param string id Required.  The ID or screen name of the user to follow.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function notifications_follow($id) {
		$url = "http://twitter.com/notifications/follow/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Disables notifications for updates from the specified user to the authenticating user.
	 *
	 * @param string id Required.  The ID or screen name of the user to leave.
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function notifications_leave($id) {
		$url = "http://twitter.com/notifications/leave/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Blocks the user specified in the ID parameter as the authenticating user.
	 * Returns the blocked user in the requested format when successful.
	 *
	 * @param string id The ID or screen_name of the user to block
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function block_create($id) {
		$url = "http://twitter.com/blocks/create/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Un-blocks the user specified in the ID parameter as the authenticating user.
	 * Returns the un-blocked user in the requested format when successful.
	 *
	 * @param string id The ID or screen_name of the user to block
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function block_destroy($id) {
		$url = "http://twitter.com/blocks/destroy/{$id}.xml";
		return $this->__process($this->Http->get($url, null, $this->__getAuthHeader()));
	}

	/**
	 * Returns the string "ok" in the requested format with a 200 OK HTTP status code.
	 *
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function help_test() {
		$url = 'http://twitter.com/help/test.xml';
		return $this->__process($this->Http->get($url));
	}

	/**
	 * Returns the same text displayed on http://twitter.com/home when
	 * a maintenance window is scheduled, in the requested format.
	 *
	 * @see http://groups.google.com/group/twitter-development-talk/web/api-documentation
	 */
	function help_downtime_schedule() {
		$url = 'http://twitter.com/help/downtime_schedule.xml';
		return $this->__process($this->Http->get($url));
	}
	/**
	 * Credentials array for method with mandatory auth
	 * @return array credentials
	 */
	function __getAuthHeader() {
		return array('auth' => array('method' => 'Basic',
									 'user' => $this->username,
									 'pass' => $this->password
		)
		);
	}

	/**
	 *
	 * @param string data to process
	 * @return array Twitter API response
	 */
	function __process($response) {
		$xml = new XML($response);
		$array = $xml->toArray();

		//$xml->__killParent();
		$xml->__destruct();
		$xml = null;
		unset($xml);

		return $array;
	}
}
?>
