<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['id'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	
	if(isset($user['username'])) {
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
	}
	
	if($_POST['page']) {
		$feed->page_data = $feed->pageData(null, $_POST['id']);
		echo $feed->profileCard($feed->page_data, 1);
	} else {
		$feed->profile_data = $feed->profileData(null, $_POST['id']);
		$feed->profile = $feed->profile_data['username'];
		$feed->friendsArray = $feed->getFriends($feed->profile_data['idu']);
		$feed->friendsCount = $feed->countFriends($feed->profile_data['idu'], 1);
		echo $feed->profileCard($feed->profileData(null, $_POST['id']), 0);
	}
}

mysqli_close($db);
?>