=== About ===
name: KML Filter
website: https://github.com/HTSolution/Ushahidi-plugin-kmlfilter
description: Adds filter for the report by KML layers
author: HTSolution Pvt. Ltd.
author website: http://himalayantechies.com

== Description ==
*Adds layer filter to reports index filter page
*KML file will require some changes
*If want don't want to work on KML file then prefer another branch (gadm) with KML files from 
	http://www.gadm.org


== Installation ==
1. Copy the entire /kmlfilter/ directory into your /plugins/ directory.
2. Convert KML file in format mentioned on NOTE
3. Activate the plugin

__NOTE:__
1. KML file requires 

	<Placemark>
		<ID></ID>
	</Placemark>
	
	tag with unique id inside <ID></ID> for each <Placemark>
	and coordinates should be in format

	<Placemark>
		<MultiGeometry>
			<Polygon>
				<outerBoundaryIs>
					<LinearRing>
						<coordinates>
						</coordinates>
					</LinearRing>
				</outerBoundaryIs>
			</Polygon>
		</MultiGeometry>
	</Placemark>


2. If activating plugin does not show location filter on main page then search for

	if (layerType !== Ushahidi.KML) {
	
and its related
	
	}
	
code in media/js/ushahidi.js and comment out these two lines

3. If plugin does not filter timeline by location then search for 
	
	// Fetch the timeline data
	$query = 'SELECT UNIX_TIMESTAMP('.$select_date_text.') AS time, COUNT(id) AS number '
	. 'FROM '.$this->table_prefix.'incident '
		. 'WHERE incident_active = 1 '.$incident_id_in.' '
	. 'GROUP BY '.$groupby_date_text;

in controllers/json.php under function timeline() and add
