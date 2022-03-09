<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['type']) && isset($_POST['start']) && isset($_POST['id'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	if(isset($user['username'])) {
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
	}
	$feed->per_page = $settings['perpage'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->profile = isset($_POST['profile']) ? $_POST['profile'] : null;
	$feed->profile_data = $feed->profileData(null, $_POST['id']);
	$feed->s_per_page = $settings['sperpage'];
	$feed->listFriends = $feed->getFriends($feed->profile_data['idu'], $_POST['start']);

	// Check for permissions
	$friendship = $feed->verifyFriendship($feed->id, $feed->profile_data['idu']);
	if(!isset($_SESSION['adminUsername']) && !isset($_SESSION['adminPassword'])) {
		if($feed->profile_data['private'] == 2 && $friendship['status'] !== '1' || $feed->profile_data['private'] == 1 || $feed->getBlocked($feed->profile_data['idu'], 2)) {
			return false;
		}
	}
	
	echo $feed->listFriends($_POST['type']);
}

mysqli_close($db);
?>