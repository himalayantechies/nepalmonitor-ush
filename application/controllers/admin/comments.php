<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Comments Controller.
 * This controller will take care of viewing and editing comments in the Admin section.
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author     Ushahidi Team <team@ushahidi.com> 
 * @package    Ushahidi - http://source.ushahididev.com
 * @subpackage Admin
 * @copyright  Ushahidi - http://www.ushahidi.com
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */

class Comments_Controller extends Admin_Controller {
	
	public function __construct()
    {
        parent::__construct();
    
        $this->template->this_page = 'reports';
        
        // If user doesn't have access, redirect to dashboard
        if ( ! $this->auth->has_permission("reports_comments"))
        {
            url::redirect(url::site().'admin/dashboard');
        }
    }
    
    
    /**
    * Lists the reports.
    * @param int $page
    */
    function index($page = 1)
    {
        $this->template->content = new View('admin/comments/main');
        $this->template->content->title = Kohana::lang('ui_admin.comments');
        
        
        if (!empty($_GET['status']))
        {
            $status = $_GET['status'];
            
            if (strtolower($status) == 'a')
            {
                $filter = 'comment_active = 1 AND comment_spam = 0';
            }
            elseif (strtolower($status) == 'p')
            {
                $filter = 'comment_active = 0 AND comment_spam = 0';
            }
            elseif (strtolower($status) == 's')
            {
                $filter = 'comment_spam = 1';
            }
            else
            {
                $status = "0";
                $filter = 'comment_spam = 0';
            }
        }
        else
        {
            $status = "0";
            $filter = 'comment_spam = 0';
        }
        
        
        // check, has the form been submitted?
        $form_error = FALSE;
        $form_saved = FALSE;
        $form_action = "";
        if ($_POST)
        {
            $post = Validation::factory($_POST);
            
             //  Add some filters
            $post->pre_filter('trim', TRUE);

            // Add some rules, the input field, followed by a list of checks, carried out in order
            $post->add_rules('action','required', 'alpha', 'length[1,1]');
            $post->add_rules('comment_id.*','required','numeric');
            
            if ($post->validate())
            {
                if ($post->action == 'a')       
                { // Approve Action
            
		            $settings = kohana::config('settings');
					$site_name = $settings['site_name'];
					$alerts_email = ($settings['alerts_email']) ? $settings['alerts_email']
						: $settings['site_email'];
                    foreach($post->comment_id as $item)
                    {
                        $update = new Comment_Model($item);
                        if ($update->loaded == true) {
                            $update->comment_active = '1';
                            $update->comment_spam = '0';
                            $update->save();
                        }
                        $comment = ORM::factory('comment')->where('id', $item)->find();
						$subscribe = ORM::factory('comment')->where('incident_id', $comment->incident_id)->notin('id', $item)->select_list('comment_email', 'comment_author');
						$person = ORM::factory('incident_person')->where('incident_id', $comment->incident_id)->find_all();
						$phone = ORM::factory('form_response')->where('form_field_id', 2)->select_list('id', 'form_response');
						foreach($person as $per) {
							if(!empty($per->person_email)) {
								$subscribe[$per->person_email] = $per->person_first.' '.$per->person_last;
							} 
							if(!empty($per->person_phone)) {
								$phone[$per->person_phone] = $per->id; 
							}
						}
							
						$subject = "[".$site_name."] ".Kohana::lang('notifications.member_new_comment.subject');
						$message = text::auto_p(Kohana::lang('notifications.member_new_comment.message')
									. "\n" .url::site('reports/view/'.$comment->incident_id)
									. "\n\n".Kohana::lang('notifications.member_new_comment.comment').
									"\n".$comment->comment_description."\n");
						$sms_message = $message;
						$sms_message = str_replace("\n", " ", $sms_message);
						$sms_message = text::limit_chars($sms_message, 150, "...");
						foreach($subscribe as $mailAdd => $sub) {
							if(!empty($mailAdd)) {
								$from = array();
								$from[] = $alerts_email;
								$from[] = $site_name;
								$to = $mailAdd;
								
								email::send($to, $from, $subject, $message, TRUE);
							} 
						}	
						
						if (Kohana::config("settings.sms_no3"))
							$sms_from = Kohana::config("settings.sms_no3");
						elseif (Kohana::config("settings.sms_no2"))
							$sms_from = Kohana::config("settings.sms_no2");
						elseif (Kohana::config("settings.sms_no1"))
							$sms_from = Kohana::config("settings.sms_no1");
						else
							$sms_from = "12053705050";		// Admin needs to set up an SMS number	

						foreach($phone as $no => $ph) {
							if(!empty($no)) {
								$sms_to = $no;
								sms::send($sms_to, $sms_from, $sms_message);
							}
						}

                    }
                    $form_action = utf8::strtoupper(Kohana::lang('ui_admin.approved'));
                }
                elseif ($post->action == 'u')   
                { // Unapprove Action
                    foreach($post->comment_id as $item)
                    {
                        $update = new Comment_Model($item);
                        if ($update->loaded == true) {
                            $update->comment_active = '0';
                            $update->save();
                        }
                    }
                    $form_action = utf8::strtoupper(Kohana::lang('ui_admin.unapproved'));
                }
                elseif ($post->action == 's')   
                { // Spam Action
                    foreach($post->comment_id as $item)
                    {
                        $update = new Comment_Model($item);
                        if ($update->loaded == true) {
                            $update->comment_spam = '1';
                            $update->comment_active = '0';
                            $update->save();
                        }
                    }
                    $form_action = utf8::strtoupper(Kohana::lang('ui_admin.marked_as_spam'));
                }
                elseif ($post->action == 'n')   
                { // Spam Action
                    foreach($post->comment_id as $item)
                    {
                        $update = new Comment_Model($item);
                        if ($update->loaded == true) {
                            $update->comment_spam = '0';
                            $update->comment_active = '1';
                            $update->save();
                        }
                    }
                    $form_action = utf8::strtoupper(Kohana::lang('ui_admin.marked_as_not_spam'));
                }
                elseif ($post->action == 'd')   // Delete Action
                {
                    foreach($post->comment_id as $item)
                    {
                        $update = new Comment_Model($item);
                        if ($update->loaded == true)
                        {
                            $update->delete();
                        }                   
                    }
                    $form_action = Kohana::lang('ui_admin.deleted');
                }
                elseif ($post->action == 'x')   // Delete All Spam Action
                {
                    ORM::factory('comment')->where('comment_spam','1')->delete_all();
                    $form_action = Kohana::lang('ui_admin.deleted');
                }
                $form_saved = TRUE;
            }
            else
            {
                $form_error = TRUE;
            }
            
        }
        
        
        // Pagination
        $pagination = new Pagination(array(
            'query_string'    => 'page',
            'items_per_page' => $this->items_per_page,
            'total_items'    => ORM::factory('comment')->where($filter)->count_all()
        ));

        $comments = ORM::factory('comment')->where($filter)->orderby('comment_date', 'desc')->find_all($this->items_per_page, $pagination->sql_offset);
        
        $this->template->content->comments = $comments;
        $this->template->content->pagination = $pagination;
        $this->template->content->form_error = $form_error;
        $this->template->content->form_saved = $form_saved;
        $this->template->content->form_action = $form_action;
        
        // Total Reports
        $this->template->content->total_items = $pagination->total_items;
        
        // Status Tab
        $this->template->content->status = $status;
        
        // Javascript Header
        $this->template->js = new View('admin/comments/comments_js');        
    }
    
}
