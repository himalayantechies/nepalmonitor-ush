<?php defined('SYSPATH') or die('No direct script access.');
?>
<h3>
	<a href="#" class="small-link-button f-clear reset" onclick="removeParameterKey('lkey', 'fl-layers');"><?php echo Kohana::lang('ui_main.clear')?></a>
	<a class="f-title" href="#"><?php echo Kohana::lang('kml_filter.layer')?></a>
</h3>
<div class="f-layer-box">
	<ul class="filter-list fl-layers" id="layer-filter-list">
		<?php echo kmlfilter_helper::get_layer_tree_view(); ?>
	</ul>
</div>