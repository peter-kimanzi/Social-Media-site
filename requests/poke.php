<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['id'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->email = $CONF['email'];
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->user_email = $user['email'];
		$feed->profile_data = $feed->profileData(null, $_POST['id']);
		
		if(!$feed->getBlocked($_POST['id'], 2) && !empty($feed->profile_data)) {
			$result = $feed->poke($_POST['id'], 1);
			echo $result;
		}
	}
}

mysqli_close($db);
?>