<?php defined('SYSPATH') or die('No direct script access.'); ?>
<style>
/* Hide default other layers
*/
#category_switch + .cat-filters { display: none !important; }
#kml_switch { display: none !important; }
</style>
<script type="text/javascript">
//<![CDATA[
/**
 * Remove default other layers
 */
 $(function() {
	 $('#kml_switch').prev('.cat-filters:first').remove();
	 $('#kml_switch').remove();
 });
//]]>
</script>