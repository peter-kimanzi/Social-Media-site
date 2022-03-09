<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['start']) && isset($_POST['q']) && ctype_digit($_POST['start'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	
	if(isset($user['username'])) {
		$feed->user = $user;
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
	}
	
	$feed->per_page = $settings['perpage'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->s_per_page = $settings['sperpage'];
	$feed->plugins = loadPlugins($db);
	
	if(isset($_POST['live'])) {
		echo $feed->getHashtags(0, 10, str_replace('#', '', $_POST['q']), 1);
	} else {
		$hashtags = $feed->getHashtags($_POST['start'], $settings['sperpage'], $_POST['q'], null, $_POST['filter']);
		echo $hashtags[0];
	}
}

mysqli_close($db);
?>