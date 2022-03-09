<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(!empty($_POST['start']) && isset($_POST['group'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	
	if(isset($user['username'])) {
		$feed->user = $user;
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
	}
	
	$feed->per_page = $settings['perpage'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->group_data = $feed->groupData(null, $_POST['group']);
	if(!$feed->group_data['id']) {
		return false;
	}
	$feed->group_member_data = $feed->groupMemberData($feed->group_data['id']);
	$feed->plugins = loadPlugins($db);
	
	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	$getGroup = $feed->getGroup($_POST['start'], $feed->group_data['id']);
	
	echo $getGroup[0];
}

mysqli_close($db);
?>