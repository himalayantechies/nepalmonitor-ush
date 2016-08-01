<?php defined('SYSPATH') or die('No direct script access.');
class location_filter_Core {

	// Table Prefix
	protected static $table_prefix;
	protected static $pcode = '';
	protected static $adm_level = '';
	public static $admLevels = array(0 => array('label' => 'Country', 'types' => 'country', 'pcode' => 'admin0Pcode', 'name' => 'admin0Name_en'), 1 => array('label' => 'Development Region', 'types' => 'administrative_area_level_1', 'pcode' => 'admin1Pcode', 'name' => 'admin1Name_en'), 2 => array('label' => 'Zone', 'types' => 'administrative_area_level_2', 'pcode' => 'admin2Pcode', 'name' => 'admin2Name_en'), 3 => array('label' => 'District', 'types' => 'administrative_area_level_3', 'pcode' => 'admin3Pcode', 'name' => 'admin3Name_en'), 4 => array('label' => 'Municipality/VDC', 'types' => 'locality', 'pcode' => 'admin4Pcode', 'name' => 'admin4Name_en'), 5 => array('label' => 'Ward', 'types' => '', 'pcode' => 'admin5Pcode', 'name' => 'admin5Name_en'));

	static function init() {
		// Set Table Prefix
		self::$table_prefix = Kohana::config('database.default.table_prefix');
	}

	function uploadkml($kmlfile = null) {
		$allList = new Location_Filter_Model();
		foreach (self::$admLevels as $key => $levels) {
			$adminPcode[$key] = array();
			$adminPcode[$key] = $allList->where('adm_level', $key)->select_list('pcode', 'pcode'); 
		}
		// Pull from an uploaded file
		$layer_link = Kohana::config('upload.directory').'/'.$kmlfile;
		if(!empty($layer_link)) {
			$content = file_get_contents($layer_link);
			if ($content !== false) {
				
				$xml = simplexml_load_string($content);
				
				foreach($xml->Document->Folder->Placemark as $placemark) {
					$parent_pcode = '';
					$record = array();
					foreach($placemark->ExtendedData->SchemaData->SimpleData as $data) {
						$var = (string) $data->attributes()->name;
						$record[$var] = (string) $data;
					}
					
					if($placemark->MultiGeometry) {
						foreach($placemark->MultiGeometry->Polygon as $geometry) {
							$record['coord'][] = (string) $geometry->outerBoundaryIs->LinearRing->coordinates;
						}
					} else {
						$record['coord'][] = (string) $placemark->Polygon->outerBoundaryIs->LinearRing->coordinates;
					}
					
					foreach (self::$admLevels as $key => $levels) {
						$location_filter = new Location_Filter_Model();
						end(self::$admLevels);
						if (!empty($record[$levels['pcode']])) {
							if (empty($adminPcode[$key][$record[$levels['pcode']]])) {
								if($key == key(self::$admLevels)) {
									foreach($record['coord'] as $coord) {
										$cord = str_replace(" ", "\n", $coord);
										$cords = explode("\n", $cord);
										$poly_cor = false;
										foreach($cords as $cordinate) {
											$cor = explode(',', $cordinate);
											if(is_array($cor) && intval($cor[0]) != 0) {
												if($poly_cor !== false) $poly_cor .= ', ';
												$poly_cor .= $cor[1].' '.$cor[0];
											}
										}
										$location_filter->clear();
										$location_filter->parent_pcode = $parent_pcode;
										$location_filter->pcode = $record[$levels['pcode']];
										$location_filter->name = $record[$levels['name']];
										$location_filter->adm_level = $key;
										$location_filter->coord = $poly_cor;
										$location_filter->save();
									}
								} else {
									$location_filter->clear();
									$location_filter->parent_pcode = $parent_pcode;
									$location_filter->pcode = $record[$levels['pcode']];
									$location_filter->name = $record[$levels['name']];
									$location_filter->adm_level = $key;
									$location_filter->save();
								}
								$adminPcode[$key][$record[$levels['pcode']]] = $record[$levels['pcode']];
							}
							//$parent_pcode = $adminPcode[$key][$record[$levels['pcode']]];
							$parent_pcode = $record[$levels['pcode']];
						} else {
							continue;
						}
					}
				}
			}
		}
	}
	
	function upload() {
		$parent_pcode = '';
		foreach (self::$admLevels as $key => $levels) {
			$adminPcode[$key] = array();
		}
		foreach ($records as $record) {
			foreach (self::$admLevels as $key => $levels) {
				if ($record[$levels['pcode']]) {
					if (!$adminPcode[$key][$record[$levels['pcode']]]) {
						if($record['multipoly']) {
							foreach($record['coord'] as $coord) {
								//$id = INSERT INTO location_filter FIELDS() VALUES($parent_pcode, $record[$levels['pcode']], $record[$levels['name']], $key, $coord);
							}
						} else if ($record['coord']) {
							//$id = INSERT INTO location_filter FIELDS() VALUES($parent_pcode, $record[$levels['pcode']], $record[$levels['name']], $key, $record['coord']);
						} else {
							//$id = INSERT INTO location_filter FIELDS() VALUES($parent_pcode, $record[$levels['pcode']], $record[$levels['name']], $key);
						}
						$adminPcode[$key][$record[$levels['pcode']]] = $record[$levels['pcode']];
					}
					//$parent_pcode = $adminPcode[$key][$record[$levels['pcode']]];
					$parent_pcode = $record[$levels['pcode']];
				} else {
					continue;
				}
			}
		}
	}

	function save($post, $incident) {
		$s = curl_init();
		$loc_mapping_url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=" . $post['latitude'] . "," . $post['longitude'];
		curl_setopt($s, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($s, CURLOPT_URL, $loc_mapping_url);
		curl_setopt($s, CURLOPT_RETURNTRANSFER, true);

		$_useragent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1';
		$_referer = url::site();
		curl_setopt($s, CURLOPT_USERAGENT, $_useragent);
		curl_setopt($s, CURLOPT_REFERER, $_referer);

		$_webpage = curl_exec($s);
		$_status = curl_getinfo($s, CURLINFO_HTTP_CODE);
		curl_close($s);
		if ($_status == 200) {
			$response = json_decode($_webpage, true);
			if ($response['status'] == 'OK') {
				foreach ($response['results'] as $result) {
					foreach (self::$admLevels as $key => $admLvl) {
						if (!empty($admLvl['types'])) {
							if (in_array($admLvl['types'], $result['types'])) {
								foreach ($result['address_components'] as $location) {
									if (in_array($admLvl['types'], $location['types'])) {
										$admLevel[$key] = $location['long_name'];
									}
									break;
								}
								break;
							}
						}
					}
				}
				krsort($admLevel);

				$locfilter_model = new Database();
				foreach ($admLevel as $lvl => $name) {
					$filters = $locfilter_model -> query("SELECT DISTINCT pcode, id, parent_pcode, adm_level, coord FROM ".self::$table_prefix.".location_filter WHERE adm_level = '".$lvl."' AND name = '". $name ."' GROUP BY pcode");
					if (count($filters) == 1) {
						self::$pcode = $filters[0] -> pcode;
						self::$adm_level = $filters[0] -> adm_level;
						if (self::check_child($post, $filters[0]))
							break;
					} elseif (count($filters) > 1) {
						/* child coord */
					} elseif (count($filters) < 1) {
						/*	parent
						 child coord*/
					}
				}
				$incident -> pcode = self::$pcode;
				$incident -> adm_level = self::$adm_level;
			}
		}

	}

	function check_child($post, $parent = null) {
		$filter_match = false;
		$db = new Database();
		$locfilter_model = new Location_Filter_Model();
		$childs = $locfilter_model -> where('parent_pcode', $parent -> pcode) -> find_all();
		if (count($childs) > 0) {
			foreach ($childs as $child) {
				if ($locfilter_model -> where('parent_pcode', $child -> pcode) -> count_all() > 0) {
					$filter_match = self::check_child($post, $child);
				}
				if (!$filter_match && !empty($child -> coord)) {
					$sql = 'SELECT myWithin(PointFromText(CONCAT( "POINT(", ' . $post['latitude'] . ', " ", ' . $post['longitude'] . ', ")" )), PolyFromText("POLYGON((' . $child -> coord . '))")) AS inPolygon';
					foreach ($db->query($sql) as $item) {
						if ($item -> inPolygon) {
							$filter_match = true;
							self::$pcode = $child -> pcode;
							self::$adm_level = $child -> adm_level;
							break;
						}

					}
				}
				if ($filter_match) {
					break;
				}
			}
		}
		if (!$filter_match) {
			$loc_model = new Location_Filter_Model();
			$siblings = $loc_model -> where('pcode', $parent->pcode) -> find_all();
			foreach($siblings as $pnt) {
				if(!empty($pnt -> coord)) {
					$sql = 'SELECT myWithin(PointFromText(CONCAT( "POINT(", ' . $post['latitude'] . ', " ", ' . $post['longitude'] . ', ")" )), PolyFromText("POLYGON((' . $pnt -> coord . '))")) AS inPolygon';
					foreach ($db->query($sql) as $item) {
						if ($item -> inPolygon) {
							$filter_match = true;
							self::$pcode = $pnt -> pcode;
							self::$adm_level = $pnt -> adm_level;
							break;
						}
		
					}
				}
			}
		}
		return $filter_match;
	}

	/**
	 * Generates a location filter view - recursively iterates
	 *
	 * @return string
	 */
	public static function get_location_filter_view($adm_level = null) {
		$location_data = self::get_location_filter_data(TRUE, $adm_level);
		// Generate and return the HTML
		return self::_generate_filterview_html($location_data);
	}

	/**
	 * Traverses an array containing location data and returns a tree view
	 *
	 * @param array $location_data
	 * @return string
	 */
	private static function _generate_filterview_html($location_data) {
		// To hold the filterview HTMl
		$tree_html = array();

		foreach ($location_data as $lvl => $location) {
			$tree_html[$lvl] = "";
			foreach ($location as $lid => $loc) {
				if(!isset($loc->report_count)) {
					$tree_html[$lvl] .= "<li>" . "<a href=\"#\" class=\"loc_selected\" id=\"filter_link_adm_" . $loc->id . "\" title=\"{$loc->name}\">" . "<span class=\"item-title\">" . html::strip_tags($loc->name) . "</span>" . "</a></li>";
				} else if(isset($loc->report_count) && $loc->report_count > 0) {
					$tree_html[$lvl] .= "<li>" . "<a href=\"#\" class=\"loc_selected\" id=\"filter_link_adm_" . $loc->id . "\" title=\"{$loc->name}\">" . "<span class=\"item-title\">" . html::strip_tags($loc->name) . "</span>" . "<span class=\"item-count\">" . $loc->report_count . "</span>" . "</a></li>";
				}
			}
		}

		// Return
		return $tree_html;
	}

	/**
	 * Get locations as an tree array
	 * @param bool Get location count?
	 * @param bool Include hidden locations
	 * @return array
	 **/
	public static function get_location_filter_data($count = FALSE, $adm_level = FALSE) {

		// To hold the location data
		$location_data = array();

		// Database table prefix
		$table_prefix = Kohana::config('database.default.table_prefix');

		// Database instance
		$db = new Database();

		// Fetch the other locations
		if (is_numeric($adm_level)) {
			if ($count) {
				$sql = "SELECT DISTINCT lf.pcode, lf.id, lf.parent_pcode, lf.name, lf.adm_level, COUNT(i.id) report_count " 
				. "FROM " . $table_prefix . "location_filter lf " 
				. "LEFT JOIN " . $table_prefix . "incident i ON (i.pcode LIKE CONCAT(lf.pcode ,'%') AND i.incident_active = 1 ) " 
				. "WHERE lf.adm_level = '" . $adm_level . "'" 
				. "GROUP BY lf.pcode " 
				. "ORDER BY lf.name ASC";
			} else {
				$sql = "SELECT DISTINCT lf.pcode, lf.id, lf.parent_pcode, lf.name, lf.adm_level " 
				. "FROM " . $table_prefix . "location_filter lf " 
				. "WHERE lf.adm_level = '" . $adm_level . "'" 
				. "ORDER BY lf.name ASC";
			}
			$location_data[$adm_level] = $db -> query($sql);
		} else {
			foreach (location_filter::$admLevels as $adm_level => $admlvl) {
				if ($count) {
					$sql = "SELECT DISTINCT lf.pcode, lf.id, lf.parent_pcode, lf.name, lf.adm_level, COUNT(i.id) report_count " 
					. "FROM " . $table_prefix . "location_filter lf " 
					. "LEFT JOIN " . $table_prefix . "incident i ON (i.pcode LIKE CONCAT(lf.pcode ,'%') AND i.incident_active = 1 ) " 
					. "WHERE lf.adm_level = '" . $adm_level . "'" 
					. "GROUP BY lf.pcode " 
					. "ORDER BY lf.name ASC";
				} else {
					$sql = "SELECT DISTINCT lf.pcode, lf.id, lf.parent_pcode, lf.name, lf.adm_level " 
					. "FROM " . $table_prefix . "location_filter lf " 
					. "WHERE lf.adm_level = '" . $adm_level . "'" 
					. "ORDER BY lf.name ASC";
				}
				$location_data[$adm_level] = $db -> query($sql);
			}
		}
		return $location_data;
	}
	
	public static function get_adm_levels($adm_level, $pcode) {

		// To hold the location data
		$adm_Lvls = array();
		if(isset($adm_level) && !empty($pcode)) {
			$loc_model = new Location_Filter_Model();
			$adm_Lvls[$adm_level] = $loc_model -> where('pcode', $pcode) -> where('adm_level', $adm_level) -> find();
			for($i = $adm_level-1; $i >= 0; $i--) {
				$lvl_model = new Location_Filter_Model();
				 
				$adm_Lvls[$i] = $lvl_model -> where('pcode', $adm_Lvls[$i + 1]->parent_pcode) -> where('adm_level', $i) -> find();
			}
		}
		return $adm_Lvls;
	}

}

location_filter_Core::init();
?>