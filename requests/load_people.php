<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['start']) && isset($_POST['q']) && ctype_digit($_POST['start'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	
	if(isset($user['username'])) {
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		$feed->online_time = $settings['conline'];
		
		if(!empty($_POST['list'])) {
			echo $feed->onlineUsers(2, $_POST['q'], $_POST['type']);
			return;
		}
	}
	
	$feed->per_page = $settings['perpage'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
    if(isset($_POST['profile'])) {
        $feed->profile = $_POST['profile'];
    }
	if(isset($_POST['id'])) {
        $feed->profile_data = $feed->profileData(null, $_POST['id']);
    }
	$feed->s_per_page = $settings['sperpage'];
	$feed->subsList = $feed->getFriends($feed->profile_data['idu'] ?? null, $_POST['start']);

	if(isset($_POST['live'])) {
		echo $feed->getSearch(0, 5, $_POST['q'], null, null, 1);
	} else {
		echo $feed->getSearch($_POST['start'], $settings['sperpage'], $_POST['q'], (isset($_POST['filter']) ? $_POST['filter'] : null), (isset($_POST['age']) ? $_POST['age'] : null));
	}
}

mysqli_close($db);
?>