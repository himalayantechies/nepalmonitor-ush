jQuery(function() {
	// Category Switch Action
	$("ul#kmlfilter_switch li.layer_type > a").click(function(e) {
	
		var layerId = this.id.substring(10);
		var catSet = 'kmlfilter_' + this.id.substring(10);
	
		// Remove All active
		$("a[id^='kmlfilter_']").removeClass("active");
	
		// Hide All Children DIV
		$("[id^='kmlchild_']").hide();
	
		// Add Highlight
		$("#kmlfilter_" + layerId).addClass("active");
	
		// Show children DIV
		$("#kmlchild_" + layerId).show();
		$(this).parents("div").show();
		
		map.addLayer(Ushahidi.KML, {
			name: $(".layer-name", this).html(),
			url: "json/layer/" + layerId
		}, false, false);
		
		// Update report filters
		map.updateReportFilters({
			lkey: 0});
				
		e.stopPropagation();
		return false;
	});
	
	$("ul#kmlfilter_switch li.layer_child > a").click(function(e) {
	
		var layerId = this.id.substring(10);
		var catSet = 'kmlfilter_' + this.id.substring(10);
	
		// Remove All active
		$("a[id^='kmlfilter_']").removeClass("active");
	
		// Hide All Children DIV
		$("[id^='kmlchild_']").hide();
	
		// Add Highlight
		$("#kmlfilter_" + layerId).addClass("active");
		
		// Show children DIV
		$("#kmlchild_" + layerId).show();
		$(this).parents("div").show();
	
		// Update report filters
		map.updateReportFilters({
			lkey: layerId});
	
		e.stopPropagation();
		return false;
	});

});

function triggerkmlfilter(layerId) {
	if(layerId != 'undefined' && layerId != 0)
		$("#kmlfilter_"+layerId).trigger('click');
}