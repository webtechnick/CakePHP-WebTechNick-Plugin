# AddThis Helper

Load javascript and buttons using hte AddThis service

## Setup

1. Obtain a username by signing up for an addthis account http://www.addthis.com
2. Create a `config/addthis.php` file with the following
Note: You can use Username or PubID, only one is required for tracking

	$config = array(
		'AddThis' => array(
			'username' => 'YOUR_USER_NAME',
			'pubid' => 'YOUR_PUBID'
		)
	);
	
### OR

You can also pass in any settable option when declaring the helper

	var $helpers = array(
		'WebTechNick.AddThis' => array(
			'username' => 'YOUR_USER_NAME',
			'pubid' => 'YOUR_PUBID',
			'defaultShow' => array(
				'facebook_like',
				'twitter_count',
				'addthis_pill',
			)
		);
	);
	
	
# Usage Examples

	//Load default share buttons
	$this->AddThis->share();
	
	//Load custom layout with title
	$this->AddThis->share(array(
		'show' => array(
			'facebook',
			'twitter',
			'email',
			'google',
			'addthis_compact'
		),
		'url' => 'http://www.example.com'
	)); ?>