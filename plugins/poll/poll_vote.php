<?php
require_once(__DIR__ .'/../../includes/autoload.php');
if($_POST['token_id'] != $_SESSION['token_id']) {
	return false;
}
require_once(__DIR__ .'/functions.php');
require_once(__DIR__ .'/poll_output.php');

if(isset($_POST['id']) && isset($_POST['value'])) {
	if(isset($user['username'])) {
		$feed = new Poll();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
	
		$result = $feed->pollVote($_POST['id'], $_POST['value']);
		
		echo $result;
	}
}
mysqli_close($db);
?>