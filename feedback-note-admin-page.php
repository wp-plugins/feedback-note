

<div class="wrap">

   <?php
	
	global $wpdb;
	//$feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	//$notes =  $wpdb->get_results('select * from ' . $feedback_note_table);
	define( 'SAVEQUERIES', true );
	$feedback_note_table = $wpdb->prefix . FEEDBACK_NOTE_SUFFIX;
	$feedback_note_comment_table = $wpdb->prefix . FEEDBACK_NOTE_COMMENT_SUFFIX;
	$users_table = $wpdb->prefix . 'users';

    $notes = $wpdb->get_results('select '.$feedback_note_table.'.*, '.$users_table.'.user_nicename from ' . $feedback_note_table . ' LEFT JOIN ' . $users_table .' ON '.$users_table.'.id = '.$feedback_note_table.'.user_id  ORDER BY '.$feedback_note_table.'.id ASC' );

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

    ?>

    <div id="head">
    <h1>FeedbackNote</h1>
  </div>

  <?php

  if (sizeof($notes) == 0) {
    ?>
    <p>No notes found</p>
    <?php
} else {
    ?>
    <table id="feedbacknote" class="tablesorter">
        <thead>
            <tr>    
                <th class="sorter-false"><input id="check_note" type="checkbox"></th>
                <th data-sorter="false">Note</th>
                <th>Comments</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Pixel Width</th>
                <th>Pixel Height</th>
                <th>Device</th>
                <th>Browser</th>
                <th>User Agent</th>
                <th>Category</th>
                <th>Relative URL</th>
                <th>Post ID</th>
                <th>Finished</th>
                <th>Exported</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            
            foreach ($notes as $note) {
            	
				$user_agent = json_decode($note->user_agent);
				
                ?>
                <tr class="tr_class" id="<?php echo $note->id ?>">
                    <td><input id="check_note" type="checkbox"></td>
                    <td><?php echo $note->text; ?></td>   
                    <td><?php 
						foreach ($note->comments as $comment) {
							echo $comment->user_nicename . ': ' . $comment->text . '<br/>';
						}

					?></td> 
                    <td><?php echo $note->created; ?></td>
                    <td><?php echo $note->updated; ?></td>
                    <td><?php echo $note->pixel_width; ?></td>
                    <td><?php echo $note->pixel_height; ?></td>
                    <td><?php 
                    	$format = "model: %s, type: %s, vendor: %s";
                    	if (!empty($user_agent->device) && !empty($user_agent->device->type)) {
                    		echo sprintf($format, $user_agent->device->model, $user_agent->device->type, $user_agent->device->vendor);  
                    	}  else {
                    		echo 'not detected';
						} 
                    ?></td>
                    	
                    <td><?php echo $note->browser; ?></td>
                    <td><?php 
						$format = 'OS: %s %s';
						echo sprintf($format, $user_agent->os->name, $user_agent->os->version);             	
                    	?>
                    </td>
                    <td><?php echo $note->category; ?></td>
                    <td><a class="popup-open" target="_blank" data-width="<?= $note->pixel_width ?>" data-height="<?= $note->pixel_height ?>" href="<?= $note->relative_url. '#feedbacknote='.$note->id; ?>"><?= $note->relative_url; ?></a></td>
                    <td><a class="popup-open" target="_blank" data-width="<?= $note->pixel_width ?>" data-height="<?= $note->pixel_height ?>" href="/?p=<?= $note->post_id . '#feedbacknote='.$note->id; ?>"><?= $note->post_id; ?></a></td>
                    <td><?php echo ($note->finished == '0000-00-00 00:00:00')? '' : $note->finished; ?></td>
                    <td><?php echo ($note->exported == '0000-00-00 00:00:00')? '' : $note->exported; ?></td>
                    <td><input class="status_note" type="button" value="<?= ($note->closed)? 'DONE' : 'NOT DONE'  ?>" id="status_note_<?php echo $note->id ?>" /></td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
    
    <?php
}

?>
<div id="select_project_managing_tool_div">

    <form>
        Select project managing tool:
        <select class="select_tool" id="select_tool">
            <option value="trello">Trello</option>
            <option value="basecamp">Basecamp</option>
        </select>
        <input id="select_project_managing_tool_button" type="button" value="OK" >
    </form>
</div>
<!-- Basecamp -->
<?php
{
	
	$appName = get_option('basecamp_app_name');;
	$appContact = get_option('basecamp_username');
	
	$basecampAccountId = get_option('basecamp_account_id');
	$basecampUsername = get_option('basecamp_username');
	$basecampPassword = get_option('basecamp_password');
	
	$baseUrl = "https://basecamp.com/$basecampAccountId/api/v1";
	
	$url= $baseUrl.'/projects.json';
	$credentials = "$basecampUsername:$basecampPassword";
	$helloHeader = "User-Agent: $appName ($appContact)";


	$ch = curl_init($url);
  	curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($helloHeader));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERPWD, $credentials);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output = curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $arr = json_decode($output, TRUE);
}
?>

<?php



?>
<!-- Basecamp ends -->


<div id="export_to_trello">
    <h2> Trello </h2>
</div>  

<div id="export_to_basecamp">
    <h2> Basecamp </h2>
    <form id="export_to_basecamp_form"> 
        <label>Which Board do you want to export to?</label> 
        <select id="board_basecamp" required> 

            <?php foreach($arr as $project){ ?>
            <option value="<?php echo $project['id']; ?>" name="<?php echo $project['name']; ?>"><?php echo $project['name']; ?></option> 
            <?php } ?>

        </select><br><br> 
        <input id="export_to_basecamp_form_submit" type="button" value="Export"> 
    </form><br> 
</div>




<script>
    var notes = <?php echo json_encode($notes); ?>;
</script>
</div>