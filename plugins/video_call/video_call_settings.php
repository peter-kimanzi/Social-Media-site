<?php
function video_call_settings() {
    global $CONF, $LNG, $pluginsSettings;

    // Settings Content
    return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_video_call_twilio_account_sid'].'</div>
			<div class="page-input-content">
				<input type="text" name="video_call_twilio_account_sid" value="'.$pluginsSettings['video_call_twilio_account_sid'].'">
				<div class="page-input-sub">'.$LNG['plugin_video_call_twilio_account_sid_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_video_call_twilio_key_sid'].'</div>
			<div class="page-input-content">
				<input type="text" name="video_call_twilio_key_sid" value="'.$pluginsSettings['video_call_twilio_key_sid'].'">
				<div class="page-input-sub">'.$LNG['plugin_video_call_twilio_key_sid_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_video_call_twilio_key_secret'].'</div>
			<div class="page-input-content">
				<input type="text" name="video_call_twilio_key_secret" value="'.$pluginsSettings['video_call_twilio_key_secret'].'">
				<div class="page-input-sub">'.$LNG['plugin_video_call_twilio_key_secret_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_video_call_dial_time'].'</div>
			<div class="page-input-content">
				<input type="number" name="video_call_dial_time" value="'.$pluginsSettings['video_call_dial_time'].'">
				<div class="page-input-sub">'.$LNG['plugin_video_call_dial_time_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_video_call_call_time'].'</div>
			<div class="page-input-content">
				<input type="number" name="video_call_call_time" value="'.$pluginsSettings['video_call_call_time'].'">
				<div class="page-input-sub">'.$LNG['plugin_video_call_call_time_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_video_call_save'].'">
	</div>
	</form></div><div>';
}

function video_call_save($values) {
    global $db;

    // Validate the inputs
    $values['video_call_dial_time'] = (int)$values['video_call_dial_time'];
    $values['video_call_call_time'] = (int)$values['video_call_call_time'];

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES ('video_call_twilio_account_sid', ?), ('video_call_twilio_key_sid', ?), ('video_call_twilio_key_secret', ?), ('video_call_dial_time', ?), ('video_call_call_time', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('sssii', $values['video_call_twilio_account_sid'], $values['video_call_twilio_key_sid'], $values['video_call_twilio_key_secret'], $values['video_call_dial_time'], $values['video_call_call_time']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>