<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(!empty($_POST['start']) && isset($_POST['profile'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	
	if(isset($user['username'])) {
		$feed->user = $user;
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
	}
	
	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	$feed->per_page = $settings['perpage'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->profile = $_POST['profile'];
	$feed->profile_data = $feed->profileData($_POST['profile']);
	$feed->plugins = loadPlugins($db);
	
	if(empty($_POST['filter'])) {
		$_POST['filter'] = '';
	}
	
	$messages = $feed->getProfile($_POST['start'], $_POST['filter']);
	
	echo $messages[0];
}

mysqli_close($db);
?>