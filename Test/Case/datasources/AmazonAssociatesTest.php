<?php
App::import('Datsource', 'WebTechNick.GeoLocSource');
class AmazonAssociatesTest extends CakeTestCase {
  var $Amazon = null;
  var $config = array(
	  'server' => 'maxmind',
	  'cache' => false,
	  'engine' => false,
  );
  
  function startTest(){
    $this->GeoLoc = new GeoLocSource($this->config);
  }
  
  function testByIp(){
  	$result = $this->GeoLoc->byIp('76.18.89.8', array('server' => 'maxmind'));
  	$this->assertTrue(!empty($result));
  }
  
  function endTest(){
    unset($this->Amazon);
  }
}
?>