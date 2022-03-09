<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['page']) && isset($_POST['user']) || isset($_POST['live']) && isset($_POST['value']) || isset($_POST['type'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
    $feed->id = $user['idu'] ?? null;
	$feed->per_page = $settings['sperpage'];
	
	if(isset($_POST['type']) && $_POST['type'] == 1) {
		echo $feed->getPages($_POST['page'], $_POST['user']);
		return;
	} elseif(isset($_POST['type']) && $_POST['type'] == 2) {
		if($_POST['profile']) {
			$feed->profile_data = $feed->profileData(null, $_POST['profile']);
			
			// Check for permissions
			$friendship = $feed->verifyFriendship($feed->id, $feed->profile_data['idu']);
			if(!isset($_SESSION['is_admin'])) {
				if($feed->profile_data['private'] == 2 && $friendship['status'] !== '1' || $feed->profile_data['private'] == 1 || $feed->getBlocked($feed->profile_data['idu'], 2)) {
					return false;
				}
			}
			
			echo $feed->getPages($_POST['page'], null, $feed->profile_data['idu']);
		} else {
			$feed->per_page = $settings['uperpage'];
			echo $feed->getPages($_POST['page'], null);
		}
		return;
	} elseif(isset($_POST['live'])) {
		$feed->per_page = 4;
		
		if(empty($settings['pages'])) {
			echo '<div class="search-content"><div class="search-results"><div class="notification-inner"><strong>'.$LNG['view_all_results'].'</strong> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div><div class="message-inner">'.$LNG['no_results'].'</div>';
		} else {
			echo $feed->getPages(0, substr($_POST['value'], 1), 1);
		}
		
		return;
	} else {
		if(isset($user['username'])) {
			$feed->title = $settings['title'];
			$feed->username = $user['username'];
			$feed->id = $user['idu'];
			$feed->profile = $_POST['profile'];
			$feed->email = $CONF['email'];
			$feed->profile_data = $feed->profileData(null, $_POST['id']);
			$feed->email_page_invite = $settings['email_page_invite'];
			$feed->s_per_page = $settings['sperpage'];
	
			$feed->page_data = $feed->pageData(null, $_POST['page']);

			if(!$feed->page_data['id']) {
				return false;
			}
			
			$feed->invitePage(1, $_POST['user']);
		}
	}
}

mysqli_close($db);
?>