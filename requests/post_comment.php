<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

// Remove any extra white spaces, new lines
$_POST['comment'] = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $_POST['comment']);

if(!empty($_POST['id'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->email = $CONF['email'];
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->user_email = $user['email'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->max_size = $settings['sizemsg'];
		$feed->image_format = $settings['formatmsg'];
		$feed->time = $settings['time'];
		$feed->email_comment = $settings['email_comment'];
		$feed->email_mention = $settings['email_mention'];
		$feed->message_length = $settings['message'];
		
		$result = $feed->addComment($_POST['id'], $_POST['comment'], (isset($_POST['type']) ? $_POST['type'] : null), (isset($_POST['value']) ? $_POST['value'] : null));
		
		if($result[0] == 1) {
			$actions = $feed->getActions($_POST['id'], null, null, null, 1);
			echo json_encode(array('content' => $feed->getLastComment(), 'actions' => $actions));
		} else {
			echo json_encode(array('content' => (isset($result[1]) ? $result[1] : null)));
		}
	}
}

mysqli_close($db);
?>