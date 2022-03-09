<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(in_array($_POST['type'], array('0', '1', '2'))) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		$feed->plugins = loadPlugins($db);
		
		$result = $feed->delete($_POST['message'], $_POST['type']);
		
		if($result) {
			if($_POST['type'] == 0) {
				echo json_encode(array('content' => 1, 'actions' => $feed->getActions($_POST['parent'], null, null, null, 1)));
			} else {
				echo 1;
			}
		} else {
			echo 0;
		}
	}
}

mysqli_close($db);
?>