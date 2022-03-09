<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

// If message is not empty
if((!empty($_POST['message']) && $_POST['message'] !== ' ') && isset($_POST['id'])) {
	// If user is authed successfully
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->time = $settings['time'];
		$feed->id = $user['idu'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->message_length = $settings['message'];
        $feed->plugins = [];
		
		if(isset($_POST['type'])) {
			// Remove any extra white spaces, new lines
			$_POST['message'] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $_POST['message']);
			
			echo $feed->commentEdit($_POST['message'], $_POST['id']);
		} else {
			echo $feed->postEdit($_POST['message'], $_POST['id']);
		}
	}
}

mysqli_close($db);
?>