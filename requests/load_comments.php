<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(!empty($_POST['id']) && !empty($_POST['start']) && !empty($_POST['cid'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->id = $user['idu'] ?? null;
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->time = $settings['time'];

	if(isset($user['username'])) {
		$feed->username = $user['username'];
	}
	
	if($_POST['start'] == 50) {
		$feed->c_per_page = 50;
	} else {
		$feed->c_per_page = $settings['cperpage'];
	}
	
	$message = $feed->getMessageOwner($_POST['id']);
	
	echo $feed->getComments($_POST['id'], $_POST['cid'], $_POST['start'], ($feed->id == $message['uid'] ? 1 : 0));
}

mysqli_close($db);
?>