<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($user['username'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->username = $user['username'];
	$feed->id = $user['idu'];
	$feed->title = $settings['title'];
	$feed->email = $CONF['email'];
	$feed->m_per_page = $settings['mperpage'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->time = $settings['time'];
	$feed->online_time = $settings['conline'];
	$feed->updateStatus($user['offline']);
	$feed->plugins = loadPlugins($db);
	
	// Type 1: Check for new messages.
	if(isset($_POST['type']) && $_POST['type'] == 1) {
		echo $feed->checkChat($_POST['uid']);
	} elseif(isset($_POST['type']) && $_POST['type'] == 2) {
		$friends_chat = $friends_messages = array();
		$friends_chat = $feed->onlineUsers();
		if(!empty($_POST['friends'])) {
			$friends_messages = $feed->checkChat(explode(',', $_POST['friends']));
		}
		echo json_encode(array_merge($friends_chat, $friends_messages));
	} else {
		echo $feed->getChatMessages($_POST['uid'], (isset($_POST['cid']) ? $_POST['cid'] : null), (isset($_POST['start']) ? $_POST['start'] : null), null, $_POST['for']);
	}
}

mysqli_close($db);
?>