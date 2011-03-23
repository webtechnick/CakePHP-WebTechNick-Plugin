<?php
App::import('Helper', 'WebTechNick.Google');

class GoogleHelperTestCase extends CakeTestCase {

	function startTest() {
		$this->Google = new GoogleHelper(true);
	}
	
	function test_geoLoc(){
		$result = $this->Google->geoLoc('90210');
		$this->assertEqual('Beverly Hills, CA 90210, USA', $result['Placemark'][0]['address']);
		$this->assertEqual('-118.4389877', $result['Placemark'][0]['Point']['coordinates'][0]);
		$this->assertEqual('34.1346702', $result['Placemark'][0]['Point']['coordinates'][1]);
	}

	function endTest(){
		unset($this->Google);
	}
}
?>