<?php
require_once(__DIR__ .'/../../../includes/autoload.php');

if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($user['username'])) {
	// Decline call
	if($_POST['type'] == 3) {
		// $incomingCalls = $db->query(sprintf("SELECT * FROM `video_calls` LEFT JOIN `users` ON `video_calls`.`from` = `users`.`idu` WHERE `video_calls`.`to` = '%s' AND `video_calls`.`status` = 0", $db->real_escape_string($user['idu'])));

		$declineCall = $db->query(sprintf("UPDATE `video_calls` SET `status` = 3 WHERE `video_calls`.`id` = '%s' AND `video_calls`.`from` = '%s' AND `video_calls`.`to` = '%s'", $db->real_escape_string($_POST['call_id']), $db->real_escape_string($_POST['caller_id']), $db->real_escape_string($user['idu'])));
	} elseif($_POST['type'] == 0) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		
		// If the users are friends, start a conversation
		if($feed->verifyFriendship($user['idu'], $_POST['profile_id'])) {
			// Check if the targeted user doesn't have a pending call
			$incomingCalls = $db->query(sprintf("SELECT * FROM `video_calls` LEFT JOIN `users` ON `video_calls`.`from` = `users`.`idu` WHERE `video_calls`.`to` = '%s' AND `video_calls`.`status` = 0 AND `video_calls`.`time` > DATE_SUB(NOW(), INTERVAL %s SECOND) LIMIT 0,1", $db->real_escape_string($_POST['profile_id']), $pluginsSettings['video_call_dial_time']));
			
			$resultIncoming = $incomingCalls->fetch_assoc();

			// If there's no pending calls
			if(isset($resultIncoming['id']) == false) {
				$db->query(sprintf("INSERT INTO `video_calls` (`from`, `to`, `type`, `status`) VALUES ('%s', '%s', '%s', '%s')", $user['idu'], $db->real_escape_string($_POST['profile_id']), $db->real_escape_string($_POST['call_type']), 0));
				
				$selectCall = $db->query(sprintf("SELECT * FROM `video_calls` WHERE `from` = '%s' AND `to` = '%s' AND `type` = '%s' AND `status` = '%s' ORDER BY `id` DESC", $user['idu'], $db->real_escape_string($_POST['profile_id']), $db->real_escape_string($_POST['call_type']), 0));
				
				$result = $selectCall->fetch_assoc();
				
				echo json_encode(array('call_id' => $result['id']));
			} else {
				echo json_encode(array('error' => $LNG['plugin_video_call_user_busy']));
			}
		}
	} elseif($_POST['type'] == 1) {
		$selectCall = $db->query(sprintf("SELECT * FROM `video_calls` WHERE `id` = '%s'", $db->real_escape_string($_POST['profile_id'])));
		
		$result = $selectCall->fetch_assoc();
		
		// If the call is destinated to the logged-in user
		if($result['to'] == $user['idu'] && in_array($result['status'], array(0,1))) {
			$db->query(sprintf("UPDATE `video_calls` SET `status` = 1 WHERE `id` = '%s'", $result['id']));
			
			echo json_encode(array('call_id' => $result['id']));
		} else {
			echo json_encode(array('error' => $LNG['plugin_video_call_conversation_ended']));
		}
	} elseif($_POST['type'] == 2) {
		$selectCall = $db->query(sprintf("SELECT * FROM `video_calls` WHERE `id` = '%s'", $db->real_escape_string($_POST['call_id'])));
		
		$result = $selectCall->fetch_assoc();
		
		// If participant
		if($result['from'] == $user['idu'] || $result['to'] == $user['idu']) {
			$db->query(sprintf("UPDATE `video_calls` SET `status` = 2 WHERE `id` = '%s'", $result['id']));
		}
	}
}

mysqli_close($db);
?>