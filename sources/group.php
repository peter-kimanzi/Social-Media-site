<?php
if(empty($settings['groups'])) {
	header("Location: ".$CONF['url']."/index.php?a=welcome");
}
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;

	if(isset($_GET['name']) && empty($_GET['name']) || !isset($_GET['name']) && !$user['username']) {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	}
	
	
	// Start displaying the Feed
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->user = $user;
	$feed->id = $user['idu'] ?? null;
	$feed->username = $user['username'] ?? null;
	$feed->per_page = $settings['perpage'];
	$feed->time = $settings['time'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->max_size = $settings['size'];
	$feed->image_format = $settings['format'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->online_time = $settings['conline'];
	$feed->groups_limit = $settings['groups_limit'];
	$feed->updateStatus($user['offline'] ?? null);
	$feed->group_data = $feed->groupData(isset($_GET['name']) ? $_GET['name'] : null);
	$feed->group_member_data = $feed->groupMemberData($feed->group_data['id'] ?? null);
	$feed->plugins = $plugins;

	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	$TMPL_old = $TMPL; $TMPL = array();
	$TMPL['url'] = $CONF['url'];
	// If the user is logged in and is allowed to post in the group
	if(isset($user['username']) && $user['username'] && isset($_GET['name']) && empty($_GET['friends']) && empty($_GET['search']) && empty($_GET['r']) && $feed->groupPermission($feed->group_data, $feed->group_member_data, 1)) {
		$skin = new skin('shared/top'); $top = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		// Load the sidebar plugins
        $TMPL['plugins'] = '';
		foreach($plugins as $plugin) {
			if(array_intersect(array("e"), str_split($plugin['type']))) {
				$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['password']); unset($data['salted']);
				$TMPL['plugins'] .= plugin($plugin['name'], $data, 3);
			}
		}
	
		$TMPL['theme_url'] = $CONF['theme_url'];
		$TMPL['private_message'] = $user['privacy'];
		$TMPL['privacy_class'] = (($user['privacy']) ? (($user['privacy'] == 2) ? 'friends' : 'public') : 'private');
		$TMPL['avatar'] = permalink($CONF['url'].'/image.php?t=a&w=48&h=48&src='.$user['image']);
		$TMPL['group'] = $feed->group_data['id'];
		$TMPL['style'] = ' style="display: none;"';

		$top = $skin->make();
	} else {
		$top = '';
	}
	
	if(isset($_GET['r']) && $_GET['r'] == 'edit' && $feed->group_member_data['permissions'] == 2) {
		$skin = new skin('group/edit'); $rows = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		// The Group Title
		$TMPL['group_title'] = $LNG['edit_group'];
		
		// The Group button
		$TMPL['group_btn'] = $LNG['save_changes'];
		
		// The URL to append for the form
		$TMPL['edit_url'] = permalink($CONF['url'].'/index.php?a=group&name='.$feed->group_data['name'].'&r=edit');
		$TMPL['delete_url'] = permalink($CONF['url'].'/index.php?a=group&name='.$feed->group_data['name'].'&r=delete');
		
		if(!empty($_POST)) {
			$message = $feed->createGroup($_POST, 1);
			
			$feed->group_data = $feed->groupData($_GET['name']);
			
			// If there's an error during group validation
			if($message[0]) {
				$TMPL['message'] = notificationBox('error', $message[1]);
			} else {
				if($message[1]) {
					$TMPL['message'] = notificationBox('success', $LNG['settings_saved']);
				} else {
					$TMPL['message'] = notificationBox('info', $LNG['nothing_changed']);
				}
			}
		}
		
		// The disabled attribute for inputs
		$TMPL['disabled'] = ' disabled';
		$TMPL['current_name'] = $feed->group_data['name'];
		$TMPL['current_title'] = $feed->group_data['title'];
		$TMPL['current_desc'] = $feed->group_data['description'];
		$TMPL['current_invite_code'] = $feed->group_data['invitecode'];
		if($feed->group_data['privacy'] == 1) {
			$TMPL['privacy_private'] = ' selected="selected"';
		} else {
			$TMPL['privacy_public'] = ' selected="selected"';
		}
		if($feed->group_data['posts'] == 1) {
			$TMPL['posts_admins'] = ' selected="selected"';
		} else {
			$TMPL['posts_members'] = ' selected="selected"';
		}
	} elseif(isset($_GET['r']) && $_GET['r'] == 'delete' && $feed->group_member_data['permissions'] == 2) {
		$skin = new skin('group/delete'); $delete = '';
		$TMPL['token_id'] = $_SESSION['token_id'];
		$TMPL['id'] = $feed->group_data['id'];
		$delete = $skin->make();
	} elseif(isset($_GET['name'])) {
		$skin = new skin('shared/rows'); $rows = '';
		
		$feed->s_per_page = $settings['sperpage'];
		if(isset($_GET['r']) && $_GET['r'] == 'members') {
			$TMPL['messages'] = $feed->listGroupMembers(0, 0);
		} elseif(isset($_GET['r']) && $_GET['r'] == 'admins') {
			$TMPL['messages'] = $feed->listGroupMembers(1, 0);
		} elseif(isset($_GET['r']) && $_GET['r'] == 'requests' && in_array($feed->group_member_data['permissions'], array(1, 2)) && $feed->group_member_data['status']) {
			$TMPL['messages'] = $feed->listGroupMembers(2, 0);
		} elseif(isset($_GET['r']) && $_GET['r'] == 'blocked' && in_array($feed->group_member_data['permissions'], array(1, 2)) && $feed->group_member_data['status']) {
			$TMPL['messages'] = $feed->listGroupMembers(3, 0);
		} elseif(!empty($_GET['friends']) && $feed->group_member_data['status']) {
			$TMPL['messages'] = $feed->searchFriends($_GET['friends']);
		} elseif(!empty($_GET['search']) && $feed->id && ($feed->group_member_data['status'] || $feed->group_data['privacy'] == 0)) {
			$TMPL['messages'] = $feed->listGroupMembers(4, $_GET['search']);
		} else {
			// Get the group's feed
			list($timeline, $message) = $feed->getGroup(0, $feed->group_data['id']);
			$TMPL['messages'] = $timeline;
		}
	} else {
		$skin = new skin('group/edit'); $rows = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		$TMPL['edit_url'] = permalink($CONF['url'].'/index.php?a=group');
		$TMPL['group_title'] = $TMPL['group_btn'] = $LNG['create_group'];
		$TMPL['style'] = ' style="display: none;"';
		if(!empty($_GET['deleted'])) {
			$TMPL['message'] = notificationBox('success', sprintf($LNG['group_deleted'], $_GET['deleted']));
		}
		if(!empty($_GET['delete'])) {
			$feed->deleteGroup($_GET['delete']);
		}
		if(!empty($_POST)) {
			$message = $feed->createGroup($_POST);
			// Display the current inputs
			$TMPL['current_name'] = htmlspecialchars($_POST['group_name']);
			$TMPL['current_title'] = htmlspecialchars($_POST['group_title']);
			$TMPL['current_desc'] = htmlspecialchars($_POST['group_desc']);
			$TMPL['current_invite_code'] = htmlspecialchars($_POST['group_invite_code']);
			if($_POST['group_privacy'] == 1) {
				$TMPL['privacy_private'] = ' selected="selected"';
			} else {
				$TMPL['privacy_public'] = ' selected="selected"';
			}
			if($_POST['group_posts'] == 1) {
				$TMPL['posts_admins'] = ' selected="selected"';
			} else {
				$TMPL['posts_members'] = ' selected="selected"';
			}
			// If there's an error during group validation
			if($message[0]) {
				$TMPL['message'] = notificationBox('error', $message[1]);
			} else {
				header("Location: ".permalink($CONF['url']."/index.php?a=group&name=".$message[1]));
			}
		}
	}

	$rows = $skin->make();
	
	$skin = new skin('group/sidebar'); $sidebar = '';
	if(isset($_GET['name'])) {
		// If the group exists
		if($feed->group_data['name']) {
			$TMPL['about'] = $feed->sidebarGroupInfo($feed->group_data);
			// If the user is a group member
			if(isset($feed->group_member_data['status']) && $feed->group_member_data['status'] == 1) {
				$TMPL['invite'] = $feed->sidebarInput(0);
			}
			// If the user is logge in and is a member of the group or if the group is public and the user is logged in
			if($feed->id && (isset($feed->group_member_data['status']) && $feed->group_member_data['status'] == 1 || $feed->group_data['privacy'] == 0)) {
				$TMPL['search'] = $feed->sidebarInput(1);
			}
		}
	} else {
		$TMPL['groups'] = $feed->sidebarGroups(1);
	}
	
	$TMPL['ad'] = generateAd($settings['ad3']);
	
	$sidebar = $skin->make();
	
	$TMPL = $TMPL_old; unset($TMPL_old);
	$TMPL['top'] = $top;
	$TMPL['rows'] = $rows;
	$TMPL['sidebar'] = $sidebar;
    if(isset($delete)) {
        $TMPL['delete'] = $delete;
    }
    if(isset($_GET['name'])) {
        $TMPL['cover'] = $feed->fetchGroup($feed->group_data);
    }

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = (isset($_GET['name']) ? $LNG['group'].' - '.($feed->group_data['title'] ? $feed->group_data['title'] : htmlspecialchars($_GET['name'])) : $LNG['title_group']).' - '.$settings['title'];

	$skin = new skin((isset($_GET['name']) ? 'shared/timeline' : 'group/content'));
	return $skin->make();
}
?>