<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if($_SESSION['adminUsername']) {
	$manageReports = new manageReports();
	$manageReports->db = $db;
	$manageReports->url = $CONF['url'];
	$manageReports->per_page = $settings['uperpage'];
	$manageReports->plugins = loadPlugins($db);
	
	if(isset($_POST['start'])) {
		echo $manageReports->getReports($_POST['start']);
	} elseif(isset($_POST['kind'])) {
		echo $manageReports->manageReport($_POST['id'], $_POST['type'], $_POST['post'], $_POST['kind']);
	}
}

mysqli_close($db);
?>