/*
* Add KML Layers
*/
function locationHighlightLite_init()
{
	//var layerURL = "/media/uploads/LocationHighlightLite.kml" + "?" + Math.random();
	// HT: http://himalayantechies.com Added jsBaseUrl to work with any baseurl structure
	var layerURL = jsBaseUrl + "media/uploads/LocationHighlightLite.kml" + "?" + Math.random();

	//create new layer
	var layer = new OpenLayers.Layer.Vector("Location", {
		projection: map.displayProjection,
		strategies: [new OpenLayers.Strategy.Fixed()],
		protocol: new OpenLayers.Protocol.HTTP({
			url: layerURL,
			format: new OpenLayers.Format.KML({
				extractStyles: true,
				extractAttributes: true
			})
		})
	});


	// Add New Layer
	map.addLayer(layer);

	return false;
}

/**
 * HT: Created by http://himalayantechies.com
 * to wait recursively until map is defined then only add events
 */
function map_register() {
	if(typeof(map) != 'undefined') {
		map.events.register("loadend", locationHighlightLite_init());
	} else {
		setTimeout(function(){
			map_register();
		}, 100);
	}
}
// can't find better way for binding to 'map' var, which isn't initialized at this time
jQuery(document).ready(function($) {
	/**
	 * HT: Added map_register() and commented code below
	 * as once call by 2000 time was not loading it on all cases
	 */
	map_register();
		/*window.setTimeout(function() {
	    map.events.register("loadend", locationHighlightLite_init());
		}, 2000);*/
});
