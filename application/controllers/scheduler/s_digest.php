<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Digest Scheduler Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Ushahidi Team <team@ushahidi.com>
 * @package	   Ushahidi - http://source.ushahididev.com
 * @subpackage Scheduler
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license	   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL)
 */

class S_Digest_Controller extends Controller {

	public $table_prefix = '';

	// Cache instance
	protected $cache;

	function __construct() {
		parent::__construct();

		// Load cache
		$this -> cache = new Cache;

		// *************************************
		// ** SAFEGUARD DUPLICATE SEND-OUTS **
		// Create A 15 Minute SEND LOCK
		// This lock is released at the end of execution
		// Or expires automatically
		$digest_lock = $this -> cache -> get(Kohana::config('settings.subdomain') . "_digest_lock");
		if (!$digest_lock) {
			// Lock doesn't exist
			$timestamp = time();
			$this -> cache -> set(Kohana::config('settings.subdomain') . "_digest_lock", $timestamp, array("digest"), 900);
		} else {
			// Lock Exists - End
			exit("Other process is running - waiting 15 minutes!");
		}
		// *************************************
	}

	function __destruct() {
		$this -> cache -> delete(Kohana::config('settings.subdomain') . "_digest_lock");
	}

	public function index() {
		$settings = kohana::config('settings');

		if (!$settings['email_digest']) {
			return;
		}
		$site_name = $settings['site_name'];
		$alerts_email = ($settings['alerts_email']) ? $settings['alerts_email'] : $settings['site_email'];
		$unsubscribe_message = Kohana::lang('alerts.unsubscribe') . url::site() . 'alerts/unsubscribe/';
		$company_note = Kohana::lang('alerts.digest_company_note')."<br/><br/>";

		$database_settings = kohana::config('database');
		//around line 33
		$this -> table_prefix = $database_settings['default']['table_prefix'];
		//around line 34

		$db = new Database();

		/* Find All Alerts with the following parameters
		 - incident_active = 1 -- An approved incident
		 - incident_alert_status = 2 -- Incident has been sent to individuals

		 Incident Alert Statuses
		 - 0, Incident has not been tagged for sending. Ensures old incidents are not sent out as alerts
		 - 1, Incident has been tagged for sending by updating it with 'approved' or 'verified'
		 - 2, Incident has been tagged as sent. No need to resend again
		 */

		// Fixes an issue with one report being sent out as an alert more than ones
		// becoming spam to users

		$alert_query = "SELECT a.*, MAX(s.alert_date) as last_date FROM " . $this -> table_prefix . "alert AS a 
			LEFT JOIN " . $this -> table_prefix . "alert_sent AS s ON
				a.id = s.alert_id
			WHERE a.alert_type = 3
				AND a.alert_confirmed = 1
			GROUP BY a.id
			ORDER BY s.alert_date DESC";

		$alertees = $db -> query($alert_query);

		foreach ($alertees as $alertee) {
			$alert_incident = array();
			if($alertee->last_date > $settings['last_digest_schedule']) continue;
			
			$alert_radius = (int)$alertee -> alert_radius;
			$alert_type = (int)$alertee -> alert_type;
			$latitude = (double)$alertee -> alert_lat;
			$longitude = (double)$alertee -> alert_lon;

			// Find all the catecories including parents
			$category_ids = $this -> _find_categories($alertee -> id);

			$to = $alertee -> alert_recipient;
			$from = array();
			$from[] = $alerts_email;
			$from[] = $site_name;
			$subject = "[$site_name] Email Digest - ".date("dd M, Y");
			// HT: html br for \n
			$message_end = "<br/><br/>" . $unsubscribe_message . $alertee -> alert_code . '<br/>' . Kohana::lang('alerts.disclaimer') . "<br/>";
			$incident_msg_list = "";
			$message = "";

			// Get all incidents
			$alert_sent = ORM::factory('alert_sent') -> where('alert_id', $alertee -> id) -> select_list('id', 'incident_id');

			$incident_query = "SELECT i.id, incident_title,
                       incident_description, incident_verified,
                       l.latitude, l.longitude FROM " . $this -> table_prefix . "incident AS i INNER JOIN " . $this -> table_prefix . "location AS l ON i.location_id = l.id
                       WHERE i.incident_active=1 AND i.incident_alert_status = 2 
					   AND (DATE_FORMAT(i.incident_datemodify,'%Y-%m-%d %T')>= '" . ($settings['last_digest_schedule']) . "') ";
			if (!empty($alert_sent))
				$incident_query .= " AND i.id NOT IN (" . implode(",", $alert_sent). ")";
			if ($digest_days = $settings['digest_days']) {
				$incident_query .= " AND DATE_FORMAT(i.incident_datemodify,'%Y-%m-%d %T') >= DATE_SUB( NOW(), INTERVAL " . ($digest_days) . " DAY )";
			}
			$incident_query .= " ORDER BY l.latitude DESC";
			$incidents = $db -> query($incident_query);
			$incident_count = 1;
			foreach ($incidents as $incident) {
				
				$longitude2 = $incident->longitude;
				$latitude2 = $incident->latitude;
				// HT: check same alert_receipent multi subscription does not get multiple alert
				if ($this -> _multi_subscribe($alertee, $incident -> id)) {
					continue;

				}
				// Check the categories
				if (!$this -> _check_categories($incident, $category_ids)) {
					continue;
				}
				
				$distance = (string)new Distance($latitude, $longitude, $latitude2, $longitude2);

				// If the calculated distance between the incident and the alert fits...
				if ($distance <= $alert_radius) {
					$incident_title = '<span id="title-'.$incident -> id.'">'.$incident_count.'. '.$incident -> incident_title.'</span>';
					$title_anchor = '<a href="#title-'.$incident -> id.'">'.$incident_count.'. '.$incident -> incident_title.'</a><br/>';
					$incident_description = $incident -> incident_description;
					$incident_url = url::site() . 'reports/view/' . $incident -> id;
					$html2text = new Html2Text($incident_description);
					// HT: br for \n
					$email_message = $incident_title . "<br/><br/>". $incident_description . "<br/><br/>" . $incident_url.'<br/><br/><hr><br/><br/>';
					$alert_incident[$incident -> id] = $incident -> id;
					$message .= $email_message;
					$incident_msg_list .=  $title_anchor;
					$incident_count++;
				}

			}
			$message = $company_note. $incident_msg_list . "<br/><br/>" . $message . $message_end;
			if(!empty($alert_incident)) {
				if (email::send($to, $from, $subject, $message, TRUE) == 1)// HT: New Code to make email as html
				{
					$alert = ORM::factory('alert_sent');
					foreach ($alert_incident as $incident_sent) {
						$alert -> clear();
						$alert -> alert_id = $alertee -> id;
						$alert -> incident_id = $incident_sent;
						$alert -> alert_date = date("Y-m-d H:i:s");
						$alert -> save();
					}
				}
			}
		}
		// Update Last schedule date - All Digest  Have Been Sent!
		$update_setting = ORM::factory('settings')->where('key', 'last_digest_schedule');
		$update_setting -> find();
		$update_setting -> value = date("Y-m-d H:i:s");
		$update_setting -> save();
	}

	private function _find_categories($alert_id) {
		$ret = array();
		$alert_categories = ORM::factory('alert_category') -> where('alert_id', $alert_id) -> find_all();

		foreach ($alert_categories as $ac) {
			$category = ORM::factory('category') -> where('id', $ac -> category_id) -> find();
			$this -> _add_category($ret, $category);
		}

		return $ret;
	}

	private function _add_category(array & $ids, Category_Model $category) {
		if ($category == null) {
			return;
		}

		$id = (string)$category -> id;

		if (!array_key_exists($id, $ids)) {
			$ids[$id] = 1;
		}

		if ($category -> parent_id != 0) {
			$parent = ORM::factory('category') -> where('id', $category -> parent_id) -> find();

			$this -> _add_category($ids, $parent);
		}
	}

	private function _check_categories($incident, array $category_ids) {
		$ret = false;
		$incident_categories = ORM::factory('incident_category') -> where('incident_id', $incident -> id) -> find_all();
		if (count($incident_categories) == 0) {
			$ret = false;
		} elseif(empty($category_ids)) {
				$ret = true;
		} else {
			foreach ($incident_categories as $ic) {
				if (array_key_exists((string)$ic -> category_id, $category_ids)) {
					$ret = true;
				}
			}
		}
		return $ret;
	}

	/**
	 * HT: Function to verify that alert is not sent to same alert_receipent being subscribed multiple time
	 * @param Alert_Model $alertee
	 * @param integer $incident_id
	 * @return boolean
	 */
	private function _multi_subscribe($alertee, $incident_id) {
		$multi_subscribe_ids = ORM::factory('alert') -> where('alert_confirmed', '1') -> where('alert_recipient', $alertee -> alert_recipient) -> where('alert_type', 3) -> select_list('id', 'id');
		$subscription_alert = ORM::factory('alert_sent') -> where('incident_id', $incident_id) -> in('alert_id', $multi_subscribe_ids) -> find();
		return ((boolean)$subscription_alert -> id);
	}

}
