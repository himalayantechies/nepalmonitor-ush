<?php
set_time_limit(0);
$error = '';
$fext = '.xml';
$zext = '.zip';
if ($pagin->current_page <= 1){
	$fname = time().'_'.rand();
} else {
	$fname = $_GET['file'];
} 
$filePath = '/plugins/exportreports/tmpexport/'.$fname;
$fileName = SYSPATH.'..'.$filePath.$fext;
$zipName = SYSPATH.'..'.$filePath.$zext;
if ($pagin->current_page <= 1){
	$fp = @fopen($fileName,"w");
	$head = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
	$head .= "<?xml-stylesheet type=\"text/xsl\" href=\"export.xsl\" ?>";
	$head .= '<export xmlns:atom="http://www.w3.org/2005/Atom" xmlns:georss="http://www.georss.org/georss">';
	$head .= '<channel>';
	$head .= '<copyright uri="http://himalayantechies.com">HimalayanTechies Pvt. Ltd. </copyright>';
	$head .= '<updated>' .gmdate("c", time()) .'</updated>';
	$head .= '<title>' .Kohana::config('settings.site_name') .'</title>';
	$head .= '<description>' .Kohana::config('settings.site_tagline').'</description>';
	$head .= '<link rel="alternate" type="text/html" .href="url::base(); "/>';
	$head .= '<generator uri="https://github.com/HTSolution/Ushahidi-plugin-exportreports" version="1.0">Export Report Plugin - HTSolution </generator>';
			// Event::report_download_xml_head - Add to the xml head
		Event::run('ushahidi_action.report_download_xml_head');
		fwrite ($fp, $head);
		@fclose($fp);	
}

$fp = @fopen($fileName, "a");
if ($fp) {
	$content = '';
	foreach ($incidents as $incident) {
			$content .= '<item>';
			$content .= '<id>'.$incident->incident_id.'</id>';
			$content .= '<title>'.exportreports_helper::_csv_text($incident->incident_title).'</title>';
			$link = url::base().'reports/view/'.$incident->incident_id;
			$content .= '<link  rel="alternate" type="text/html" href="'.$link.'" />';
			$content .= '<published>'.$incident->incident_date.'</published>';
			$content .= '<location>'.exportreports_helper::_csv_text($incident->location_name).'</location>';
			$incident->incident_category = ORM::Factory('category')->join('incident_category', 'category_id', 'category.id')->where('incident_id', $incident->incident_id)->find_all();
			foreach($incident->incident_category as $category) {
				if ($category->category_title) {
					$content .= '<category>'.exportreports_helper::_csv_text($category->category_title).'</category>';
				}
			}
			$content .= '<longitude>'.exportreports_helper::_csv_text($incident->longitude).'</longitude>';
			$content .= '<latitude>'.exportreports_helper::_csv_text($incident->longitude).'</latitude>';
			$content .= '<pcode>'.exportreports_helper::_csv_text($incident->pcode).'</pcode>';
			if(isset(location_filter::$admLevels[$incident->adm_level]['label'])) {
			$content .= '<adm_level>'.exportreports_helper::_csv_text(location_filter::$admLevels[$incident->adm_level]['label']).'</adm_level>';
			}
			$media = reports::get_media($incident->incident_id, 4);
			if(!empty($media)) {
				foreach($media as $m) {
					$content .= '<source>'.exportreports_helper::_csv_text($m->media_link).'</source>';
				}
			}
			$content .= '<content type="xhtml" xml:lang="en">'
			.exportreports_helper::_csv_text($incident->incident_description)
			.'</content>';
			$content .= '<content_parsed>'
			.exportreports_helper::_csv_text(strip_tags($incident->incident_description))
			.'</content_parsed>';
			
			$custom_fields = customforms::get_custom_form_fields($incident->incident_id,'',false);
			if (!empty($custom_fields)) {
				$content .= '<customfields>';
				foreach($custom_fields as $custom_field) {
					$tag = exportreports_helper::_xml_tag($custom_field['field_name']);
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
						$content .= '<'.$tag.'>'.exportreports_helper::_csv_text($value).'</'.$tag.'>';
					} else {
						$content .= '<'.$tag.'>'.exportreports_helper::_csv_text($custom_field['field_response']).'</'.$tag.'>';	
					}
					
				}
				$content .= '</customfields>';
			}
			$incident_orm = ORM::factory('incident', $incident->incident_id);
			$incident_person = $incident_orm->incident_person;
			if($incident_person->loaded) {
				$content .= '<person>';
					$content .= '<firstname>'.exportreports_helper::_csv_text($incident_person->person_first).'</firstname>';
					$content .= '<lastname>'.exportreports_helper::_csv_text($incident_person->person_last).'</lastname>';
					$content .= '<email>'.exportreports_helper::_csv_text($incident_person->person_email).'</email>';
				$content .= '</person>';
			}
			if ($incident->incident_active) {
				$content .= '<approved>YES</approved>';
			} else {
				$content .= '<approved>NO</approved>';
			}
			if ($incident->incident_verified) {
				$content .= '<verified>YES</verified>';
			} else {
				$content .= '<verified>NO</verified>';
			}
			
			Event::run('ushahidi_filter.report_download_xml_incident', $incident->incident_id);
		$content .= '</item>';
	}
	if($pagin->total_pages != $pagin->current_page) {
	$content .= "</channel>";
	$content .= "</export>";
	}
	@fwrite($fp, $content);
	@fclose($fp);
}

if($pagin->total_pages == $pagin->current_page) {
	$zip = new ZipArchive();
	if($zip->open($zipName, ZIPARCHIVE::CREATE)!==TRUE){
		$error .= "* Sorry ZIP creation failed at this time<br/>";
	} else {
		if(!$zip->addFile(SYSPATH.'../media/export_reports_readme.txt', 'readme.txt')) {
			$error .= "* Sorry readme file failed to attach<br/>";
		}
		if(!$zip->addFile(SYSPATH.'../plugins/exportreports/css/export.xsl', 'export.xsl')) {
			$error .= "* Sorry Stylesheet file failed to attach<br/>";
		}
		if(!$zip->addFile($fileName, $fname.$fext)) {
			$error .= "* Sorry XML file not created<br/>";
		}
		$zip->close();
	}
	$nxtUrl = url::site().$filePath.$zext;
}
else {
	$nxtUrl = url::site().url::merge(array('page' => $pagin->current_page+1, 'file' => $fname));
}
if(!empty($error)) {
	echo $error;
	exit;
}
$Download_percent = round(($pagin->current_page / $pagin->total_pages) * 100 , 0 , PHP_ROUND_HALF_EVEN);
echo nl2br("It may take a while. Please wait...\nExporting:$Download_percent%");
echo '<input id="reloadUrl" type="hidden" value="'.$nxtUrl.'">';
?>
<script>
window.location = document.getElementById('reloadUrl').value;
</script>