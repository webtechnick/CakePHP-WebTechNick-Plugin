<?php
class GeoLocComponent extends Component{
  
  var $server = "http://www.geobytes.com/IpLocator.htm?GetLocation&template=php3.txt&IpAddress=";
  
  function data($ip = null){
    if($ip){
      $tags = get_meta_tags($this->server . $ip);
      return $tags;
    }
    return array();
  }
  
}
?>