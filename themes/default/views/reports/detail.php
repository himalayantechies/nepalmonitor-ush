<div id="main" class="report_detail">
<div class="section group">
	
	<div class="left-col">
	<!--div class="col span_1_of_2"-->

  	  <?php
    	  if ($incident_verified)
    		{
    			echo '<p class="r_verified">'.Kohana::lang('ui_main.verified').'</p>';
    		}
    		else
    		{
    			echo '<p class="r_unverified">'.Kohana::lang('ui_main.unverified').'</p>';
    		}
  	  ?>

		<h1 class="report-title"><?php
			echo htmlentities($incident_title, ENT_QUOTES, "UTF-8");

			// If Admin is Logged In - Allow For Edit Link
			if ($logged_in)
			{
				echo " [&nbsp;<a href=\"".url::site()."admin/reports/edit/".$incident_id."\">"
				    .Kohana::lang('ui_main.edit')."</a>&nbsp;]";
			}
		?></h1>

		<p class="report-when-where">
			<span class="r_date"><?php echo $incident_time.' '.$incident_date; ?> </span>
			<span class="r_location"><?php echo html::specialchars($incident_location); ?></span>
			<?php Event::run('ushahidi_action.report_meta_after_time', $incident_id); ?>
		</p>
		<p>
			<?php if(!empty($adm_level)) { //get locations based on P-Code -NH
			$admList = location_filter::get_adm_levels($adm_level, $pcode);
			foreach(location_filter::$admLevels as $key => $admLvl) {
				if(isset($admList[$key])) {
					echo '<span style="display:inline-block"><strong>'.$admLvl['label'].' (O): </strong>'.$admList[$key]->name.'&nbsp;&nbsp;</span>';
				}
			}
			echo '<hr/>';
			foreach(location_filter::$admLevels as $key => $admLvl) {
				if(isset($admList[$key])) {
					echo '<span style="display:inline-block"><strong>'.$admLvl['label'].' (N): </strong>'.$admList[$key]->new_name.'&nbsp;&nbsp;</span>';
				}
			}// Display location accuracy and HLCIT code - NH
			?>
			<br><span><strong><?php echo Kohana::lang('ui_main.adm_level').': ';?></strong> 
			<?php echo location_filter::$admLevels[$adm_level]['label'].'&nbsp;&nbsp;'; ?></span>
			<span><strong><?php echo Kohana::lang('ui_main.pcode').': ';?></strong>
			<?php echo $pcode; ?></span><br/>
			<?php 
			
			}
			?>
		</p>

		<div class="report-category-list">
		<p>
			<?php
				foreach ($incident_category as $category)
				{
					// don't show hidden categoies
					if ($category->category->category_visible == 0)
					{
						continue;
					}
					if ($category->category->category_image_thumb)
					{
						$style = "background:transparent url(".url::convert_uploaded_to_abs($category->category->category_image_thumb).") 0 0 no-repeat";
					}
					else
					{
						$style = "background-color:#".$category->category->category_color;
					}
					
					?>
					<a href="<?php echo url::site()."reports/?c=".$category->category->id; ?>" title="<?php echo Category_Lang_Model::category_description($category->category_id);; ?>">
						<span class="r_cat-box" style="<?php echo $style ?>">&nbsp;</span>
						<?php echo Category_Lang_Model::category_title($category->category_id); ?>
					</a>
					<?php 
				}
			?>
			</p>
			<?php
			// Action::report_meta - Add Items to the Report Meta (Location/Date/Time etc.)
			Event::run('ushahidi_action.report_meta', $incident_id);
			?>
		</div>

		<?php
		// Action::report_display_media - Add content just above media section
	    Event::run('ushahidi_action.report_display_media', $incident_id);
		?>

		<!-- start report media -->
		<div class="<?php if( count($incident_photos) > 0 || count($incident_videos) > 0){ echo "report-media";}?>">
	    <?php
	    // if there are images, show them
	    if( count($incident_photos) > 0 )
	    {
			echo '<div id="report-images">';
			foreach ($incident_photos as $photo)
			{
				echo '<a class="photothumb" rel="lightbox-group1" href="'.$photo['large'].'"><img alt="'.htmlentities($incident_title, ENT_QUOTES, "UTF-8").'" src="'.$photo['thumb'].'"/></a> ';
			};
			echo '</div>';
	    }

	    // if there are videos, show those too
	    if( count($incident_videos) > 0 )
	    {
	      echo '<div id="report-video"><ol>';

          // embed the video codes
          foreach( $incident_videos as $incident_video)
          {
            echo '<li>';
            $videos_embed->embed($incident_video,'');
            echo '</li>';
          };
  			echo '</ol></div>';

	    }
	    ?>
		</div>

		<!-- start report description -->
		<div class="report-description-text">
			<h5><?php echo Kohana::lang('ui_main.reports_description');?></h5>
			<?php echo nl2br($incident_description); ?>
			<br/><br/>
			
			<!-- start news source link -->
			<?php if( count($incident_news) > 0 ) { ?>
			<div class="credibility">
			<h5><?php echo Kohana::lang('ui_main.reports_news');?></h5>
					<?php
						foreach( $incident_news as $incident_new)
						{
							if(valid::url($incident_new)) {
							?>
							<a href="<?php echo $incident_new; ?> " target="_blank"><?php
							echo $incident_new;?></a>
							<?php } else {
								echo $incident_new;
							} ?>
							<br/>
							<?php
						}
			?>
			</div>
			<?php } ?>
			<!-- end news source link -->
			
			<!-- start news source type link -->
			<?php if( count($incident_news_types) > 0 ) { ?>
			<div class="credibility">
			<h5><?php echo Kohana::lang('ui_main.source_type');?></h5>
					<?php
						foreach( $incident_news_types as $incident_news_type)
						{
							if(valid::url($incident_news_type)) {
							?>
							<a href="<?php echo $incident_news_type; ?> " target="_blank"><?php
							echo $incident_news_type;?></a>
							<?php } else {
								echo $incident_news_type;
							} ?>
							<br/>
							<?php
						}
			?>
			</div>
			<?php } ?>
			<!-- end news source type link -->
			
			<!-- start media link -->
			<?php if( count($incident_medias) > 0 ) { ?>
			<div class="credibility">
			<h5><?php echo Kohana::lang('ui_main.reports_medias');?></h5>
					<?php
						foreach( $incident_medias as $incident_media)
						{
							if(valid::url($incident_media)) {
							?>
							<a href="<?php echo $incident_media; ?> " target="_blank"><?php
							echo $incident_media;?></a>
							<br/>
							<?php }
						}
			?>
			</div>
			<?php } ?>
			<!-- end media link -->
			
			<!-- start related incident link -->
			<?php if( count($incident_relateds) > 0 ) { ?>
			<div class="credibility">
			<h5><?php echo Kohana::lang('ui_main.related_incident_link');?></h5>
					<?php
						foreach( $incident_relateds as $incident_related)
						{
							if(valid::url($incident_related)) {
							?>
							<a href="<?php echo $incident_related; ?> " target="_blank"><?php
							echo $incident_related;?></a>
							<br/>
							<?php }
						}
			?>
			</div>
			<?php } ?>
			<!-- end related incident link -->

			<!-- start additional fields -->
			<?php if(strlen($custom_forms) > 0) { ?>
			<div class="credibility">
			<h5><?php echo Kohana::lang('ui_main.additional_data');?></h5>
			<?php

				echo $custom_forms;

			?>
			<br/>
			</div>
			<?php } ?>
			<!-- end additional fields -->

			<?php if ($features_count)
			{
				?>
				<br /><br /><h5><?php echo Kohana::lang('ui_main.reports_features');?></h5>
				<?php
				foreach ($features as $feature)
				{
					echo ($feature->geometry_label) ?
					 	"<div class=\"feature_label\"><a href=\"javascript:getFeature($feature->id)\">$feature->geometry_label</a></div>" : "";
					echo ($feature->geometry_comment) ?
						"<div class=\"feature_comment\">$feature->geometry_comment</div>" : "";
				}
			}?>

			<div class="credibility">
				<table class="rating-table" cellspacing="0" cellpadding="0" border="0">
          <tr>
            <td><?php echo Kohana::lang('ui_main.credibility');?>:</td>
            <td><a href="javascript:rating('<?php echo $incident_id; ?>','add','original','oloader_<?php echo $incident_id; ?>')"><img id="oup_<?php echo $incident_id; ?>" src="<?php echo url::file_loc('img'); ?>media/img/up.png" alt="UP" title="UP" border="0" /></a></td>
            <td><a href="javascript:rating('<?php echo $incident_id; ?>','subtract','original')"><img id="odown_<?php echo $incident_id; ?>" src="<?php echo url::file_loc('img'); ?>media/img/down.png" alt="DOWN" title="DOWN" border="0" /></a></td>
            <td><a href="" class="rating_value" id="orating_<?php echo $incident_id; ?>"><?php echo $incident_rating; ?></a></td>
            <td><a href="" id="oloader_<?php echo $incident_id; ?>" class="rating_loading" ></a></td>
          </tr>
        </table>
			</div>
		</div>

		<?php
            // Action::report_extra - Allows you to target an individual report right after the description
            Event::run('ushahidi_action.report_extra', $incident_id);

			// Filter::comments_block - The block that contains posted comments
			Event::run('ushahidi_filter.comment_block', $comments);
			echo $comments;
		?>

		<?php
			// Filter::comments_form_block - The block that contains the comments form
			Event::run('ushahidi_filter.comment_form_block', $comments_form);
			echo $comments_form;
		?>

	</div>

	<div class="right-col">
	<!--div class="col span_1_of_3"-->

		<div class="report-media-box-content">

			<div id="report-map" class="report-map">
				<div class="map-holder" id="map"></div>
        <ul class="map-toggles">
          <li><a href="#" class="smaller-map"><?php echo Kohana::lang('ui_main.smaller_map'); ?></a></li>
          <li style="display:block;"><a href="#" class="wider-map"><?php echo Kohana::lang('ui_main.wider_map'); ?></a></li>
          <li><a href="#" class="taller-map"><?php echo Kohana::lang('ui_main.taller_map'); ?></a></li>
          <li><a href="#" class="shorter-map"><?php echo Kohana::lang('ui_main.shorter_map'); ?></a></li>
        </ul>
        <div style="clear:both"></div>
			</div>
		</div>

		<?php
			// Action::report_view_sidebar - This gives plugins the ability to insert into the sidebar (below the map and above additional reports)
			Event::run('ushahidi_action.report_view_sidebar', $incident_id);
		?>

		<div class="report-additional-reports">
			<h4><?php echo Kohana::lang('ui_main.additional_reports');?></h4>
			<?php foreach($incident_neighbors as $neighbor) { ?>
			  <div class="rb_report">
  			  <h5><a href="<?php echo url::site(); ?>reports/view/<?php echo $neighbor->id; ?>"><?php echo $neighbor->incident_title; ?></a></h5>
  			  <p class="r_date r-3 bottom-cap"><?php echo date('H:i M d, Y', strtotime($neighbor->incident_date)); ?></p>
  			  <p class="r_location"><?php echo $neighbor->location_name.", ".round($neighbor->distance, 2); ?> Kms</p>
  			</div>
      <?php } ?>
		</div>
<!---Start facebook likebox-->
			<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fnepalmonitor&amp;width=350&amp;height=558&amp;colorscheme=light&amp;show_faces=true&amp;border_color&amp;stream=true&amp;header=false&amp;appId=140091756132653" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:100%; height:558px;" allowTransparency="true"></iframe>
			<!---end facebook likebox--->

	</div>

	<div style="clear:both;"></div>




</div>
