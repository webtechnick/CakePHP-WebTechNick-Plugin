<?php
/**
*	Disqus CakePHP Element
*
* @author Nick Baker
* @version 0.1
* @license MIT
* @link http://www.webtechnick.com
*
*
* Setup account at http://disqus.com
* @reference http://docs.disqus.com/help/2/
* @reference http://docs.disqus.com/developers/universal/
*
*/


	$shortname = isset($shortname) ? $shortname : 'example';
	$identifier = isset($identifier) ? $identifier : null;
	$url = isset($url) ? $url : null;
	$title = isset($title) ? $title : null;
	$dev = isset($dev) ? $dev : false;
	$count = isset($count) ? $count : false;
?>
<a name="disqus_comments"></a>
<div id="disqus_thread"></div>
<script type="text/javascript">
    /* * * CONFIGURATION VARIABLES: EDIT BEFORE PASTING INTO YOUR WEBPAGE * * */
    var disqus_shortname = '<?php echo $shortname; ?>'; // required: replace example with your forum shortname

    // The following are highly recommended additional parameters. Remove the slashes in front to use.
    <?php
			if($identifier){
				echo "var disqus_identifier = '$identifier';";
			}
			if($url){
				echo "var disqus_url = '$url';";
			}
			if($title){
				echo "var disqus_title = '$title';";
			}
			if($dev){
				echo "var disqus_developer = 1;";
			}
    ?>

    /* * * DON'T EDIT BELOW THIS LINE * * */
    (function() {
        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
    })();
    <?php if($count): ?>
    (function () {
        var s = document.createElement('script'); s.async = true;
        s.type = 'text/javascript';
        s.src = 'http://' + disqus_shortname + '.disqus.com/count.js';
        (document.getElementsByTagName('HEAD')[0] || document.getElementsByTagName('BODY')[0]).appendChild(s);
    }());
    <?php endif; ?>
</script>