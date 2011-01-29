# AddThis Helper

Load javascript and buttons using hte AddThis service

## Setup

1. Obtain a username by signing up for an addthis account http://www.addthis.com
2. Create a `config/addthis.php` file with the following

	$config = array(
		'AddThis' => array(
			'username' => 'YOUR_USER_NAME'
		)
	);
	
### OR

You can also pass in any settable option when declaring the helper

	var $helpers = array(
		'WebTechNick.AddThis' => array(
			'username' => 'YOUR_USER_NAME',
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