<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if($_POST['value'] == 0 || $_POST['value'] == 1 || $_POST['value'] == 2) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		
		$result = $feed->changePrivacy($_POST['message'], $_POST['value']);
		echo $result;
	}
}

mysqli_close($db);
?>