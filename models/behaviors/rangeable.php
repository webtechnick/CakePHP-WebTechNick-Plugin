<?php
/**
 * A CakePHP behavior to facilitate a simple reteival of results via a range search on a lon/lat fields
 *
 * Copyright 2010, Alan Blount
 * Alan@zeroasterisk.com
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2010, Alan Blount
 * @version 1.0
 * @author Alan Blount <Alan@zeroasterisk.com>
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link https://github.com/zeroasterisk/cakephp-behavior-rangeable
 */
class RangeableBehavior extends ModelBehavior {
	var $settings = array();
	var $milesToDegrees = .01445;
	var $mapMethods = array('/^_findRange$/' => '_findRange');
	var $debug = array(); // false or array()
	
	/**
	* Setup the Behavior
	*/
	function setup(&$Model, $settings = array()) {
		if (!isset($this->settings[$Model->alias])) {
			$this->settings[$Model->alias] = array();
		}
		if (!is_array($settings)) {
			$settings = array();
		}
		$this->settings[$Model->alias] = array_merge(array(
			'field_lat' => 'lat',
			'field_lon' => 'lon',
			'field_zip' => 'zip', // only used if 'lookup_zip_from_conditions' == true
			'lookup_zip_from_conditions' => true, // lookup a zip from the conditions array, remove from conditions array 
			'lookup_zip_model_name' => 'Zip',
			'lookup_zip_field_zip' => 'zip',
			'lookup_zip_field_lat' => 'lat',
			'lookup_zip_field_lon' => 'lon',
			'range' => 20, // range in miles
			'range_out_till_count_is' => 2, // keep increasing range till count >= (false or 0 to disable)
			'range_out_increment' => 20, // increasing range by this increment
			'range_out_limit' => 20, // maximum tries - max range = range + (range_out_increment * range_out_limit)
			'order_by_distance' => true, // resort results by distance to target
			'unique_only' => true, // only show unique items based on primaryKey
			'limitless' => true, // removes limit for initial find, so the sort can be accurate
			'query' => array(),
			'countField' => 'count',
			), $settings);
		$Model->_findMethods['range'] = true;
		
		if (!$Model->hasField($this->settings[$Model->alias]['lookup_zip_field_lat']) || !$Model->hasField($this->settings[$Model->alias]['lookup_zip_field_lon'])) {
			trigger_error('RangeableBehavior: You must have a valid lat and lon field for querying this Model\'s as rangeable!');
			die();
		}
	}
	
	/**
	* Custom Find Method which injects the range box (and optionally increments that range until we hit a minimum set of results) into the conditions
	* it also re-sorts the results by the distance to the sort point
	*/
	function _findRange(&$Model, $method, $state, $query, $results = array()) {
		if ($state == 'before') {
			$query = array_merge($query, $this->settings[$Model->alias]['query']);
			
			$lat = $lon = $zip = null;
			if (isset($query['lat'])) { $lat = $query['lat']; }
			foreach ( array($this->settings[$Model->alias]['field_lat'], "{$Model->alias}.{$this->settings[$Model->alias]['field_lat']}") as $key ) { 
				if (array_key_exists($key, $query['conditions'])) {
					$lat = $query['conditions'][$key];
					unset($query['conditions'][$key]);
				}
			}
			if (isset($query['lon'])) { $lon = $query['lon']; }
			foreach ( array($this->settings[$Model->alias]['field_lon'], "{$Model->alias}.{$this->settings[$Model->alias]['field_lon']}") as $key ) { 
				if (array_key_exists($key, $query['conditions'])) {
					$lon = $query['conditions'][$key];
					unset($query['conditions'][$key]);
				}
			}
			if (isset($query['zip'])) { $zip = $query['zip']; }
			if ($this->settings[$Model->alias]['lookup_zip_from_conditions']) {
				foreach ( array($this->settings[$Model->alias]['field_zip'], "{$Model->alias}.{$this->settings[$Model->alias]['field_zip']}") as $key ) { 
					if (array_key_exists($key, $query['conditions'])) {
						$zip = $query['conditions'][$key];
						unset($query['conditions'][$key]);
					}
				}
				if (empty($lat) || empty($lon)) {
					if (!empty($zip)) {
						if (!isset($this->ZipModel) || !is_object($this->ZipModel)) {
							App::import('Model', $this->settings[$Model->alias]['lookup_zip_model_name']);
							$this->ZipModel = null;
							$this->ZipModel =& ClassRegistry::init($this->settings[$Model->alias]['lookup_zip_model_name']);
						}
						$zipData = $this->ZipModel->find('first', array(
							'recursive' => 1,
							'conditions' => array("{$this->ZipModel->alias}.{$this->settings[$Model->alias]['lookup_zip_field_zip']}" => $zip)
							));
						if (isset($zipData[$this->ZipModel->alias][($this->settings[$Model->alias]['lookup_zip_field_lat'])])) {
							$lat = $zipData[$this->ZipModel->alias][($this->settings[$Model->alias]['lookup_zip_field_lat'])];
						}
						if (isset($zipData[$this->ZipModel->alias][($this->settings[$Model->alias]['lookup_zip_field_lon'])])) {
							$lon = $zipData[$this->ZipModel->alias][($this->settings[$Model->alias]['lookup_zip_field_lon'])];
						}
					}
				}
			}
			if (!empty($lat) && !empty($lon)) {
				$range = 10;
				if (isset($query['range']) && !empty($query['range'])) { $range = $query['range']; }
				$query = $this->getRangeQuery($Model, $query, $range, $lat, $lon);
				$range_out_till_count_is = $this->settings[$Model->alias]['range_out_till_count_is'];
				if (isset($query['range_out_till_count_is'])) {
					$range_out_till_count_is = $query['range_out_till_count_is'];
				}
				if (!empty($range_out_till_count_is)) {
					// gotta count results and loop until count =>
					$count = $Model->find('count', $query);
					$reps = 0;
					while ($count < $range_out_till_count_is && $reps < $this->settings[$Model->alias]['range_out_limit']) {
						$range = $range + $this->settings[$Model->alias]['range_out_increment'];
						$query = $this->getRangeQuery($Model, $query, $range, $lat, $lon);
						$count = $Model->find('count', $query);
						$reps++;
					}
				}
				$query['lat'] = $lat;
				$query['lon'] = $lon;
				$query['range'] = $range;
				// possibly remove limit, from find, so that order_by_distance works right
				$limitless = $this->settings[$Model->alias]['limitless'];
				if (isset($query['limitless'])) { $limitless = $query['limitless']; }
				$order_by_distance = $this->settings[$Model->alias]['order_by_distance'];
				if (isset($query['order_by_distance'])) { $order_by_distance = $query['order_by_distance']; }
				if (!empty($limitless) && !empty($order_by_distance) && isset($query['limit'])) { 
					$query['limit_'] = $query['limit'];
					$query['limit'] = (is_numeric($limitless) ? $limitless : 9999);
				}
			}
			return $query;
		}
		if (empty($results)) {
			return array();
		}
		// re-sort by distance
		$lat = $lon = null;
		if (isset($query['lat'])) { $lat = $query['lat']; }
		if (isset($query['lon'])) { $lon = $query['lon']; }
		$unique_only = isset($query['unique_only']) ? $query['unique_only'] : $this->settings[$Model->alias]['unique_only'];
		$order_by_distance = isset($query['order_by_distance']) ? $query['order_by_distance'] : $this->settings[$Model->alias]['order_by_distance'];
		
		if (!empty($lat) && !empty($lon) && !empty($order_by_distance)) {
			$resultsByRange = array();
			$foundIds = array();
			foreach ( $results as $result ) {
				$distance = $this->calculatePrecisionDistance($lat, $lon, $result[$Model->alias][($this->settings[$Model->alias]['lookup_zip_field_lat'])], $result[$Model->alias][($this->settings[$Model->alias]['lookup_zip_field_lon'])]);
				$result[$Model->alias]['distance'] = $distance;
				$distanceSortable = intval(($distance*1000));
				if (array_key_exists($distanceSortable, $resultsByRange)) {
					while (array_key_exists($distanceSortable, $resultsByRange)) {
						$distanceSortable++;
					}
				}
				//only display unique
				if(!$unique_only || !in_array($result[$Model->alias][$Model->primaryKey], $foundIds)){
					$foundIds[] = $result[$Model->alias][$Model->primaryKey];
					$resultsByRange[$distanceSortable] = $result;
				}
			}
			ksort($resultsByRange);
			$results = array();
			foreach ( $resultsByRange as $distanceSortable => $result ) {
				if (!isset($query['limit_']) || count($results) < $query['limit_']) {
					// treated as a string, so that putting into an array doesn't force type to an int.
					$distanceSortable = strval($distanceSortable);
					$distanceSortable = substr($distanceSortable, 0, strlen($distanceSortable)-3).'.'.substr($distanceSortable,-3, 4);
					$results[$distanceSortable] = $result; 
				}
			}
		}
		return $results;
	}
	
	/**
	* Get the latitude and longitude for a single zip code.
	*
	* @access public
	* @param object $Model
	* @param array $query
	* @param float $range
	* @param float $lat
	* @param float $lon
	* @return array conditions node for this range
	*/
	function getRangeQuery(&$Model, $query, $range, $lat, $lon) {
		$maxCoords = $this->getRangeBox($range, $lat, $lon);
		$return = $query;
		$return['conditions']["{$Model->alias}.{$this->settings[$Model->alias]['lookup_zip_field_lat']} BETWEEN ? AND ?"] = array($maxCoords['min_lat'], $maxCoords['max_lat']);
		$return['conditions']["{$Model->alias}.{$this->settings[$Model->alias]['lookup_zip_field_lon']} BETWEEN ? AND ?"] = array($maxCoords['min_lon'], $maxCoords['max_lon']);
		if (is_array($this->debug)) {
			$this->debug[__function__][] = compact('range', 'query', 'lat', 'lon', 'return');
		}
		return $return; 
	}
	/**
	* Get the maximum and minimum longitude and latitude values
	* that our zip codes can be in.
	*
	* Not all zipcodes in this box will be with in the range.
	* The closest edge of this box is exactly range miles away
	* from the origin but the corners are sqrt(2(range^2)) miles
	* away. That is why we have to double check the ranges.
	*
	* @access public
	* @param float $range
	* @param float $lat
	* @param float $lon
	* @return array associative index max/min lat/lon
	*/
	function getRangeBox($range, $lat, $lon) {
		// Calculate the degree range using the mile range
		$degrees = $range * $this->milesToDegrees;
		$lat = floatval($lat);
		$lon = floatval($lon);
		$return = array(
			'max_lat' => $lat + $degrees,
			'max_lon' => $lon + $degrees,
			'min_lat' => $lat - $degrees,
			'min_lon' => $lon - $degrees,
			);
		if (is_array($this->debug)) {
			$this->debug[__function__][] = compact('range', 'lat', 'lon', 'return');
		}
		return $return; 
	}
	
	
	/**
	* this does the math to find the actual distance between two sets of lat/lon coordinates
	* @param float $sourceLat
	* @param float $sourceLon
	* @param float $targetLat
	* @param float $targetLon
	* @param int $precision
	* @return float $distanceBetweenSourceAndTarget
	*/
	function calculatePrecisionDistance($sourceLat, $sourceLon, $targetLat, $targetLon, $precision = 2) {
		$earthsradius = 3963.19;
		$pi = pi();
		$c = sin($sourceLat/(180/$pi)) * sin($targetLat/(180/$pi)) +
		cos($sourceLat/(180/$pi)) * cos($targetLat/(180/$pi)) *
        cos($targetLon/(180/$pi) - $sourceLon/(180/$pi));
		$c = preg_replace("/1/", "1", $c);
		$distance = $earthsradius * acos($c);
		return round($distance, $precision);
	}
}
?>
