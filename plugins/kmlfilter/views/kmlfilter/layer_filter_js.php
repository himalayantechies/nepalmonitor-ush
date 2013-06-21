// Layer Filter Selection Action
$('#layer-filter-list span.ui-icon-triangle-1-s, #layer-filter-list span.ui-icon-triangle-1-e').click(function() {
	var lyrID = $(this).parent('li').find('a[id^="filter_link_lyr_"]').attr("id").substring(16);
	if( $("#filter_child_link_lyr_"+lyrID).find('a').length > 0) {
		if(!$("#filter_child_link_lyr_"+lyrID).is(":visible")) {
			$("#filter_child_link_lyr_"+lyrID).show();
			$(this).removeClass('ui-icon-triangle-1-e');
			$(this).addClass('ui-icon-triangle-1-s');
		} else { //kids are shown, deactivate them.
			$("#filter_child_link_lyr_"+lyrID).hide();
			$(this).removeClass('ui-icon-triangle-1-s');
			$(this).addClass('ui-icon-triangle-1-e');
		}
	}
});
$("a[id^='filter_link_lyr_']").click(function() {
	//the id of the layer that just changed
	var lyrID = this.id.substring(16);
	
	//first check and see if we're dealing with a parent layer
	if( $("#filter_child_link_lyr_"+lyrID).find('a').length > 0) {
		//are we turning the parent on or off?
		var parentActive = $("#filter_link_lyr_"+lyrID).hasClass("selected");
		
		//we want to turn on/off kid layers
		var kids = $("#filter_child_link_lyr_"+lyrID).find('a');
		kids.each(function(){
			var kidNum = $(this).attr("id").substring(16);
			if(!parentActive) {
				$(this).addClass("selected");
			} else {
				$(this).removeClass("selected");
			}
		});
	} else {
		//check if we're dealing with a child
		parentDiv = $(this).parents("div[id^='filter_child_link_lyr_']");
		if(parentDiv.length > 0) {
			parentID = $(parentDiv).attr('id').substring('22');
			if($("#filter_link_lyr_"+lyrID).hasClass("selected")) {
				//if it's active deactivate it
				if($("#filter_link_lyr_"+parentID).hasClass("selected")) {
					var retain = false;
					//if all siblings disabled then disable parent
					var kids = $("#filter_child_link_lyr_"+parentID).find('a');
					kids.each(function() {
						if($(this).hasClass("selected")) {
							if($(this).attr("id").substring(16) != lyrID) {
								retain = true;
								return false;
							}
						}
					});
					if(!retain) $("#filter_link_lyr_"+parentID).removeClass("selected");
				}
			} else {
				if(!$("#filter_link_lyr_"+parentID).hasClass("selected")) {
					$("#filter_link_lyr_"+parentID).addClass("selected");
				}
			}
		}//end of dealing with kids
	}
	//first check and see if we're adding or removing this layer
	if($("#filter_link_lyr_"+lyrID).hasClass("selected")) {
		$("#filter_link_lyr_"+lyrID).removeClass("selected");
	} else {
		$("#filter_link_lyr_"+lyrID).addClass("selected");
	}
});