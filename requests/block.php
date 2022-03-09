<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['id']) && isset($_POST['type'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		
		echo $feed->getBlocked($_POST['id'], $_POST['type']);
	}
}
mysqli_close($db);
?>