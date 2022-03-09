<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['id'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->email = $CONF['email'];
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->user_email = $user['email'];
		$feed->email_like = $settings['email_like'];
		$feed->time = $settings['time'];
		
		if($_POST['type'] == 2) {
			$feed->page_data = $feed->pageData(null, $_POST['id']);
			if(empty($feed->page_data['id'])) {
				return false;
			}
			$result = $feed->likePage(1);
			
			// Update the notifications after liking the page if the page owner (prevents showing [1] notification when self-liking a page)
			if($feed->id == $feed->page_data['by']) {
				// Update the notifications
				$feed->pageActivity(1, $feed->page_data);
			}
		} else {
			$result = $feed->like($_POST['id'], $_POST['type']);
		}
		
		echo $result;
	}
}

mysqli_close($db);
?>