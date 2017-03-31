<?php defined('SYSPATH') or die('No direct script access.'); ?>
<script type="text/javascript">
	$(document).ready(function() {
		$("a.export-button").click(function(ev) {
			ev.preventDefault();
			//
			// Get all the selected categories
			//
			var category_ids = [];
			$.each($(".fl-categories li a.selected"), function(i, item){
				itemId = item.id.substring("filter_link_cat_".length);
				// Check if category 0, "All categories" has been selected
				category_ids.push(itemId);
			});
			
			if (category_ids.length > 0) {
				urlParameters["c"] = category_ids;
			}
			
			//
			// Get the incident modes
			//
			var incidentModes = [];
			$.each($(".fl-incident-mode li a.selected"), function(i, item) {
				modeId = item.id.substring("filter_link_mode_".length);
				incidentModes.push(modeId);
			});
			
			if (incidentModes.length > 0) {
				urlParameters["mode"] = incidentModes;
			}

			var incidentForms = [];
			$.each($(".fl-form li a.selected"), function(i, item){
				formId = item.id.substring("filter_link_form_".length);
				incidentForms.push(formId);
			});
			
			if (incidentForms.length > 0)
			{
				urlParameters["fm"] = incidentForms;
			}
			
			//
			// Get the media type
			//
			var mediaTypes = [];
			$.each($(".fl-media li a.selected"), function(i, item) {
				mediaId = item.id.substring("filter_link_media_".length);
				mediaTypes.push(mediaId);
			});
			
			if (mediaTypes.length > 0) {
				urlParameters["m"] = mediaTypes;
			}
			
			// Get the verification status
			var verificationStatus = [];
			$.each($(".fl-verification li a.selected"), function(i, item) {
				statusVal = item.id.substring("filter_link_verification_".length);
				verificationStatus.push(statusVal);
			});
			if (verificationStatus.length > 0) {
				urlParameters["v"] = verificationStatus;
			}
			
			var admIds = [];
			$.each($("[class^='filter-list fl-adm'] li a.selected"), function(i, item){
				admId = item.id.substring("filter_link_adm_".length);
				admIds.push(admId);
			});
			if (admIds.length > 0)
			{
				urlParameters["adm"] = admIds;
			}
						//
			// Get the Custom Form Fields
			//
			var customFields = new Array();
			var checkBoxId = null;
			var checkBoxArray = new Array();
			$.each($("input[id^='custom_field_']"), function(i, item) {
				var cffId = item.id.substring("custom_field_".length);
				var value = $(item).val();
				var type = $(item).attr("type");
				if(type == "text") {
					if(value != "" && value != undefined && value != null) {
						customFields.push([cffId, value]);
					}
				} else if(type == "radio") {
					if($(item).attr("checked")) {
						customFields.push([cffId, value]);
					}
				} else if(type == "checkbox") {
					if($(item).attr("checked")) {
						checkBoxId = cffId;
						checkBoxArray.push(value);
					}
				}
				
				if(type != "checkbox" && checkBoxId != null) {
					customFields.push([checkBoxId, checkBoxArray]);
					checkBoxId = null;
					checkBoxArray = new Array();
				}
				
			});
			//incase the last field was a checkbox
			if(checkBoxId != null) {
				customFields.push([checkBoxId, checkBoxArray]);
			}
			
			//now selects
			$.each($("select[id^='custom_field_']"), function(i, item) {
				var cffId = item.id.substring("custom_field_".length);
				var value = $(item).val();
				if(value != "---NOT_SELECTED---") {
					customFields.push([cffId, value]);
				}
			});
			if(customFields.length > 0) {
				urlParameters["cff"] = customFields;
			} else {
				delete urlParameters["cff"];
			}
			//for unapproved reports
			if (<?php echo (intval(Auth::instance()->has_permission('reports_approve'))) ?>){
				var approvalStatus = [0,1]
				if (approvalStatus.length > 0) {
					urlParameters["a"] = approvalStatus;
				}
			}	
			else{
				delete urlParameters["a"];
			}
			<?php
				// Action, allows plugins to add custom filters
				Event::run('ushahidi_action.report_js_filterReportsAction');
			?>
			// Export the reports
			exportReports($(this).attr('exptype'));
		});
	});

	function exportReports(exp) {
		// Check if there are any parameters
		if ($.isEmptyObject(urlParameters)) {
			urlParameters = {show: "all"}
		}
		console.log(urlParameters);
		outparam = serializeExport(urlParameters);
		console.log(outparam);
		window.location.href = '<?php echo url::site().'export_reports/index/'?>'+exp+'?'+outparam;
	}

	serializeExport = function(obj, prefix) {
	  var str = [];
	  for(var p in obj) {
	    if (obj.hasOwnProperty(p)) {
	      var k = prefix ? prefix + "[" + p + "]" : p, v = obj[p];
	      str.push(typeof v == "object" ?
	    		  serializeExport(v, k) :
	        encodeURIComponent(k) + "=" + encodeURIComponent(v));
	    }
	  }
	  return str.join("&");
	}
</script>