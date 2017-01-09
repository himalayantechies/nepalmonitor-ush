<?php defined('SYSPATH') or die('No direct script access.'); ?>

<script type="text/javascript">
//<![CDATA[
/**
 * Set the selected layers as selected
 */
$(document).ready(function() {
	var lyrs = [<?php echo $selected_layers; ?>];
	for( i in lyrs) {
		if(!$("#filter_link_lyr_" + lyrs[i]).hasClass("selected")) {
			$("#filter_link_lyr_" + lyrs[i]).trigger("click");
		}
	}
	/*
	HT: removed as its giving parent category as well while selecting child so it filters for all child categories
	$('#category-filter-list li a').click(function() {
		curElem = $(this);
		parentLI = curElem.parent('li');
		catAll = !curElem.hasClass('cat_selected');
		if(catAll) {
			nextLIs = parentLI.nextAll('li');
			nextLIs.each(function(){
				$(this).find('a.cat_selected').removeClass('selected');
			});
		} else {
			parentLI.parent('ul').find('li:first').find('a').removeClass('selected');
			if(parentLI.hasClass('report-listing-category-child')) {
				if(curElem.hasClass('selected')) {
					var close = false;
					prevLIs = parentLI.prevAll('li');
					prevLIs.each(function(){
						if(!close && !$(this).hasClass('report-listing-category-child')) {
							$(this).find('a.cat_selected').addClass('selected');
							close = true;
						}
					});
				} else {
					var close = false;
					var addaction = false;
					prevLIs = parentLI.prevAll('li');
					prevLIs.each(function(){
						if(!close && !$(this).hasClass('report-listing-category-child')) {
							nextLIs = $(this).nextAll('li');
							var reclose = false;
							nextLIs.each(function(){
								if(!reclose && $(this).hasClass('report-listing-category-child')) {
									if($(this).find('a.cat_selected').hasClass('selected')) {
										addaction = true;
									}
								} else {
									reclose = true;
								}
							});
							if(!addaction) $(this).find('a.cat_selected').removeClass('selected');
							close = true;
						}
					});
				}
			} else {
				nextLIs = parentLI.nextAll('li');
				var close = false;
				nextLIs.each(function(){
					if(!close && $(this).hasClass('report-listing-category-child')) {
						if(curElem.hasClass('selected')) {
							$(this).find('a.cat_selected').addClass('selected');
						} else {
							$(this).find('a.cat_selected').removeClass('selected');
						}
					} else {
						close = true;
					}
				});
			}
		}
	});
	*/
	// HT: updated not to select parent id until all childs are clicked
	$('#category-filter-list li a').click(function() {
		curElem = $(this);
		parentLI = curElem.parent('li');
		catAll = !curElem.hasClass('cat_selected');
		if(catAll) {
			nextLIs = parentLI.nextAll('li');
			nextLIs.each(function(){
				$(this).find('a.cat_selected').removeClass('selected');
			});
		} else {
			parentLI.parent('ul').find('li:first').find('a').removeClass('selected');
			if(parentLI.hasClass('report-listing-category-child')) {
				if(curElem.hasClass('selected')) {
					var close = false;
					prevLIs = parentLI.prevAll('li');
					prevLIs.each(function(){
						if(!close && !$(this).hasClass('report-listing-category-child')) {
							$(this).find('a.cat_selected').removeClass('selected');
							close = true;
						}
					});
				}
			} else {
				nextLIs = parentLI.nextAll('li');
				var close = false;
				nextLIs.each(function(){
					if(!close && $(this).hasClass('report-listing-category-child')) {
						if(curElem.hasClass('selected')) {
							$(this).find('a.cat_selected').addClass('selected');
						} else {
							$(this).find('a.cat_selected').removeClass('selected');
						}
					} else {
						close = true;
					}
				});
			}
		}
	});
});
//]]>
</script>