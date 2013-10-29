<!-- AddThis Button BEGIN -->
<?php
	$default_show = array(
		'facebook','twitter','addthis'
	);
	$script = isset($script) ? $script : true;
	$username = isset($username) ? $username : null;
	$show = (isset($show) && !empty($show)) ? $show : $default_show; 
?>
<div class="addthis_toolbox addthis_default_style"<?php if(isset($url)): ?> addthis:url="<?php echo $url ?>"<?php endif;?>>
<?php if(in_array('facebook', $show)): ?>
	<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
<?php endif; ?>
<?php if(in_array('twitter', $show)): ?>
	<a class="addthis_button_tweet"></a>
<?php endif; ?>
<?php if(in_array('addthis', $show)): ?>
	<a class="addthis_counter addthis_pill_style"></a>
<?php endif; ?>
</div>
<?php if($script): ?>
	<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=<?php echo $username; ?>"></script>
<?php endif ;?>
<!-- AddThis Button END -->