<?php
set_time_limit(0);
$fext = '.csv';
if ($pagin->current_page <= 1){
	$fname = time().'_'.rand();
} else {
	$fname = $_GET['file'];
}
$filePath = '/plugins/exportreports/tmpexport/'.$fname.$fext;
$fileName = SYSPATH.'..'.$filePath;
if ($pagin->current_page <= 1){
 
 $fp = @fopen($fileName,"w");
	$head = "#,INCIDENT TITLE,INCIDENT DATE,LOCATION,DESCRIPTION,CATEGORY,LATITUDE,LONGITUDE,".strtoupper(Kohana::lang('ui_main.pcode'));
	$head .= ",".strtoupper(Kohana::lang('ui_main.adm_level'));
	foreach(location_filter::$admLevels as $key => $admLvl) {
		if(!$admLvl['dummy']){ $head .= ",".$admLvl['label'];} 
	}
	$head .= ",SOURCE,SOURCE TYPE";
	$custom_titles = customforms::get_custom_form_fields('','',false);
	foreach($custom_titles as $field_name) {
		$head .= ",".$field_name['field_name'];
	}
	$head .= ",FIRST NAME,LAST NAME,EMAIL,APPROVED,VERIFIED";

	// Incase a plugin would like to add some custom fields
	Event::run('ushahidi_filter.report_download_csv_header', $custom_headers);
	$head .= "\n";
	fwrite ($fp, $head);
		@fclose($fp);	
}

$fp = @fopen($fileName, "a");
if ($fp) {
	$content = '';
	foreach ($incidents as $incident){
		$incident_id = $incident->incident_id;
		$content .= '"'.$incident->incident_id.'",';
		$content .= '"'.exportreports_helper::_csv_text($incident->incident_title).'",';
		$content .= '"'.$incident->incident_date.'"';
		$content .= ',"'.exportreports_helper::_csv_text($incident->location_name).'"';
		$content .= ',"'.exportreports_helper::_csv_text($incident->incident_description).'"';
		$content .= ',"';
		$incident->incident_category = ORM::Factory('category')->join('incident_category', 'category_id', 'category.id')->where('incident_id', $incident_id)->find_all();
		foreach($incident->incident_category as $category) {
			if ($category->category_title) {
				$content .= exportreports_helper::_csv_text($category->category_title) . ", ";
			}
		}
		$content .= '"';
		$content .= ',"'.exportreports_helper::_csv_text($incident->latitude).'"';
		$content .= ',"'.exportreports_helper::_csv_text($incident->longitude).'"';
		$content .= ',"'.exportreports_helper::_csv_text($incident->pcode).'"';
		if(isset(location_filter::$admLevels[$incident->adm_level]))
		$content .= ',"'.exportreports_helper::_csv_text(location_filter::$admLevels[$incident->adm_level]['label']).'"';
		else $content .= ',';
		$admList = location_filter::get_adm_levels($incident->adm_level, $incident->pcode);
		foreach(location_filter::$admLevels as $key => $admLvl) {
			if(!$admLvl['dummy']) {
				if(isset($admList[$key])) { $content .= ',"'.$admList[$key]->name.'"'; }
				else $content .= ',""';
			}
		}
		$media_news = reports::get_media($incident->incident_id, 4);
		if(!empty($media_news)) {
			$content .= ',';
			foreach($media_news as $m) {
				$content .= '"'.exportreports_helper::_csv_text($m->media_link).'" ';
			}
		} else {
			$content .= ',';
		}
		
		$media_news_type = reports::get_media($incident->incident_id, 8);
		if(!empty($media_news_type)) {
			$content .= ',';
			foreach($media_news_type as $m) {
				$content .= '"'.exportreports_helper::_csv_text($m->media_link).'" ';
			}
		} else {
			$content .= ',';
		}
		
				
		$custom_fields = customforms::get_custom_form_fields($incident_id,'',false);
		if ( ! empty($custom_fields)) {
			foreach($custom_fields as $custom_field) {
				if($custom_field['field_type'] == 10) {
					$value = $custom_field['field_response'];
					$field_options = customforms::get_custom_field_options($custom_field['field_id']);
					if (isset($field_options['field_autocomplete_type']) && ($field_options['field_autocomplete_type'] == 'FILE')) {
						if (!empty($field_options['field_autocomplete_file'])) 
						{
							$field_file = $field_options['field_autocomplete_file'];
							$value = customforms::get_autosearch_text($value, $field_file, true);	
						} 
					} else {
						$value = customforms::get_autosearchDb_text($custom_field['field_id'], $value, true);
					}
					$content .= ',"'.exportreports_helper::_csv_text($value).'"';
				} else {
					$content .= ',"'.exportreports_helper::_csv_text($custom_field['field_response']).'"';
				}
			}
		} else {
			$custom_field = customforms::get_custom_form_fields('','',false);
			foreach ($custom_field as $custom) {
				$content .= ',"'.exportreports_helper::_csv_text("").'"';
			}
		}
		$incident_orm = ORM::factory('incident', $incident_id);
		$incident_person = $incident_orm->incident_person;
		if($incident_person->loaded) {
			$content .= ',"'.exportreports_helper::_csv_text($incident_person->person_first).'"'.',"'.exportreports_helper::_csv_text($incident_person->person_last).'"'.
					',"'.exportreports_helper::_csv_text($incident_person->person_email).'"';
		} else {
			$content .= ',"'.exportreports_helper::_csv_text("").'"'.',"'.exportreports_helper::_csv_text("").'"'.',"'.exportreports_helper::_csv_text("").'"';
		}
		$content .= ($incident->incident_active) ? ",YES" : ",NO";
		$content .= ($incident->incident_verified) ? ",YES" : ",NO";
		// Incase a plugin would like to add some custom data for an incident
		Event::run('ushahidi_filter.report_download_csv_incident', $incident->incident_id);
		$content .= "\n";
	}
 	@fwrite($fp, $content);
	@fclose($fp);
}

if($pagin->total_pages == $pagin->current_page) {
	$nxtUrl = url::site().$filePath;
} else {
	$nxtUrl = url::site().url::merge(array('page' => $pagin->current_page+1, 'file' => $fname));
}
$Download_percent = round(($pagin->current_page / $pagin->total_pages) * 100 , 0 , PHP_ROUND_HALF_EVEN);
echo nl2br("It may take a while. Please wait...\nExporting:$Download_percent%");
echo '<input id="reloadUrl" type="hidden" value="'.$nxtUrl.'">';
?>
<script>
window.location = document.getElementById('reloadUrl').value;
</script>	