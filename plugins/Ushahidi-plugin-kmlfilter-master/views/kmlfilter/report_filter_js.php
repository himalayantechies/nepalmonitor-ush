var layer_ids = [];
$.each($(".fl-layers li a.selected"), function(i, item){
	itemId = item.id.substring("filter_link_lyr_".length);
	layer_ids.push(itemId);
});
	
if (layer_ids.length > 0)
{
	urlParameters["lkey"] = layer_ids;
}