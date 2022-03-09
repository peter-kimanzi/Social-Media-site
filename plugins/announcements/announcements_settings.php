<?php

function announcements_settings() {
	global $CONF, $LNG;
	
	// Settings Content
	return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_announcements_title'].'</div>
			<div class="page-input-content">
				<input type="text" name="announcements_title" maxlength="32">
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_announcements_content'].'</div>
			<div class="page-input-content">
				<textarea class="ads" name="announcements_content"></textarea>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_announcements_duration'].'</div>
			<div class="page-input-content">
				<select name="announcements_duration">
					'.announcements_duration(30).'
				</select>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_announcements_type'].'</div>
			<div class="page-input-content">
				<select name="announcements_type">
					<option value="0">Info</option>
					<option value="1">Success</option>
					<option value="2">Alert</option>
				</select>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_announcements_email_users'].'</div>
			<div class="page-input-content">
				<select name="announcements_email">
					<option value="0">No</option>
					<option value="1">Yes</option>
				</select>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_announcements_save'].'">
	</div>
	</form></div><div>'.announcements_lists().'
	';
}

function announcements_save($values) {
	global $db, $settings, $CONF;
	// Format the duration date
	$values['announcements_duration'] = date("Y-m-d", strtotime(date("Y-m-d") . ' + '.$values['announcements_duration'].' days'));
	
	// Validate the type
	if(!in_array($values['announcements_type'], array(0, 1, 2))) {
		$values['announcements_type'] = 0;
	}
	
	$values['announcements_title'] = substr($values['announcements_title'], 0, 32);
	
	// If title and content is being set
	if(!empty($values['announcements_title']) && !empty($values['announcements_content'])) {
		$values['announcements_title'] = strip_tags(substr($values['announcements_title'], 0, 32));
		
		// If the mass emailing option is being set
		if($values['announcements_email']) {
			
			// Select the user emails
			$userEmails = $db->query("SELECT `email` FROM `users` WHERE `suspended` = 0");

			$list = [];
			while($row = $userEmails->fetch_assoc()) {
				// Store the user emails
				$list[] = $row['email'];
			}
			
			// Send out the emails
			sendMail($list, $settings['title'].' - '.$values['announcements_title'], $values['announcements_content'], $CONF['email']);
		}
		
		$db->query(sprintf("INSERT INTO `announcements` (`title`, `content`, `type`, `duration`, `time`) VALUES ('%s', '%s', '%s', '%s', CURRENT_TIMESTAMP);", $db->real_escape_string($values['announcements_title']), $db->real_escape_string($values['announcements_content']), $db->real_escape_string($values['announcements_type']), $db->real_escape_string($values['announcements_duration'])));
		
		return 1;
	} else {
		return 0;
	}
}

function announcements_duration($i) {
	global $LNG;
	$output = '';
	for($i = 1; $i <= 30; $i++) {
		$output .= '<option value="'.$i.'">'.$i.' '.$LNG['plugin_announcements_days'].'</option>';
	}
	return $output;
}

function announcements_lists() {
	global $CONF, $db, $LNG;
	
	// Get the announcements
	$result = $db->query("SELECT * FROM `announcements` ORDER BY `id` DESC");
	$output = '';
	while($row = $result->fetch_assoc()) {
		$output .= '<div class="users-container">
						<div class="message-content">
							<div class="message-inner">
								<div class="users-button '.(date('d-m-Y') > date('d-m-Y', strtotime($row['duration'])) ? 'button-normal' : 'button-active').'"><a href="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'&token_id='.generateToken().'&delete='.$row['id'].'">'.$LNG['plugin_announcements_delete'].'</a></div>
								<div class="message-avatar">
									<img src="'.$CONF['url'].'/'.$CONF['plugin_path'].'/'.$_GET['settings'].'/images/'.$row['type'].'.png">
								</div>
								<div class="message-top">
									<div class="message-author" rel="loadpage">
										<strong>'.$row['title'].'</strong> ('.$row['duration'].')
									</div>
									<div class="message-time">
										'.$row['content'].'
									</div>
								</div>
							</div>
						</div>
					</div>';
	}
	return $output;
}

// Delete an announcement
if(isset($_GET['delete']) && $_GET['token_id'] == $_SESSION['token_id']) {
	$delAnn = $db->prepare(sprintf("DELETE FROM `announcements` WHERE `id` = '%s'", $db->real_escape_string($_GET['delete'])));
	$delAnn->execute();
	
	// If the announcement has been removed
	if($delAnn->affected_rows > 0) {
		header("Location: ".$CONF['url']."/index.php?a=admin&b=plugins&settings=".$_GET['settings']."&m=s");
	} else {
		header("Location: ".$CONF['url']."/index.php?a=admin&b=plugins&settings=".$_GET['settings']."&m=i");
	}
}

?>