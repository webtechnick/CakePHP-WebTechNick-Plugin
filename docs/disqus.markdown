== Disqus Element

* Setup account at http://disqus.com
* http://docs.disqus.com/help/2/
* http://docs.disqus.com/developers/universal/


***Usage:***

		$this->element('disqus', array(
			'plugin' => 'WebTechNick', 
			'shortname' => 'example'
		));
		
		$this->element('disqus', array(
			'plugin' => 'WebTechNick',
			'shortname' => 'example',
			'dev' => true
		));
		
		$this->element('disqus', array(
			'plugin' => 'WebTechNick',
			'shortname' => 'example',
			'title' => 'Title',
			'url' => 'http://example.com',
			'identifier' => 'unique_dynamic_identifier_123'
		));

