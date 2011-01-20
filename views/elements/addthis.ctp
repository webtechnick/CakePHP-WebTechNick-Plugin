<!-- AddThis Button BEGIN -->
<?php
	$script = isset($script) ? $script : true;
	$username = isset($username) ? $username : null;
?>
<div class="addthis_toolbox addthis_default_style ">
<a class="addthis_button_facebook_like" fb:like:layout="button_count"></a>
<a class="addthis_button_tweet"></a>
<a class="addthis_counter addthis_pill_style"></a>
</div>
<?php if($script): ?>
	<script type="text/javascript">var addthis_config = {"data_track_clickback":true};</script>
	<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#username=<?php echo $username; ?>"></script>
<?php endif ;?>
<!-- AddThis Button END -->