<!-- kml layer filters -->
<script>
//<![CDATA[
<?php echo $js; ?>
//]]>
</script>
<div class="cat-filters clearingfix">
	<strong>
	<?php echo Kohana::lang('ui_main.layers_filter');?>
		<span>
			[<a href="javascript:toggleLayer('kmlfilter_switch_link', 'kmlfilter_switch')" id="kmlfilter_switch_link">
				<?php echo Kohana::lang('ui_main.hide'); ?>
			</a>]
		</span>
	</strong>
</div>

<ul id="kmlfilter_switch" class="category-filters">
	<?php
		foreach ($kmlfilterlayers as $kmllayer => $kmllayer_info)
		{
			$kmllayer_title = htmlentities($kmllayer_info[0], ENT_QUOTES, "UTF-8");
			$kmllayer_color = $kmllayer_info[1];
			$kmllayer_image = '';
			
			$color_css = 'class="swatch" style="background-color:#'.$kmllayer_color.'"';
			
			echo '<li class="layer_type">'
			. '<a href="#" id="kmlfilter_'. $kmllayer .'" title="'.$kmllayer_title.'">'
			. '<span '.$color_css.'>'.$kmllayer_image.'</span>'
			. '<span class="layer-name">'.$kmllayer_title.'</span>'
			. '</a>';
			
			$layer_url = $kmllayer_info[2];
			$layer_file = $kmllayer_info[3];
			
			if ($layer_url != '') {
				// Pull from a URL
				$layer_link = $layer_url;
			} else {
				// Pull from an uploaded file
				$layer_link = Kohana::config('upload.directory').'/'.$layer_file;
			}
			echo '<div class="hide" id="kmlchild_'. $kmllayer .'">';
			if(file_exists($layer_link)) {
				$content = file_get_contents($layer_link);
				if ($content !== false) {
					$xml = simplexml_load_string($content);
					echo '<ul>';
					foreach($xml->Document->Placemark as $placemark) {
						$child_title = htmlentities($placemark->name, ENT_QUOTES, "UTF-8");
						$child_color = '';
						$child_image =  NULL;
						$child_description = htmlentities($placemark->description, ENT_QUOTES, "UTF-8");
							
						$color_css = 'class="swatch" style="background-color:#'.$child_color.'"';
						$child = $kmllayer.'_'.$placemark->ID;
						
						echo '<li style="padding-left:20px;" class="layer_child">'
						. '<a href="#" id="kmlfilter_'. $child .'" title="'.$child_description.'">'
						. '<span '.$color_css.'>'.$child_image.'</span>'
						. '<span class="category-title">'.$child_title.'</span>'
						. '</a>'
						. '</li>';
					}
					echo '</ul>';
				}
			}
			echo '</div></li>';
		}
	?>
</ul>
<!-- / kml layer filters -->