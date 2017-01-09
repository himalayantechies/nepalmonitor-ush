<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Sharing Hook - Load All Events
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com> 
 * @package	   Ushahidi - http://source.ushahididev.com
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class sharing_hooks {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{
		// Dirty hack to load this plugin before the current theme, but after default
		$modules = Kohana::config('core.modules');
		$sharing_path = PLUGINPATH.'sharing_two';
		unset($modules[array_search($sharing_path, $modules)]);
		$d_index = array_search(THEMEPATH."default", $modules);
		$modules = array_merge(
			array_slice($modules, 0, $d_index),
			array($sharing_path),
			array_slice($modules, $d_index)
		);
		Kohana::config_set('core.modules', $modules);
		
		// Try to alter routing now
		Sharing::routing();
		
		// hook into routing - in case we're running too early
		Event::add_after('system.routing', array('Router', 'find_uri'), array('Sharing', 'routing'));
		
		//  Add other events just before controller runs
		Event::add('system.pre_controller', array($this, 'add'));
	}
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		// Only add the events if we are on that controller
		if (strripos(Router::$current_uri, "admin/manage") !== false)
		{
			Event::add('ushahidi_action.nav_admin_manage', array('Sharing','sharing_admin_nav'));
		}
		elseif (Router::$controller == "main")
		{
			Event::add('ushahidi_action.header_scripts', array('Sharing', 'sharing_bar_js'));
			Event::add('ushahidi_action.main_sidebar_post_filters', array('Sharing', 'sharing_bar'));
		}
		elseif (strripos(Router::$current_uri, 'json') === 0
			OR strripos(Router::$current_uri, 'reports') === 0
		)
		{
			// Quick hack to set default sharing value
			! isset($_GET['sharing']) ? $_GET['sharing'] = Kohana::config('sharing_two.default_sharing_filter') : null;
			
			if (strripos(Router::$current_uri, 'reports') === 0)
			{
				Event::add('ushahidi_filter.get_incidents_sql', array('Sharing', 'get_incidents_sql'));
				Event::add('ushahidi_filter.fetch_incidents_set_params', array('Sharing', 'fetch_incidents_set_params'));
			}

			//Event::add('ushahidi_filter.json_alter_markers', array($this, 'json_alter_markers'));
			//Event::add('ushahidi_filter.json_replace_markers', array($this, 'json_replace_markers'));
		}
	}

}
new sharing_hooks;
