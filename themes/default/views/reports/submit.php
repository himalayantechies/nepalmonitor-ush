<?php
// HT: Start of css and script for select2 autocomplete
echo html::stylesheet(url::file_loc('css')."media/css/select2.min","",TRUE);
echo html::script(url::file_loc('js')."media/js/select2/select2.min", TRUE);
// HT: End of css and script for select2 autocomplete
?>
<div id="content">
	<div class="content-bg">

		<?php if ($site_submit_report_message != ''): ?>
			<div class="green-box" style="margin: 25px 25px 0px 25px">
				<h3><?php echo $site_submit_report_message; ?></h3>
			</div>
		<?php endif; ?>

		<!-- start report form block -->
		<?php print form::open(NULL, array('enctype' => 'multipart/form-data', 'id' => 'reportForm', 'name' => 'reportForm', 'class' => 'gen_forms')); ?>
		<input type="hidden" name="latitude" id="latitude" value="<?php echo $form['latitude']; ?>">
		<input type="hidden" name="longitude" id="longitude" value="<?php echo $form['longitude']; ?>">
		<input type="hidden" name="country_name" id="country_name" value="<?php echo $form['country_name']; ?>" />
		<input type="hidden" name="incident_zoom" id="incident_zoom" value="<?php echo $form['incident_zoom']; ?>" />
		<div class="big-block">
			<h1><?php echo Kohana::lang('ui_main.reports_submit_new'); ?></h1>
			<?php if ($form_error): ?>
			<!-- red-box -->
			<div class="red-box">
				<h3>Error!</h3>
				<ul>
					<?php
						foreach ($errors as $error_item => $error_description)
						{
							print (!$error_description) ? '' : "<li>" . $error_description . "</li>";
						}
					?>
				</ul>
			</div>
			<?php endif; ?>
			<div class="row">
				<input type="hidden" name="form_id" id="form_id" value="<?php echo $id?>">
			</div>
			<div class="report_left">
				<div class="report_row">
					<?php if(count($forms) > 1): ?>
					<div class="row">
						<h4><span><?php echo Kohana::lang('ui_main.select_form_type');?></span>
						<span class="sel-holder">
							<?php print form::dropdown('form_id', $forms, $form['form_id'],
						' onchange="formSwitch(this.options[this.selectedIndex].value, \''.$id.'\')"') ?>
						</span>
						<div id="form_loader" style="float:left;"></div>
						</h4>
					</div>
					<?php endif; ?>
					<h4><?php echo Kohana::lang('ui_main.reports_title'); ?> <span class="required">*</span> </h4>
					<?php print form::input('incident_title', $form['incident_title'], ' class="text long"'); ?>
				</div>
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_description'); ?> <span class="required">*</span> </h4>
					<?php print form::textarea('incident_description', $form['incident_description'], ' rows="10" class="textarea long" ') ?>
				</div>
				<div class="report_row" id="datetime_default">
					<h4>
						<a href="#" id="date_toggle" class="show-more"><?php echo Kohana::lang('ui_main.modify_date'); ?></a>
						<?php echo Kohana::lang('ui_main.date_time'); ?>: 
						<?php echo Kohana::lang('ui_main.today_at')." "."<span id='current_time'>".$form['incident_hour']
							.":".$form['incident_minute']." ".$form['incident_ampm']."</span>"; ?>
						<?php if($site_timezone): ?>
							<small>(<?php echo $site_timezone; ?>)</small>
						<?php endif; ?>
					</h4>
				</div>
				<div class="report_row hide" id="datetime_edit">
					<div class="date-box">
						<h4><?php echo Kohana::lang('ui_main.reports_date'); ?></h4>
						<?php print form::input('incident_date', $form['incident_date'], ' class="text short"'); ?>
						<script type="text/javascript">
							$().ready(function() {
								$("#incident_date").datepicker({ 
									showOn: "both", 
									buttonImage: "<?php echo url::file_loc('img'); ?>media/img/icon-calendar.gif", 
									buttonImageOnly: true 
								});
							});
						</script>
					</div>
					<div class="time">
						<h4><?php echo Kohana::lang('ui_main.reports_time'); ?></h4>
						<?php
							for ($i=1; $i <= 12 ; $i++)
							{
								// Add Leading Zero
								$hour_array[sprintf("%02d", $i)] = sprintf("%02d", $i);
							}
							for ($j=0; $j <= 59 ; $j++)
							{
								// Add Leading Zero
								$minute_array[sprintf("%02d", $j)] = sprintf("%02d", $j);
							}
							$ampm_array = array('pm'=>'pm','am'=>'am');
							print form::dropdown('incident_hour',$hour_array,$form['incident_hour']);
							print '<span class="dots">:</span>';
							print form::dropdown('incident_minute',$minute_array,$form['incident_minute']);
							print '<span class="dots">:</span>';
							print form::dropdown('incident_ampm',$ampm_array,$form['incident_ampm']);
						?>
						<?php if ($site_timezone != NULL): ?>
							<small>(<?php echo $site_timezone; ?>)</small>
						<?php endif; ?>
					</div>
					<div style="clear:both; display:block;" id="incident_date_time"></div>
				</div>
				<div class="report_row">
					<!-- Adding event for endtime plugin to hook into -->
				<?php Event::run('ushahidi_action.report_form_frontend_after_time'); ?>
				</div>
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_categories'); ?> <span class="required">*</span></h4>
					<div class="report_category" id="categories">
					<?php
						$selected_categories = (!empty($form['incident_category']) AND is_array($form['incident_category']))
							? $selected_categories = $form['incident_category']
							: array();
							
						
						echo category::form_tree('incident_category', $selected_categories, 2);
						?>
					</div>
				</div>

				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_alert_mode'); ?></h4>
					<?php print form::dropdown('alert_mode',$alert_mode,'0', ' class="select" '); ?>
				</div>
				<?php
				// Action::report_form - Runs right after the report categories
				Event::run('ushahidi_action.report_form');
				?>

				<?php echo $custom_forms ?>

				<div class="report_optional">
					<h3><?php echo Kohana::lang('ui_main.reports_optional'); ?></h3>
					<div class="report_row">
						<h4><?php echo Kohana::lang('ui_main.reports_first'); ?></h4>
						<?php print form::input('person_first', $form['person_first'], ' class="text long"'); ?>
					</div>
					<div class="report_row">
						<h4><?php echo Kohana::lang('ui_main.reports_last'); ?></h4>
						<?php print form::input('person_last', $form['person_last'], ' class="text long"'); ?>
					</div>
					<div class="report_row">
						<h4><?php echo Kohana::lang('ui_main.reports_email'); ?></h4>
						<?php print form::input('person_email', $form['person_email'], ' class="text long"'); ?>
					</div>
					<?php
					// Action::report_form_optional - Runs in the optional information of the report form
					Event::run('ushahidi_action.report_form_optional');
					?>
				</div>
			</div>
			<div class="report_right">
				<?php if (count($cities) > 1): ?>
				<div class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_find_location'); ?></h4>
					<?php print form::dropdown('select_city',$cities,'', ' class="select" '); ?>
				</div>
				<?php endif; ?>
				<div class="report_row">
					<div id="divMap" class="report_map">
						<div id="geometryLabelerHolder" class="olControlNoSelect">
							<div id="geometryLabeler">
								<div id="geometryLabelComment">
									<span id="geometryLabel">
										<label><?php echo Kohana::lang('ui_main.geometry_label');?>:</label> 
										<?php print form::input('geometry_label', '', ' class="lbl_text"'); ?>
									</span>
									<span id="geometryComment">
										<label><?php echo Kohana::lang('ui_main.geometry_comments');?>:</label> 
										<?php print form::input('geometry_comment', '', ' class="lbl_text2"'); ?>
									</span>
								</div>
								<div>
									<span id="geometryColor">
										<label><?php echo Kohana::lang('ui_main.geometry_color');?>:</label> 
										<?php print form::input('geometry_color', '', ' class="lbl_text"'); ?>
									</span>
									<span id="geometryStrokewidth">
										<label><?php echo Kohana::lang('ui_main.geometry_strokewidth');?>:</label> 
										<?php print form::dropdown('geometry_strokewidth', $stroke_width_array, ''); ?>
									</span>
									<span id="geometryLat">
										<label><?php echo Kohana::lang('ui_main.latitude');?>:</label> 
										<?php print form::input('geometry_lat', '', ' class="lbl_text"'); ?>
									</span>
									<span id="geometryLon">
										<label><?php echo Kohana::lang('ui_main.longitude');?>:</label> 
										<?php print form::input('geometry_lon', '', ' class="lbl_text"'); ?>
									</span>
								</div>
							</div>
							<div id="geometryLabelerClose"></div>
						</div>
					</div>
					<div class="report-find-location">
					    <div id="panel" class="olControlEditingToolbar"></div>
						<div class="btns" style="float:left;">
							<ul style="padding:4px;">
								<li><a href="#" class="btn_del_last"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_last'));?></a></li>
								<li><a href="#" class="btn_del_sel"><?php echo utf8::strtoupper(Kohana::lang('ui_main.delete_selected'));?></a></li>
								<li><a href="#" class="btn_clear"><?php echo utf8::strtoupper(Kohana::lang('ui_main.clear_map'));?></a></li>
							</ul>
						</div>
						<div style="clear:both;"></div>
						<?php print form::input('location_find', '', ' title="'.Kohana::lang('ui_main.location_example').'" class="findtext"'); ?>
						<div style="float:left;margin:9px 0 0 5px;">
							<input type="button" name="button" id="button" value="<?php echo Kohana::lang('ui_main.find_location'); ?>" class="btn_find" />
						</div>
						<div id="find_loading" class="report-find-loading"></div>
						<div style="clear:both;" id="find_text"><?php echo Kohana::lang('ui_main.pinpoint_location'); ?>.</div>
					</div>
				</div>
				<?php Event::run('ushahidi_action.report_form_location', $id); ?>

				<!-- Location Search Field -->
				<div id="locationSearch" class="report_row">
					<h4><?php echo Kohana::lang('ui_main.search_location'); ?></h4>
						<input type="longtext " class = "text long" id="location_search" placeholder="Enter the location" />
						<div id="location_suggesstion"></div>
					<?php
					$url_loc = url::site().'json/autosearch_location'; ?>
					<script type="text/javascript">
						$(document).ready(function(){
							$('#location_search').autocomplete({
								source: function( request, response ){
									<?php echo " $.getJSON('".$url_loc."', { keyword: $('#location_search').val() }, response);" ?>
								},
								search: function() {
							        var keyword = $('#location_search').val();
	    						    if ( keyword.length < 2 ) {
	        					    	return false;
	        						}
  							    },
  							    select: function( event, ui){
							        $( '#location_search' ).val( ui.item.label );
							        //$( '#location_name' ).val( ui.item.value );
							        $( '#latitude' ).val( ui.item.y_coord );
							        $( '#longitude' ).val( ui.item.x_coord ).trigger('focusout');
							        $('#adm_level').val(5).trigger('change');
							        $('#getPcode').trigger('click');
							        return false; 
  							    }
  							})
  							.autocomplete("widget").addClass("ac-location");  
        				});
					</script>
				</div>

				<div class="report_row">
					<h4>
						<?php echo Kohana::lang('ui_main.reports_adm_level'); ?> 
						<span class="required">*</span> <small><a href="javascript:void();" onclick="getPcode()" id="getPcode" style="display: none;">Get HLCIT Code</a></small><br />
						<span id="adm_location" class="example"></span>
					</h4>
					<?php print form::dropdown(array('name' => 'adm_level', 'id' => 'adm_level', 'required' => 'required'), $adm_levels, $form['adm_level']); ?>
					<?php print form::input(array('name'=>'pcode', 'type'=>'hidden', 'id'=>'pcode', 'value' => $form['pcode'])); ?>

				</div>
				<div class="report_row">
					<h4>
						<?php echo Kohana::lang('ui_main.reports_location_name'); ?> 
						<span class="required">*</span> <br />
						<span class="example"><?php echo Kohana::lang('ui_main.detailed_location_example'); ?></span>
					</h4>
					<?php print form::input('location_name', $form['location_name'], ' class="text long"'); ?>
					<?php //print form::input(array('name'=>'location_name','id'=>'location_name_pcode', 'value' => $form['location_name'], 'class' => 'text long', 'readonly' => 'readonly')); ?>
					
				</div>
				
				<!-- News Fields -->
				<div id="divNews" class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_news'); ?></h4>
					
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_news'])) ? 1 : 0;
						$newsoptions = array('' => '- Please Select -');
					?>

					<?php if (empty($form['incident_news'])): ?>
						<div class="report_row">
							<?php //print form::input('incident_news[]', '', ' class="text long2"'); ?>
							<?php print form::dropdown('incident_news[]', $newsoptions, '', ' class="text long2 incident_news"'); ?>
							<a href="#" class="add" onClick="addFormField('divNews','incident_news','news_id','autosearch'); return false;">add</a>
						</div>
					<?php else: ?>
						<?php foreach ($form['incident_news'] as $value): ?>
						<div class="report_row" id="<?php echo $i; ?>">
							<?php echo form::dropdown('incident_news[]', $newsoptions, $value, ' class="text long2 incident_news"'); ?>
							<?php //echo form::input('incident_news[]', $value, ' class="text long2"'); ?>
							
							<?php if ($i != 0): ?>
								<?php $css_id = "#incident_news_".$i; ?>
								<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
							<?php endif; ?>
							
							<a href="#" class="add" onClick="addFormField('divNews','incident_news','news_id','autosearch'); return false;">add</a>

						</div>
						<?php $i++; ?>

						<?php endforeach; ?>
					<?php endif; 
					$field_file = url::site().'/media/news_source.json';
					echo "<script type=\"text/javascript\">
						$(function(){
							$.ajax({
								url: \"".$field_file."\",
								dataType: 'json',
								success: function(data) {
									newsList = data.items;
									$(\"#divNews select.incident_news\").select2({
									  data: newsList,
									  tags: true
									});
								}
							});	
						});
					</script>";
					?>
					<?php print form::input(array('name'=>'news_id', 'type'=>'hidden', 'id'=>'news_id'), $i); ?>
				</div>

				<!-- News Type Fields -->
				<div id="divNewsType" class="report_row">
					<h4><?php echo Kohana::lang('ui_main.source_type'); ?></h4>
					
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_news_type'])) ? 1 : 0;
						$newstypeoptions = array('' => '- Please Select -');
					?>

					<?php if (empty($form['incident_news_type'])): ?>
						<div class="report_row">
							<?php //print form::input('incident_news_type[]', '', ' class="text long2"'); ?>
							<?php print form::dropdown('incident_news_type[]', $newstypeoptions, '', ' class="text long2 incident_news_type"'); ?>
							<a href="#" class="add" onClick="addFormField('divNewsType','incident_news_type','news_type_id','autosearch'); return false;">add</a>
						</div>
					<?php else: ?>
						<?php foreach ($form['incident_news_type'] as $value): ?>
						<div class="report_row" id="<?php echo $i; ?>">
							<?php echo form::dropdown('incident_news_type[]', $newstypeoptions, $value, ' class="text long2 incident_news_type"'); ?>
							<?php //echo form::input('incident_news_type[]', $value, ' class="text long2"'); ?>
							
							<?php if ($i != 0): ?>
								<?php $css_id = "#incident_news_type_".$i; ?>
								<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
							<?php endif; ?>
							
							<a href="#" class="add" onClick="addFormField('divNewsType','incident_news_type','news_type_id','autosearch'); return false;">add</a>

						</div>
						<?php $i++; ?>

						<?php endforeach; ?>
					<?php endif; 
					$field_file = url::site().'/media/news_source_type.json';
					echo "<script type=\"text/javascript\">
						$(function(){
							$.ajax({
								url: \"".$field_file."\",
								dataType: 'json',
								success: function(data) {
									newsTypeList = data.items;
									$(\"#divNewsType select.incident_news_type\").select2({
									  data: newsTypeList,
									  tags: false
									});
								}
							});
						
					});
					</script>";
					?>
					<?php print form::input(array('name'=>'news_id', 'type'=>'hidden', 'id'=>'news_type_id'), $i); ?>
				</div>

				<!-- Video Fields -->
				<div id="divVideo" class="report_row">
					<h4><?php print Kohana::lang('ui_main.external_video_link'); ?></h4>
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_video'])) ? 1 : 0;
					?>

					<?php if (empty($form['incident_video'])): ?>
						<div class="report_row">
							<?php print form::input('incident_video[]', '', ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divVideo','incident_video','video_id','text'); return false;">add</a>
						</div>
					<?php else: ?>
						<?php foreach ($form['incident_video'] as $value): ?>
							<div class="report_row" id="<?php  echo $i; ?>">

							<?php print form::input('incident_video[]', $value, ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divVideo','incident_video','video_id','text'); return false;">add</a>

							<?php if ($i != 0): ?>
								<?php $css_id = "#incident_video_".$i; ?>
								<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
							<?php endif; ?>

							</div>
							<?php $i++; ?>
						
						<?php endforeach; ?>
					<?php endif; ?>

					<?php print form::input(array('name'=>'video_id','type'=>'hidden','id'=>'video_id'), $i); ?>
				</div>
				
				<?php Event::run('ushahidi_action.report_form_after_video_link'); ?>
				
				<!-- Media Fields -->
				<div id="divMedia" class="report_row">
					<h4><?php print Kohana::lang('ui_main.external_media_link'); ?></h4>
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_media'])) ? 1 : 0;
					?>

					<?php if (empty($form['incident_media'])): ?>
						<div class="report_row">
							<?php print form::input('incident_media[]', '', ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divMedia','incident_media','media_id','text'); return false;">add</a>
						</div>
					<?php else: ?>
						<?php foreach ($form['incident_media'] as $value): ?>
							<div class="report_row" id="<?php  echo $i; ?>">

							<?php print form::input('incident_media[]', $value, ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divMedia','incident_media','media_id','text'); return false;">add</a>

							<?php if ($i != 0): ?>
								<?php $css_id = "#incident_media_".$i; ?>
								<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
							<?php endif; ?>

							</div>
							<?php $i++; ?>
						
						<?php endforeach; ?>
					<?php endif; ?>

					<?php print form::input(array('name'=>'media_id','type'=>'hidden','id'=>'media_id'), $i); ?>
				</div>
				
				<?php Event::run('ushahidi_action.report_form_after_media_link'); ?>
				
				<!-- Related Incident Fields -->
				<div id="divRelatedIncident" class="report_row">
					<h4><?php print Kohana::lang('ui_main.related_incident_link'); ?></h4>
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_related'])) ? 1 : 0;
					?>

					<?php if (empty($form['incident_related'])): ?>
						<div class="report_row">
							<?php print form::input('incident_related[]', '', ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divRelatedIncident','incident_related','related_id','text'); return false;">add</a>
						</div>
					<?php else: ?>
						<?php foreach ($form['incident_related'] as $value): ?>
							<div class="report_row" id="<?php  echo $i; ?>">

							<?php print form::input('incident_related[]', $value, ' class="text long2"'); ?>
							<a href="#" class="add" onClick="addFormField('divRelatedIncident','incident_related','related_id','text'); return false;">add</a>

							<?php if ($i != 0): ?>
								<?php $css_id = "#incident_related_".$i; ?>
								<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
							<?php endif; ?>

							</div>
							<?php $i++; ?>
						
						<?php endforeach; ?>
					<?php endif; ?>

					<?php print form::input(array('name'=>'related_id','type'=>'hidden','id'=>'related_id'), $i); ?>
				</div>
				
				<?php Event::run('ushahidi_action.report_form_after_related_link'); ?>
				
				<!-- Photo Fields -->
				<div id="divPhoto" class="report_row">
					<h4><?php echo Kohana::lang('ui_main.reports_photos'); ?></h4>
					<?php 
						// Initialize the counter
						$i = (empty($form['incident_photo']['name'][0])) ? 1 : 0;
					?>

					<?php if (empty($form['incident_photo']['name'][0])): ?>
					<div class="report_row">
						<?php print form::upload('incident_photo[]', '', ' class="file long2"'); ?>
						<a href="#" class="add" onClick="addFormField('divPhoto', 'incident_photo','photo_id','file'); return false;">add</a>
					</div>
					<?php else: ?>
						<?php foreach ($form['incident_photo']['name'] as $value): ?>

							<div class="report_row" id="<?php echo $i; ?>">
								<?php print form::upload('incident_photo[]', $value, ' class="file long2"'); ?>
								<a href="#" class="add" onClick="addFormField('divPhoto','incident_photo','photo_id','file'); return false;">add</a>

								<?php if ($i != 0): ?>
									<?php $css_id = "#incident_photo_".$i; ?>
									<a href="#" class="rem"	onClick="removeFormField('<?php echo $css_id; ?>'); return false;">remove</a>
								<?php endif; ?>

							</div>

							<?php $i++; ?>

						<?php endforeach; ?>
					<?php endif; ?>

					<?php print form::input(array('name'=>'photo_id','type'=>'hidden','id'=>'photo_id'), $i); ?>
				</div>
									
				<div class="report_row">
					<input name="submit" type="submit" value="<?php echo Kohana::lang('ui_main.reports_btn_submit'); ?>" class="btn_submit" /> 
				</div>
			</div>
		</div>
		<?php print form::close(); ?>
		<!-- end report form block -->
	</div>
</div>
