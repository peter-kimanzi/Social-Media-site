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
	$feed->per_page = $settings['perpage'];
	$feed->time = $settings['time'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	
	// Allowed types
	if($_POST['filter'] == 'likes') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], 1, null, null, null, null, null, null, null, null, null);
	} elseif($_POST['filter'] == 'comments') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, 1, null, null, null, null, null, null, null, null);
	} elseif($_POST['filter'] == 'shared') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, 1, null, null, null, null, null, null, null);
	} elseif($_POST['filter'] == 'friendships') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, 1, null, null, null, null, null, null);
	} elseif($_POST['filter'] == 'chats') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, 1, null, null, null, null, null);
	} elseif($_POST['filter'] == 'birthdays') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, null, 1, null, null, null, null);
	} elseif($_POST['filter'] == 'groups') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, null, null, 1, null, null, null);
	} elseif($_POST['filter'] == 'pokes') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, null, null, null, 1, null, null);
	} elseif($_POST['filter'] == 'pages') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, null, null, null, null, 1, null);
	} elseif($_POST['filter'] == 'mentions') {
		$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, $_POST['start'], null, null, null, null, null, null, null, null, null, 1);
	}
	echo $x;
}

mysqli_close($db);
?>