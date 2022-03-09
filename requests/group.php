<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($_POST['type']) && isset($_POST['value']) && isset($_POST['group']) && isset($_POST['user'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
    $feed->id = $user['idu'] ?? null;
	$feed->title = $settings['title'];
	$feed->groups_limit = $settings['groups_limit'];

	if($_POST['type'] == '3') {
		$feed->per_page = $settings['sperpage'];
		echo $feed->getGroups($_POST['group'], $_POST['value'], null);
		return;
	}
	
	if($_POST['type'] == '4') {
		$feed->per_page = 4;
		if(empty($settings['groups'])) {
			echo '<div class="search-content"><div class="search-results"><div class="notification-inner"><strong>'.$LNG['view_all_results'].'</strong> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div><div class="message-inner">'.$LNG['no_results'].'</div>';
		} else {
			echo $feed->getGroups(0, substr($_POST['value'], 1), 1);
		}
		return;
	}
	
	if($_POST['type'] == '5') {
		if($_POST['user']) {
			$feed->per_page = $settings['sperpage'];
			$feed->profile_data = $feed->profileData(null, $_POST['user']);
			
			// Check for permissions
			$friendship = $feed->verifyFriendship($feed->id, $feed->profile_data['idu']);
			if(!isset($_SESSION['is_admin'])) {
				if($feed->profile_data['private'] == 2 && $friendship['status'] !== '1' || $feed->profile_data['private'] == 1 || $feed->getBlocked($feed->profile_data['idu'], 2)) {
					return false;
				}
			}
			
			echo $feed->getGroups($_POST['group'], 0, $feed->profile_data['idu']);
		} else {
			$feed->per_page = $settings['uperpage'];
			echo $feed->getGroups($_POST['group'], 0, null);
		}
		return;
	}
	
	// Get the group's data
	$feed->group_data = $feed->groupData(null, $_POST['group']);

	if(!$feed->group_data['id']) {
		return false;
	}
	
	if(isset($user['username'])) {
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		if(isset($_POST['profile'])) {
            $feed->profile = $_POST['profile'];
        }
		$feed->email = $CONF['email'];
		if(isset($_POST['id'])) {
            $feed->profile_data = $feed->profileData(null, $_POST['id']);
        }
		$feed->email_group_invite = $settings['email_group_invite'];
		$feed->s_per_page = $settings['sperpage'];

		$feed->group_member_data = $feed->groupMemberData($feed->group_data['id']);
		
		if($_POST['type'] == 6) {
			echo $feed->joinGroup(1);
			return false;
		}
		
		if($_POST['type'] == 7 && $feed->group_member_data['status']) {
			$feed->inviteGroup(1, $_POST['value']);
			return false;
		}
		
		if($_POST['type'] == 1) {
			echo $feed->listGroupMembers($_POST['value'], $_POST['user']);
			return false;
		}
		
		if(!$feed->groupPermission($feed->group_data, $feed->group_member_data, 0)) {
			return false;
		}
		
		if($_POST['type'] == 0) {
			if(in_array($feed->group_member_data['permissions'], array(1, 2))) {
				// If the user tries to promote to Admin or remove the Admin status and is not the group owner, return false
				if(in_array($_POST['value'], array(4, 5)) && $feed->group_member_data['permissions'] == '1') {
					return false;
				}
				
				if(in_array($_POST['value'], array(0, 2))) {
					// Temporarily set the $feed->id to the targeted user to get the group permission
					$feed->id = $_POST['user'];
					$userx = $feed->groupMemberData($feed->group_data['id']);
					
					// Restore the $feed->id
					$feed->id = $user['idu'];
					
					// If a group Admin tries to block/remove another group admin
					if($userx['permissions'] == '1' && $feed->group_member_data['permissions'] == 1) {
						return false;
					}
				}
				return $feed->groupMember($_POST['value'], $_POST['user']);
			}
		} elseif($_POST['type'] == 2) {
			if(in_array($feed->group_member_data['permissions'], array(1, 2))) {
				// Temporarily set the $feed->id to the targeted user to get the group permission
				$feed->id = $_POST['user'];
				$userx = $feed->groupMemberData($feed->group_data['id']);
				
				// If the targeted user is not an admin or the request is made by the group owner
				if(!$userx['permissions'] || $feed->group_member_data['permissions'] == '2') {
					// Before deleting the post check if the message was posted in the group
					$query = $db->query(sprintf("SELECT `id` FROM `messages` WHERE `group` = '%s' AND `id` = '%s'", $feed->group_data['id'], $db->real_escape_string($_POST['value'])));
					
					if($query->num_rows > 0) {
						// Delete the post
						$feed->delete($_POST['value'], 1);
					}
				}
			}
		}
	} else {
		// If the user is not logged in
		// If the request is for the "Members" or "Admins" tabs
		$feed->s_per_page = $settings['sperpage'];
		if($_POST['type'] == 1) {
			echo $feed->listGroupMembers($_POST['value'], $_POST['user']);
		}
	}
}

mysqli_close($db);
?>