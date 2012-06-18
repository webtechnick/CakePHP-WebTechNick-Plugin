<?php
App::import('Datsource', 'WebTechNick.SuPrSource');
class SuPrTestCase extends CakeTestCase {
  var $SuPr = null;
  var $config = array(
	  'api_key' => 'YOUR_API_KEY',
	  'username' => 'YOUR_USERNAME',
  );
  
  function startTest(){
    $this->SuPr = new SuPrSource($this->config);
  }
  
  /*function testPost(){
  	$result = $this->SuPr->post("Hello Word! from StumbleUpon API, Twitter Only", array('services' => 'twitter'));
  	$this->assertEqual(0, $result['errorCode']);
  	$this->assertTrue(1, count($this->SuPr->__requestLog));
  	
  	$result = $this->SuPr->post("Hello Word! from StumbleUpon API");
  	$this->assertEqual(0, $result['errorCode']);
  	$this->assertTrue(2, count($this->SuPr->__requestLog));
  }*/
  
  function test_simpleShorten(){
  	$result = $this->SuPr->simpleShorten("http://www.google.com");
  	$this->assertTrue(strpos($result,"su.pr"));
  }
  
  function endTest(){
    unset($this->Amazon);
  }
}
?>