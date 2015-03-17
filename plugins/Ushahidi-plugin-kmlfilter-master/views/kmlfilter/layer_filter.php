<?php defined('SYSPATH') or die('No direct script access.'); ?>
<!-- layer filters -->
<?php
foreach($layers as $layer) {
	echo '<li>';
	if(isset($layerChildrens[$layer->id])) {
		echo '<span class="ui-icon ui-icon-triangle-1-e" style="float:right;"></span>';
	}
	echo '<a href="#" class="lyr_selected" id="filter_link_lyr_'.$layer->id.'" title="'.$layer->layer_name.'">'
			. '<span>'.strip_tags($layer->layer_name).'</span>'
		. '</a>';
		echo '<div id="filter_child_link_lyr_'.$layer->id.'" class="hide"><ul>';
			if(isset($layerChildrens[$layer->id])) {
				foreach($layerChildrens[$layer->id] as $placemark) {
					$child_title = htmlentities($placemark->name, ENT_QUOTES, "UTF-8");
					$child_description = htmlentities($placemark->description, ENT_QUOTES, "UTF-8");
					echo '<li class="report-listing-category-child">'
					. '<a href="#" class="lyr_selected" id="filter_link_lyr_'.$layer->id.'_'.$placemark->ID.'" title="'.$child_description.'">'
					. '<span>'.$child_title.'</span>'
					. '</a></li>';
				}
			}
		echo '</ul></div>';
	echo '</li>';
}
?>
<!-- / layer filters -->
<script type="text/javascript">
//<![CDATA[
<?php echo $js; ?>
//]]>
</script>