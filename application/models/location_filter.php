<?php defined('SYSPATH') or die('No direct script access.');

/*
 Purpose:  Sets table name for Location_Filter
*/

class Location_Filter_Model extends ORM
{
	// Database table name
	protected $table_name = 'location_filter';
	
	
	protected $children = "location_filter";
}

class Location_Filter_Coord_Model extends ORM
{
	protected $table_name = 'loc_coord';
	protected $children = 'loc_coord';
}