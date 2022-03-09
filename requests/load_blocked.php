<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['start'])) {
	if($user['idu']) {
		// Create the class instance
		$updateUserSettings = new updateUserSettings();
		$updateUserSettings->db = $db;
		$updateUserSettings->url = $CONF['url'];
		$updateUserSettings->id = $user['idu'];
		
		$updateUserSettings->per_page = $settings['perpage'];
		
		echo $updateUserSettings->getBlockedUsers($_POST['start']);
	}
}

mysqli_close($db);
?>