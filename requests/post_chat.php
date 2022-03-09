<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

// Remove any extra white spaces, new lines
if(isset($_POST['message'])) {
    $_POST['message'] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $_POST['message']);
} else {
    $_POST['message'] = '';
}

// If message is not empty
if(!empty($_POST['id'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->time = $settings['time'];
		$feed->id = $user['idu'];
		$feed->chat_length = $settings['message'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->max_size = $settings['sizemsg'];
		$feed->image_format = $settings['formatmsg'];
		$feed->message_length = $settings['message'];
		$feed->max_images = $settings['ilimit'];
		$feed->plugins = loadPlugins($db);

		if(!empty($_POST['message']) && $_POST['message'] !== ' ' && isset($_POST['type']) == false) {
			echo $feed->postChat($_POST['message'], $_POST['id']);
		} elseif(isset($_POST['type'])) {
			echo $feed->postChat($_POST['message'], $_POST['id'], $_POST['type'], (isset($_POST['value']) ? $_POST['value'] : null));
		}
	}
}

mysqli_close($db);
?>