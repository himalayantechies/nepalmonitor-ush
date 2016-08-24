<?php defined('SYSPATH') or die('No direct script access.');
class kmlfilter_helper_Core {

	// Table Prefix
	protected static $table_prefix;
	public static $params = array();
	public static $lyr = array();
	
	static function init()
	{
		// Set Table Prefix
		self::$table_prefix = Kohana::config('database.default.table_prefix');
	}

	public static function get_layer_tree_view() {
		$view = new View('kmlfilter/layer_filter');
		$tree_html = "";
		$parent_layers = $childrenLayer = array();
		$layers = ORM::factory('layer')->where('layer_visible', 1)->find_all();
		foreach($layers as $layer) {
			$layer_url = $layer->layer_url;
			$layer_file = $layer->layer_file;
			if ($layer_url != '') {
				// Pull from a URL
				$layer_link = $layer_url;
			} else {
				// Pull from an uploaded file
				$layer_link = Kohana::config('upload.directory').'/'.$layer_file;
			}
			if(file_exists($layer_link)) {
				$content = file_get_contents($layer_link);
				$xml = simplexml_load_string($content);
	
				$layer_class = "";
				$parent_layers[] = $layer;
				$childrenLayer[$layer->id] = $xml->Document->Placemark;

				/* $tree_html .= "<li".$layer_class.">"
				. "<a href=\"#\" class=\"lyr_selected\" id=\"filter_link_lyr_".$layer->id."\" title=\"{$layer->layer_name}\">"
				. "<span class=\"item-title\">".strip_tags($layer->layer_name)."</span>"
				. "</a></li>";
				foreach($xml->Document->Placemark as $placemark) {
					$layer_class = " class=\"report-listing-category-child\"";
					$tree_html .= "<li".$layer_class.">"
					. "<a href=\"#\" class=\"lyr_selected\" id=\"filter_link_lyr_".$layer->id."_".$placemark->ID."\" title=\"{$placemark->description}\">"
					. "<span class=\"item-title\">".strip_tags($placemark->name)."</span>"
					. "</a></li>";
				} */
			}
		}
		$view->layers = $parent_layers;
		$view->layerChildrens = $childrenLayer;
		$view->js = new View('kmlfilter/layer_filter_js');
		return $view;
		// Return
// 		return $tree_html;
	}
	/* non pcode */
	public function addkmlfilter($params = array()) {
		
		// Fetch the URL data into a local variable
		$url_data = $_GET;
		
		// Split selected parameters on ","
		// For simplicity, always turn them into arrays even theres just one value
		$exclude_params = array('lkey');
		foreach ($url_data as $key => $value)
		{
			if (in_array($key, $exclude_params) AND ! is_array($value))
			{
				$url_data[$key] = explode(",", $value);
			}
		}
		if (isset($url_data['lkey']) AND is_array($url_data['lkey'])) {
			foreach($url_data['lkey'] as $lkey) {
				if(intval($lkey) > 0) {
					$lid[substr($lkey, 0, strpos($lkey, '_'))] = substr($lkey, 0, strpos($lkey, '_'));
					self::$lyr[substr($lkey, 0, strpos($lkey, '_'))][] = substr($lkey, strpos($lkey, '_')+1);
				}
			}
			if(isset($lid)) {
				$layers = ORM::factory('layer')->where('layer_visible', 1)->in('id', implode(',', $lid))->find_all();
				$locSQL = self::layer_polygon($layers, 'i.location_id');
				if ($locSQL !== false) {
					array_push($params, $locSQL);
				}
			}
		}
		return $params;
	}
	
	/* pcode
	public function addkmlfilter($params = array()) {
		// Fetch the URL data into a local variable
		$url_data = $_GET;
		
		// Split selected parameters on ","
		// For simplicity, always turn them into arrays even theres just one value
		if(isset($url_data['filterParams'])) {
			$url_data = json_decode($url_data['filterParams'], true);
			
		}
		$exclude_params = array('lkey');
		foreach ($url_data as $key => $value)
		{
			//print_r($url_data); exit;
			if (in_array($key, $exclude_params) AND ! is_array($value))
			{
				$url_data[$key] = explode(",", $value);
			}
		}
		//print_r($url_data); exit;
		
		
		if (isset($url_data['lkey']) AND is_array($url_data['lkey'])) {
					
			foreach($url_data['lkey'] as $lkey) {
				//print_r($lkey); exit;
				if(intval($lkey) > 0) {
					$lid[] = $lkey;
				}
			}
			//print_r($lid); exit;
			if(isset($lid)) {
				$locSQL = self::layer_polygon($lid, 'i.location_id');
				
				if ($locSQL !== false) {
					array_push($params, $locSQL);
				}
			}
		}
		
					
		return $params;
	}
	*/
	/* pcode
	public function layer_polygon($layers, $table = false) {
		if(!$table) $table = 'location_id';
		$locSQL = $query = false;
		if ($locSQL !== false) {
			array_push($params, $locSQL);
		}
		$locSQL .= ' ('.$table.' IN (';
		$layerKey = '';
		foreach($layers as $i => $lkey) {
			if(!empty($layerKey)) $layerKey .= ','; 
			$layerKey .= "'".$lkey."'";
		}
		$locSQL .= 'SELECT DISTINCT v.location_id FROM '.self::$table_prefix.'vw_placemark v WHERE v.lkey IN (';
		$locSQL .= $layerKey;
		$locSQL .= ')';
		$locSQL .= ')) ';
		if($locSQL !== false) return '('.$locSQL.')';
		return $locSQL;
	}
	*/
	/* non pcode */
	 public function layer_polygon($layers, $table = false) {
		if(!$table) $table = 'location_id';
		$locSQL = $query = false;
 		if (is_object($layers) AND ($layers instanceof ORM_Iterator)) {
			foreach($layers as $layer) {
				$poly_query = self::_layer_polygon($layer, $table);
				if($poly_query !== false) {
					if($locSQL !== false) $locSQL .= ' OR ';
					$locSQL .= $poly_query;
				}
			}
		} elseif (is_object($layers) AND ($layers instanceof ORM)) {
			$locSQL .= self::_layer_polygon($layers, $table);
		}
		if($locSQL !== false) return '('.$locSQL.')';
		return $locSQL;
	}
	
	protected function _layer_polygon($layer, $table) {
		$locSQL = false;
		$layer_url = $layer->layer_url;
		$layer_file = $layer->layer_file;
		
		if ($layer_url != '') {
			// Pull from a URL
			$layer_link = $layer_url;
		} else {
			// Pull from an uploaded file
			$layer_link = Kohana::config('upload.directory').'/'.$layer_file;
		}
		$content = file_get_contents($layer_link);
		if ($content !== false) {
			$xml = simplexml_load_string($content);
			foreach($xml->Document->Placemark as $placemark) {
				$poly_cor = false;
				if(in_array($placemark->ID, self::$lyr[$layer->id])) {
					$cord = strval($placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates);
					$cord = str_replace(" ", "\n", $cord);
					$cords = explode("\n", $cord);
					foreach($cords as $key => $cordinate) {
						$cor = explode(',', $cordinate);
						if(is_array($cor) && intval($cor[0]) != 0) {
							if($poly_cor !== false) $poly_cor .= ', ';
							$poly_cor .= $cor[1].' '.$cor[0];
						}
					}
				}
				if($poly_cor !== false) {
					if($locSQL !== false) $locSQL .= ' OR ';
					$locSQL .= '('.$table.' IN (';
					$locSQL .= 'SELECT DISTINCT id FROM '.self::$table_prefix.'location WHERE myWithin(PointFromText(CONCAT( "POINT(", latitude, " ", longitude, ")" )), PolyFromText("POLYGON(('.$poly_cor.'))"))';
					$locSQL .= '))';
				}
			}
		}
		return $locSQL;
	}
	
	public function addlayerfeatures($params = array()) {
		if(isset($params['layer_id']) && isset($params['content'])) {
			$xml = simplexml_load_string($params['content']);
			foreach($xml->Document->Placemark as $key => $placemark) {
				$placemarkKey = str_replace('#', '', $placemark->ID);
				$query = http_build_query(array_merge(
					array(
						'lkey[]' => $params['layer_id'].'_'.$placemarkKey,
					),
					$_GET
				));
				if($placemark->name) {
					$filterLink = '<span class = "mapkmlfilter_switch" style="font-weight: normal; font-size: .8em; margin-left: 10px;" onclick=triggerkmlfilter("'.$params['layer_id'].'_'.$placemarkKey.'");>( Filter )</span>';
					$placemark->name = $placemark->name.$filterLink;
				}
				$link = url::site('reports/index/?'.$query);
				if(!$placemark->link) {
					$placemark->addChild('link', '');
					$placemark->link = $link;
				}
			}
			$params['content'] = $xml->asXML();
		}
		return $params['content'];
	}

	public static function layer_kmlfilter($layer_id = null) {
		$layer_file = 'doc.kml';
		/*if(empty($layer_id)) {
			$layers = ORM::factory('layer')->where('layer_visible', 1)->find_all();
		} else {
			$layers[] = ORM::factory('layer')->where('id', $layer_id)->where('layer_visible', 1)->find();
		}
		foreach($layers as $layer) {
			if($layer->id != 0) {
			
				ORM::factory('kml_placemark')->where('layer_id', $layer->id)->delete_all();
				
				$layer_url = $layer->layer_url;
				$layer_file = $layer->layer_file;
			
				if ($layer_url != '') {
					// Pull from a URL
					$layer_link = $layer_url;
				} else {*/
					// Pull from an uploaded file
					$layer_link = Kohana::config('upload.directory').'/'.$layer_file;
				//}
				if(!empty($layer_link)) {
					$content = file_get_contents($layer_link);
					if ($content !== false) {
						$xml = simplexml_load_string($content);
						print_r($xml); exit;
						foreach($xml->Document->Placemark as $placemark) {
							$kml_placemark = new Kml_Placemark_Model();
							$kml_placemark->layer_id = $layer->id;
							$kml_placemark->placemark = $placemark->ID;
							$cord = strval($placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates);
							$cord = str_replace(" ", "\n", $cord);
							$cords = explode("\n", $cord);
							$poly_cor = false;
							foreach($cords as $key => $cordinate) {
								$cor = explode(',', $cordinate);
								if(is_array($cor) && intval($cor[0]) != 0) {
									if($poly_cor !== false) $poly_cor .= ', ';
									$poly_cor .= $cor[1].' '.$cor[0];
								}
							}
							$kml_placemark->coord = $poly_cor;
							$kml_placemark->save();
						}
					}
				}
			/*}
							
		}*/
	}

	public static function layer_placemark($layer_id = null) {

		if(empty($layer_id)) {
			$layers = ORM::factory('layer')->where('layer_visible', 1)->find_all();
		} else {
			$layers[] = ORM::factory('layer')->where('id', $layer_id)->where('layer_visible', 1)->find();
		}
		foreach($layers as $layer) {
			if($layer->id != 0) {
			
				ORM::factory('kml_placemark')->where('layer_id', $layer->id)->delete_all();
				
				$layer_url = $layer->layer_url;
				$layer_file = $layer->layer_file;
			
				if ($layer_url != '') {
					// Pull from a URL
					$layer_link = $layer_url;
				} else {
					// Pull from an uploaded file
					$layer_link = Kohana::config('upload.directory').'/'.$layer_file;
				}
				if(!empty($layer_link)) {
					$content = file_get_contents($layer_link);
					if ($content !== false) {
						$xml = simplexml_load_string($content);
						foreach($xml->Document->Placemark as $placemark) {
							$kml_placemark = new Kml_Placemark_Model();
							$kml_placemark->layer_id = $layer->id;
							$kml_placemark->placemark = $placemark->ID;
							$cord = strval($placemark->MultiGeometry->Polygon->outerBoundaryIs->LinearRing->coordinates);
							$cord = str_replace(" ", "\n", $cord);
							$cords = explode("\n", $cord);
							$poly_cor = false;
							foreach($cords as $key => $cordinate) {
								$cor = explode(',', $cordinate);
								if(is_array($cor) && intval($cor[0]) != 0) {
									if($poly_cor !== false) $poly_cor .= ', ';
									$poly_cor .= $cor[1].' '.$cor[0];
								}
							}
							$kml_placemark->coord = $poly_cor;
							$kml_placemark->save();
						}
					}
				}
			}
							
		}
	}

	
}
kmlfilter_helper_Core::init();
?>