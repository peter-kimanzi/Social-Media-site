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
		$feed->username = $user['username'];
		$feed->id = $user['idu'];

		echo json_encode(array('content' => $feed->postShared($_POST['id']), 'actions' => $feed->getActions($_POST['id'], null, null, null, 1)));
	}
}

mysqli_close($db);
?>