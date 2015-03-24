<?php
defined( 'ABSPATH' ) OR exit;
/**
 * Plugin Name: FeedbackNote
 * Plugin URI: http://ding.se
 * Version: 0.0.1
 * Description: "Rocknroll"
 */

// TODO SQL INJECTIONS ETC

define('FEEDBACK_NOTE_SUFFIX', 'feedback_note');

define('FEEDBACK_NOTE_COMMENT_SUFFIX', 'feedback_note_coment');

define('SHOW_FEEDBACK_NOTE_KEY', 'show_feedback_note');


//Front End
add_action('wp_ajax_feedback_api_save', 'feedback_api_save');
add_action('wp_ajax_feedback_api_delete', 'feedback_api_delete');
add_action('wp_ajax_feedback_api_list', 'feedback_api_list');
add_action('wp_ajax_feedback_api_update', 'feedback_api_update');
add_action('wp_ajax_feedback_api_update_status', 'feedback_api_update_status');
add_action('wp_ajax_feedback_api_update_export', 'feedback_api_update_export');
add_action('wp_ajax_feedback_api_update_position', 'feedback_api_update_position');
add_action('wp_ajax_feedback_api_hello', 'feedback_api_hello');
add_action('wp_ajax_feedback_api_comment_save', 'feedback_api_comment_save');
add_action('wp_ajax_feedback_api_comment_delete', 'feedback_api_comment_delete');
add_action('wp_ajax_feedback_api_comment_list', 'feedback_api_comment_list');
add_action('wp_ajax_feedback_api_comment_update', 'feedback_api_comment_update');
//Admin
add_action('wp_ajax_feedback_admin_list', 'feedback_admin_list');
add_action('wp_ajax_feedback_admin_done', 'feedback_admin_done');
add_action('wp_ajax_feedback_admin_delete', 'feedback_admin_delete');  
//Basecamp
add_action('wp_ajax_feedback_basecamp_export', 'feedback_basecamp_export');
//Init Scripts last
add_action('wp_enqueue_scripts', 'feedback_note_initialize_scripts', 999999); //load all last

function feedback_api_save() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $x_percent = $_POST['feedback_note_new_note_x_percent'];
    $y_percent = $_POST['feedback_note_new_note_y_percent'];
    $pixel_width = $_POST['feedback_note_new_note_pixel_width'];
    $pixel_height = $_POST['feedback_note_new_note_pixel_height'];
    $browser = $_POST['feedback_note_new_note_browser'];
	$user_agent = stripslashes($_POST['feedback_note_new_note_user_agent']); //we save json to db 
    $text = $_POST['feedback_note_new_note_text'];
    $relative_url = $_POST['feedback_note_new_note_url'];
    $category = $_POST['feedback_note_new_note_category'];
    $data = array(
        'user_id' => $user_id,
        'created' => current_time('mysql', 1),
        'updated' => current_time('mysql', 1),
        'x_percent' => $x_percent,
        'y_percent' => $y_percent,
        'pixel_width' => $pixel_width,
        'pixel_height' => $pixel_height,
        'browser' => $browser,
        'user_agent' => $user_agent,
        'text' => $text,
        'category' => $category,
        'relative_url' => $relative_url,
        'post_id' => url_to_postid( $relative_url )
        );

    global $wpdb;

    $formats = array(
        '%d',
        '%s',
        '%s',
        '%f',
        '%f',
        '%d',
        '%d',
        '%s',
        '%s',
        '%s',
        '%s',
        '%s'
        );

    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->insert($feedback_note_table, $data, $formats);
	
	if($result){ //if suceess send email
		$note = feedback_api_get_note($wpdb->insert_id);
		send_mails($note[0]->id, $note[0]->post_id,'added',$note[0]->text);
	}

    if (!$result) {
        return_json_status(-1);
    } else {
        return_json_status_success_with_id($wpdb->insert_id);
    }

    die();
}

function feedback_api_delete() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $id = $_POST['id'];

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->delete($feedback_note_table, array('id' => $id, 'user_id' => $user_id));

    if (!$result) {
        return_json_status(-1);
    } else {
        return_json_status(0);
    }

    die();
}

function feedback_api_list() {
    	
	$relative_url = $_POST['relative_url'];
	$post_id = url_to_postid($relative_url);
	
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	$feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;
	$users_table = $wpdb->prefix . 'users';

    $notes = $wpdb->get_results('select '.$feedback_note_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_table.'.user_id where (relative_url="'. $relative_url. '" OR post_id="'. $post_id .'") ORDER BY '.$feedback_note_table.'.id ASC' );
    
	foreach ($notes as $note) {
		
		if($note->user_id == $user_id) {//is user owner of note
			$note->owner = true;
		} else {
			$note->owner = false;
		}
		
		$note->user_img = get_avatar( $note->user_id, 45 );
		
		$note->comments= $wpdb->get_results('select '.$feedback_note_comment_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_comment_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_comment_table.'.user_id where note_id=' . $note->id  );
		if(count($note->comments)>0)
		{
			foreach ($note->comments as $comment) {
				
			if($comment->user_id == $user_id) {//is user owner of note
				$comment->owner = true;
			} else {
				$comment->owner = false;
			}
				
				$comment->user_img = get_avatar( $comment->user_id, 38 );
			}
		}
	}
	
    echo json_encode($notes);
    die();
}

function feedback_api_update() {
	
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $id = $_POST['feedback_note_update_note_id'];
    $text = $_POST['feedback_note_update_note_text'];
    $category = $_POST['feedback_note_update_note_category'];

    $update_args = array('text' => $text, 'category' => $category, 'updated' => current_time('mysql', 1));
    $where_args = array('id' => $id, 'user_id' => $user_id);

    $format_args = array('%s', '%s', '%s');

    $where_format_args = array('%d', '%d');

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->update($feedback_note_table, $update_args, $where_args, $format_args, $where_format_args);
	
	if($result){ //if sucees send email
		$note = feedback_api_get_note($id);
		send_mails($note[0]->id, $note[0]->post_id,'updated',$note[0]->text);
	}

    return_json_status(!$result ? -1 : 0);

    die();
}

function feedback_api_update_status() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $id = $_POST['feedback_note_update_note_id'];
    $status = $_POST['status'];

    $update_args = array('closed' => $status, 'updated' => current_time('mysql', 1));
    $where_args = array('id' => $id);

    $format_args = array('%d', '%s');

    $where_format_args = array('%d');

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->update($feedback_note_table, $update_args, $where_args, $format_args, $where_format_args);
	
	if($result){ //if suceess send email
		$note = feedback_api_get_note($id);
		send_mails($note[0]->id, $note[0]->post_id,($status)?"marked done":"marked not done",$note[0]->text);
	}

    return_json_status(!$result ? -1 : 0);

    die();
}

function feedback_api_update_export(){
	$result = _feedback_api_update_export(json_decode(stripslashes($_POST['notes'])));
	return_json_status(!$result ? -1 : 0);
	die();
}

function _feedback_api_update_export($notes) {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
	
	global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	
	$update_args = array('exported' => current_time('mysql', 1));
    
    $format_args = array('%s');

    $where_format_args = array('%d');

	$note_ids = array();
	foreach ($notes as $note) {
		$where_args = array('id' => $note->id);
		$result = $wpdb->update($feedback_note_table, $update_args, $where_args, $format_args, $where_format_args);
	}
	
	
	return $result;

}

function feedback_api_comment_save() {
	$current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $note_id = $_POST['note_id'];
	$text = $_POST['text'];
	
	    $data = array(
        'user_id' => $user_id,
        'note_id' => $note_id,
        'text' => $text,
        'created' => current_time('mysql', 1),
        );

    global $wpdb;

    $formats = array(
        '%d',
        '%d',
        '%s',
        '%s'
        );

    $feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;

    $result = $wpdb->insert($feedback_note_comment_table, $data, $formats);
	
	if($result){ //if suceess send email
		$note = feedback_api_get_note($wpdb->insert_id);
		send_mails($note[0]->id, $note[0]->post_id,"added comment",$note[0]->text .'<br/>'. $text);
	}
	
    if (!$result) {
        return_json_status(-1);
    } else {
        return_json_status_success_with_id($wpdb->insert_id);
    }

    die();
	
	
}

function feedback_api_comment_delete() {
		
	$current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $id = $_POST['id'];

    global $wpdb;
    $feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;

    $result = $wpdb->delete($feedback_note_comment_table, array('id' => $id, 'user_id' => $user_id));

    if (!$result) {
        return_json_status(-1);
    } else {
        return_json_status(0);
    }

    die();
}

function feedback_api_get_note($id = null) {
	global $wpdb;

	$feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	$feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;
	$users_table = $wpdb->prefix . 'users';
	
	if(is_null($id)){
    	$notes = $wpdb->get_results('select '.$feedback_note_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_table.'.user_id  ORDER BY '.$feedback_note_table.'.id ASC' );
	} else {
		$notes = $wpdb->get_results('select '.$feedback_note_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_table.'.user_id WHERE '.$feedback_note_table.'.id='.$id.' ORDER BY '.$feedback_note_table.'.id ASC' );
	}

	foreach ($notes as $note) {
		
		if($note->user_id == $user_id) {//is user owner of note
			$note->owner = true;
		} else {
			$note->owner = false;
		}
		
		$note->user_img = get_avatar( $note->user_id, 45 );
		
		$note->comments= $wpdb->get_results('select '.$feedback_note_comment_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_comment_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_comment_table.'.user_id where note_id=' . $note->id  );
		if(count($note->comments)>0)
		{
			foreach ($note->comments as $comment) {
				
			if($comment->user_id == $user_id) {//is user owner of note
				$comment->owner = true;
			} else {
				$comment->owner = false;
			}
				
				$comment->user_img = get_avatar( $comment->user_id, 38 );
			}
		}
	}

	return $notes;
}

function feedback_hello() {


    die("hej");
}

function feedback_api_update_position() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $id = $_POST['id'];
    $x_percent = $_POST['x_percent'];
    $y_percent = $_POST['y_percent'];
    $pixel_width = $_POST['pixel_width'];
    $pixel_height = $_POST['pixel_height'];
    $browser = $_POST['browser'];

    $update_args = array(
        'x_percent' => $x_percent,
        'y_percent' => $y_percent,
        'pixel_width' => $pixel_width,
        'pixel_height' => $pixel_height,
        'browser' => $browser,
        'updated' => current_time('mysql', 1)
        )
    ;
    $where_args = array('id' => $id, 'user_id' => $user_id);

    $format_args = array('%f', '%f', '%d', '%d', '%s', '%s');

    $where_format_args = array('%d', '%d');

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
define('SAVEQUERIES', true);
    $result = $wpdb->update($feedback_note_table, $update_args, $where_args, $format_args, $where_format_args);
//print_r($wpdb->queries);
    return_json_status(!$result ? -1 : 0);

    die();
}

function feedback_admin_list() {
    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    return $wpdb->get_results('select * from ' . $feedback_note_table);
}

function feedback_admin_done() {
	$id = $_POST['id'];
	$status = !($_POST['status'] == 'DONE')? 1 : 0;

	if($status){
		$finished = current_time('mysql', 1);
	} else {
		$finished = '0000-00-00 00:00:00';
	}
	
	$update_args = array('closed' => $status, 'finished' => $finished);
	$where_args = array('id' => $id);
	$format_args = array('%d', '%s');
	$where_format_args = array('%d');
	    

    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->update($feedback_note_table, $update_args, $where_args, $format_args, $where_format_args);

    return_json_status(!$result ? -1 : 0);

    die();
}

function feedback_admin_delete($id) {
    global $wpdb;
    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;

    $result = $wpdb->delete($feedback_note_table, array('id' => $id));
}

function return_json_status($s) {
    echo '{ "status": ' . $s . '}';
}

function return_json_status_success_with_id($id) {
    echo '{ "status": 0, "id": ' . $id . ' }';
}

define('DB_VERSION', 1.0);
define('FEEDBACK_NOTE_DB_VERSION_KEY', 'DB_VERSION');

function install_feedback_note() {
    if (!current_user_can('activate_plugins'))
        return;

    global $wpdb;

    $installed_db_version = get_option(FEEDBACK_NOTE_DB_VERSION_KEY);

    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	$feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;

	//feebback note table
    $show_table_string = "SHOW TABLES LIKE '" . $feedback_note_table . "'";
    if($wpdb->query($show_table_string) != 1) {
        create_feedback_note_table($feedback_note_table);
    } else if ($installed_db_version != DB_VERSION) {
        // In the future handle db upgrades here.
    }
	
	//feebback note comment table
    $show_table_string = "SHOW TABLES LIKE '" . $feedback_note_comment_table . "'";
    if($wpdb->query($show_table_string) != 1) {
        create_feedback_note_comment_table($feedback_note_table, $feedback_note_comment_table);
    } else if ($installed_db_version != DB_VERSION) {
        // In the future handle db upgrades here.
    }
}

function create_feedback_note_table($feedback_note_table) {
    global $wpdb;

    $wp_users_table_name = $wpdb->prefix . "users";

    $sql = "CREATE TABLE IF NOT EXISTS $feedback_note_table  (
      id INT NOT NULL AUTO_INCREMENT,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
      exported DATETIME DEFAULT '0000-00-00 00:00:00' NULL,
      finished DATETIME DEFAULT '0000-00-00 00:00:00' NULL,
      closed tinyint(1) DEFAULT 0 NOT NULL,
      x_percent DECIMAL(11,10) NOT NULL,
      y_percent DECIMAL(11,10) NOT NULL,
      pixel_width INT NOT NULL,
      pixel_height INT NOT NULL,
      browser VARCHAR(1000) CHARACTER SET UTF8 NOT NULL,
      user_agent TEXT CHARACTER SET UTF8 NOT NULL,
      text VARCHAR(500) CHARACTER SET UTF8 NOT NULL,
      category VARCHAR(255) CHARACTER SET UTF8 NOT NULL,
      relative_url VARCHAR(500) CHARACTER SET UTF8 NOT NULL,
      post_id VARCHAR(500) CHARACTER SET UTF8 NOT NULL,
      FOREIGN KEY user_id (user_id) REFERENCES $wp_users_table_name (ID),
      PRIMARY KEY  (id)
      );";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);

add_option(FEEDBACK_NOTE_DB_VERSION_KEY, DB_VERSION);

add_option(SHOW_FEEDBACK_NOTE_KEY, true);
}

function uninstall_feedback_note() {
    if (!current_user_can('activate_plugins'))
        return;

    delete_option(FEEDBACK_NOTE_DB_VERSION_KEY);

    delete_option(SHOW_FEEDBACK_NOTE_KEY);

    global $wpdb;

    $feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
    $feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;

    $sql_comments_drop = "DROP TABLE IF EXISTS $feedback_note_comment_table";
    $sql_notes_drop = "DROP TABLE IF EXISTS $feedback_note_table;";
    
    $wpdb->query($sql_comments_drop);
    $wpdb->query($sql_notes_drop);
    
    
}


function create_feedback_note_comment_table($feedback_note_table, $feedback_note_comment_table) {
    global $wpdb;

    $wp_users_table_name = $wpdb->prefix . "users";
    
     $sql = "CREATE TABLE IF NOT EXISTS $feedback_note_comment_table  (
      id INT NOT NULL AUTO_INCREMENT,
      user_id BIGINT(20) UNSIGNED NOT NULL,
      note_id INT NOT NULL,
      created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL,
 	  text VARCHAR(500) CHARACTER SET UTF8 NOT NULL, 
      FOREIGN KEY user_id (user_id) REFERENCES $wp_users_table_name (ID) ON DELETE CASCADE,
      FOREIGN KEY note_id (note_id) REFERENCES $feedback_note_table (ID) ON DELETE CASCADE,
      PRIMARY KEY  (id)
      );";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

dbDelta($sql);

add_option(FEEDBACK_NOTE_DB_VERSION_KEY, DB_VERSION);

add_option(SHOW_FEEDBACK_NOTE_KEY, true);
}

function feedback_note_initialize_scripts() {
    if (is_user_logged_in() && !is_admin() && get_option(SHOW_FEEDBACK_NOTE_KEY)) {
    	wp_enqueue_style('feedback-note-reset',
          plugin_dir_url(__FILE__) . 'css/feedback-note-reset.css');

        wp_enqueue_style('feedback-note-style',
            plugin_dir_url(__FILE__) . 'frontend-client/feedback-note-style.css');

        wp_enqueue_script('feedback_note_ajax_handle',
            plugin_dir_url(__FILE__) . 'frontend-client/feedback-note.js',
            array('backbone', 'underscore', 'jquery-ui-draggable'));
		 
		wp_enqueue_script('timeago',
            plugin_dir_url(__FILE__) . 'frontend-client/timeago.js');
            
        wp_enqueue_script('ua-parser',
            plugin_dir_url(__FILE__) . 'frontend-client/ ua-parser.min.js');
            
        wp_localize_script('feedback_note_ajax_handle', 'feedback_note_ajax_script',
            array('feedback_note_ajax_url' => admin_url('admin-ajax.php')));
    } 
}

function register_feedback_note_menu_page() {
    $page = add_menu_page('FeedbackNote', 'FeedbackNote', 'administrator', "feedbacknote", "create_feedbacknote_admin_page",
        plugin_dir_url(__FILE__) . 'images/admin_small_icon.png', 6);

        add_action( 'load-' . $page, 'feedbacknote_admin_page_assets' );
};

function create_feedbacknote_admin_page() {
    global $title;
    include plugin_dir_path(__FILE__) . "feedback-note-admin-page.php";
}

function feedbacknote_admin_page_assets() {
	
	wp_enqueue_script('feedback_note_admin_ajax_handle',
        plugin_dir_url(__FILE__) . 'feedback-note-admin-page.js', 
        array('backbone', 'underscore', 'jquery-ui-draggable'));
		
	wp_enqueue_script('jquery_tablesorter_js',
        plugin_dir_url(__FILE__) . 'frontend-client/jquery.tablesorter.min.js', ''); 
       
		
	wp_localize_script('feedback_note_admin_ajax_handle', 'feedback_note_ajax_script',
        array('feedback_note_ajax_url' => admin_url('admin-ajax.php'),
				'site_url'=>site_url()));
	
	 wp_enqueue_style('feedback-note-admin-style',
            plugin_dir_url(__FILE__) . 'css/feedback-note-admin-page.css');

	 wp_enqueue_style('jquery_tablesorter_style',
            plugin_dir_url(__FILE__) . 'frontend-client/themes/blue/style.css');
            
	//Trelo
	wp_enqueue_script('trelo_client_js', 
		'//api.trello.com/1/client.js?key=7542a5a908fc2ed71055fc8e85e2d3b9','');	

}

register_activation_hook(__FILE__, 'install_feedback_note');
register_uninstall_hook(__FILE__, 'uninstall_feedback_note');

add_action('admin_menu', 'register_feedback_note_menu_page');

if (!function_exists('write_log')) {
    function write_log ( $log )  {
        if ( true === WP_DEBUG ) {
            if ( is_array( $log ) || is_object( $log ) ) {
                error_log( print_r( $log, true ) );
            } else {
                error_log( $log );
            }
        }
    }
}

/**
 * Sent mail to all user about note id
 * 
 * @param int $id of note
 * @param int|string $url url to note or post_id
 * @param string $type action like added, updated, deleted
 * @param string $text content of note or comment
 * 
 * @return void
 */
 
function send_mails($id, $url, $type, $text){
	
	$current_user = wp_get_current_user();
	
	$users = get_users( $args );
	foreach ($users as $user) {
		
		$to = $user->user_email;
		$subject = $type;
		$body = 'Hello, '. $user_nicename . '<br/> Note id: '.$id.' has been '. $type .' by '.$current_user->user_nicename.'<br/><br/>';
		$body .= $text .'<br/><br/>';
		
		if(is_int($url)) //if url is post id we shell use that
		{
			$body .= site_url() .'/?p='. $url . '#feedbacknote='.$id;
		} else { //url is some custom page so we target that url
			$body .= site_url() .'/'. $url . '#feedbacknote='.$id;
		}
		
		$headers = array('Content-Type: text/html; charset=UTF-8');
		
		wp_mail( $to, $subject, $body, $headers );
	}
}

/**
 * Settings Page
 */
add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {
	add_options_page('My Options', 'FeedbackNote', 'manage_options', 'feedback_note_settings', 'feedback_note_settings_page');
	
}


function feedback_note_settings_page() {
 		
 	$basecamp_app_name = get_option('basecamp_app_name');
 	$basecamp_account_id = get_option('basecamp_account_id');
    $basecamp_username = get_option('basecamp_username');
	$basecamp_password = get_option('basecamp_password');

    $html = '</pre>
<div class="wrap"><form action="options.php" method="post" name="options">
<h2>FeedBackNote Settings</h2>
<h3>Basecamp - Account</h3>
' . wp_nonce_field('update-options') . '
<table class="form-table" width="100%" cellpadding="10">
<tbody>
<tr>
<td scope="row" align="left">
 <label>App name</label>
 <input type="text" name="basecamp_app_name" value="'. $basecamp_app_name .'"/>
</tr>
<tr>
<td scope="row" align="left">
 <label>Account ID</label>
 <input type="text" name="basecamp_account_id" value="'. $basecamp_account_id .'"/>
</tr>
<tr>
<td scope="row" align="left">
 <label>Username</label>
 <input type="text" name="basecamp_username" value="'. $basecamp_username .'"/>
</tr>
<tr>
<td scope="row" align="left">
 <label>Password</label>
 <input type="password" name="basecamp_password" value="'. $basecamp_password .'"/>
</td>
</tr>
<tr>
</tbody>
</table>
 <input type="hidden" name="action" value="update" />
 <input type="hidden" name="page_options" value="basecamp_app_name,basecamp_account_id,basecamp_username,basecamp_password,basecamp_url" />
 <input type="submit" name="Submit" value="Update" /></form></div>
<pre>
';
 
    echo $html;
 
}

function postToBasecamp($bord_id, $data){
	
	$appName = get_option('basecamp_app_name');;
	$appContact = get_option('basecamp_username');
	
	$basecampAccountId = get_option('basecamp_account_id');
	$basecampUsername = get_option('basecamp_username');
	$basecampPassword = get_option('basecamp_password');
	
	$baseUrl = "https://basecamp.com/$basecampAccountId/api/v1";
	
	$urlPost= $baseUrl.'/projects/'.$bord_id.'/todolists.json';
	$credentials = "$basecampUsername:$basecampPassword";
	$helloHeader = "User-Agent: $appName ($appContact)";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $urlPost);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($helloHeader, 'Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);                                                                  
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

}

function feedback_basecamp_export(){
		
	$bord_id = $_POST['boardid'];
	$notes = json_decode(stripslashes($_POST['notes']));
	
	$result = true;	//TODO::cache curl errors
	
    foreach($notes as $note){
		$data = array(
  			"name"=> $note->text,
  			"description"=> site_url() . '?p='. $note->post_id . '#feedbacknote=' . $note->id 
  		);  
		postToBasecamp($bord_id, json_encode($data));
    }
	//TODO::validate all updates
	_feedback_api_update_export($notes);
	
	return_json_status(!$result ? -1 : 0);
	die();
}

?>