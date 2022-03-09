<?php
require_once(__DIR__ .'/../../../includes/autoload.php');

if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

$output = [];
if(isset($user['username'])) {
	// Get the last incoming call newer than X minutes
	$incomingCalls = $db->query(sprintf("SELECT * FROM `video_calls` LEFT JOIN `users` ON `video_calls`.`from` = `users`.`idu` WHERE `video_calls`.`to` = '%s' AND `video_calls`.`status` = 0 AND `video_calls`.`time` > DATE_SUB(NOW(), INTERVAL %s SECOND) LIMIT 0,1", $db->real_escape_string($user['idu']), $pluginsSettings['video_call_dial_time']));

	while($row = $incomingCalls->fetch_assoc()) {
		$output['type'] = ($row['type'] ? 1 : 0);
		
		$output['caller_id'] = $row['idu'];
		
		$output['call_id'] = $row['id'];
		
		$output['output'] = '
		<div>
			<div class="video-call-modal-image"><img src="'.permalink($CONF['url'].'/image.php?t=a&w=50&h=50&src='.$row['image']).'"></div>
			<div class="video-call-modal-name">'.realName($row['username'], $row['first_name'], $row['last_name']).'</div>
		</div>
		';
		
		$output['title'] = ($row['type'] ? $LNG['plugin_video_call_incoming_video'] : $LNG['plugin_video_call_incoming_audio']);
		$output['buttons'] = '<div class="modal-btn button-active" id="video-call-answer-btn" onclick="video_call_answer()"><a>'.$LNG['plugin_video_call_answer'].'</a></div><div class="modal-cancel button-normal" id="video-call-decline-btn" onclick="video_call_decline()"><a>'.$LNG['plugin_video_call_decline'].'</a></div>';
	}
}
echo json_encode($output);

mysqli_close($db);
?>