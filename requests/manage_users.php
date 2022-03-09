<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if($_SESSION['adminUsername']) {
	if(isset($_POST['start'])) {
		$manageUsers = new manageUsers();
		
		$manageUsers->db = $db;
		$manageUsers->url = $CONF['url'];
		$manageUsers->per_page = $settings['uperpage'];
		
		echo $manageUsers->getUsers($_POST['start'], $_POST['filter']);
	}
}

mysqli_close($db);
?>