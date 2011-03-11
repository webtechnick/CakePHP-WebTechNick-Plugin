<?php
class CkeditorHelper extends AppHelper{
	var $helpers = array('Html');
	var $loaded = false;
	
	function load(){
		$this->loaded = true;
		return $this->Html->script('/ckeditor/ckeditor');
	}
	
	function replace($id = null, $options = array()){
		$retval = "";
		if(!$this->loaded){
			$retval .= $this->load();
		}
		
		if(isset($options['ckfinder']) && $options['ckfinder']){
			unset($options['ckfinder']);
			$options['filebrowserBrowseUrl'] = '/ckfinder/ckfinder.html';
			$options['filebrowserImageBrowseUrl'] = '/ckfinder/ckfinder.html?Type=Images';
      $options['filebrowserFlashBrowseUrl'] = '/ckfinder/ckfinder.html?Type=Flash';
      $options['filebrowserUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files';
      $options['filebrowserImageUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images';
      $options['filebrowserFlashUploadUrl'] = '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Flash';
		}
		
		$options = json_encode(array_merge(
			array(
				'skin' => 'kama',
			),
			$options
		));
		
		$retval .= $this->Html->scriptBlock(
			"var wtn_editor = CKEDITOR.replace('$id', $options)"
		);
		
		return $retval; 
	}
	
	/**
	* Destroy the editor
	*/
	function destroy(){
		return $this->Html->scriptBlock("
			wtn_editor.destroy();
		");
	}
}
?>