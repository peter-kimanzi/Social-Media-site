<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(!empty($_POST['start'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->user = $user;
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->per_page = $settings['perpage'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->c_per_page = $settings['cperpage'];
		$feed->c_start = 0;
		$feed->time = $settings['time'];
		$feed->plugins = loadPlugins($db);
		
		if(empty($_POST['filter'])) {
			$_POST['filter'] = '';
		}
		
		$getFeed = $feed->getFeed($_POST['start'], $_POST['filter']);
		echo $getFeed[0];
	}
}

mysqli_close($db);
?>