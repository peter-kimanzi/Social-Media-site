<?php
require_once(__DIR__ .'/../../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}
require_once(__DIR__ .'/functions.php');

if(isset($_POST['id'])) {
	if(isset($user['username'])) {
		$feed = new Dislike();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->id = $user['idu'];
		$feed->username = $user['username'];

		$result = $feed->getDislikes($_POST['id']);
		echo $result;
	}
}
mysqli_close($db);
?>