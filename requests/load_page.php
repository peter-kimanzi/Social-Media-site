<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(!empty($_POST['start'])) {
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
	$feed->time = $settings['time'];
	$feed->page_data = $feed->pageData(null, $_POST['page']);
	
	if(empty($feed->page_data['id'])) {
		return false;
	}
	
	$feed->plugins = loadPlugins($db);
	if(empty($_POST['filter'])) {
		$_POST['filter'] = '';
	}
	
	$getPage = $feed->getPage($_POST['start'], $_POST['page']);
	echo $getPage[0];
}

mysqli_close($db);
?>