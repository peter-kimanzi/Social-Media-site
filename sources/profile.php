<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;		

	// If the $_GET user is empty, redirect to the home-page
	if(!isset($_GET['u']) || empty($_GET['u'])) {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	}
	
	// If the logged in user is the same with the viewed profile, display the Message Form
	if(isset($user['username']) && $user['username'] == $_GET['u']) {
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
		$TMPL['url'] = $CONF['url'];

		$top = $skin->make();
	} else {
		$top = '';
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
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->friends = $feed->getFriendsList();
	$feed->plugins = $plugins;
	
	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	if(isset($user['username'])) {
		$feed->updateStatus($user['offline']);
	}
	
	// If the $_GET user is empty, define default user as current logged in user, else redirect to home-page
	if($_GET['u'] == '') {
		$_GET['u'] = (!empty($feed->username) ? $feed->username : header("Location: ".$CONF['url']."/index.php?a=welcome"));
	}
	
	$feed->profile = $_GET['u'];
	$feed->profile_data = $feed->profileData($_GET['u']);
	$feed->s_per_page = 5;
	$feed->friendsArray = $feed->getFriends($feed->profile_data['idu']);
	$feed->friendsCount = $feed->countFriends($feed->profile_data['idu'], 1);
	$TMPL_old = $TMPL; $TMPL = array();
	$skin = new skin('shared/rows'); $rows = '';
	
	if(empty($_GET['filter'])) {
		$_GET['filter'] = '';
	}
	// Allowed types
	list($timeline, $message) = $feed->getProfile(0, $_GET['filter']);

	if(isset($_GET['r']) && $_GET['r'] == 'friends') {
		if($message !== 1) {
			$top = ''; // Hide the message form
			$feed->s_per_page = $settings['sperpage'];
			$feed->listFriends = $feed->getFriends($feed->profile_data['idu'], 0);
			$TMPL['messages'] = $feed->listFriends(0);
		} else {
			$TMPL['messages'] = $timeline;
		}
		$title = $LNG['friends'];
	} elseif(isset($_GET['r']) && $_GET['r'] == 'likes' && $settings['pages']) {
		if($message !== 1) {
			$top = ''; // Hide the message form
			$feed->per_page = $settings['sperpage'];
			$TMPL['messages'] = $feed->getPages(0, null, $feed->profile_data['idu']);
		} else {
			$TMPL['messages'] = $timeline;
		}
		$title = $LNG['likes'];
	}  elseif(isset($_GET['r']) && $_GET['r'] == 'groups' && $settings['groups']) {
		if($message !== 1) {
			$top = ''; // Hide the message form
			$feed->per_page = $settings['sperpage'];
			$TMPL['messages'] = $feed->getGroups(0, null, $feed->profile_data['idu']);
		} else {
			$TMPL['messages'] = $timeline;
		}
		$title = $LNG['groups'];
	} elseif(isset($_GET['r']) && $_GET['r'] == 'about') {
		if($message !== 1) {
			$top = ''; // Hide the message form
			$TMPL['messages'] = $feed->getAbout($feed->profile_data);
		} else {
			$TMPL['messages'] = $timeline;
		}
		$title = $LNG['about'];
	} else {
		$TMPL['messages'] = $timeline;
	}
	
	$rows = $skin->make();
	
	$skin = new skin('profile/sidebar'); $sidebar = '';
	// If the username doesn't exist
	if($message !== 1) {
		$TMPL['about'] = $feed->sidebarProfileInfo($feed->profileData($_GET['u']));
		// Load the sidebar plugins
		unset($TMPL['plugins']);
		$TMPL['plugins'] = '';
		foreach($plugins as $plugin) {
			if(array_intersect(array("3"), str_split($plugin['type']))) {
				$data = $feed->profile_data; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['salted']);
				$TMPL['plugins'] .= plugin($plugin['name'], $data, 2);
			}
		}
		
		// If the user has any messages posted and there's no filter selected
		if($timeline || $_GET['filter']) {
			$TMPL['events'] = $feed->sidebarTypes($_GET['filter']);
			$TMPL['dates'] = $feed->sidebarDates($_GET['filter'], null);
		}
		
		$TMPL['friends'] = $feed->sidebarFriends(0, 0);
		$TMPL['ad'] = generateAd($settings['ad4']);
	} else {
		$skin = new skin('profile/sidebar'); $sidebar = '';
		$TMPL['ad'] = generateAd($settings['ad4']);
	}
	$sidebar = $skin->make();
	
	$TMPL = $TMPL_old; unset($TMPL_old);
	$TMPL['top'] = $top;
	$TMPL['rows'] = $rows;
	$TMPL['sidebar'] = $sidebar;
	$TMPL['cover'] = $feed->fetchProfile($feed->profile_data);

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = (!empty($title) ? $title : $LNG['title_profile']).' - '.realName(htmlspecialchars($_GET['u']), $feed->profile_data['first_name'], $feed->profile_data['last_name'], 1).' - '.$settings['title'];

	// Load the sidebar plugins
	unset($TMPL['plugins']);
	foreach($plugins as $plugin) {
		if(array_intersect(array("6"), str_split($plugin['type']))) {
			$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['password']); unset($data['salted']);
			$TMPL['plugins'] .= plugin($plugin['name'], $data, 0);
		}
	}
	$skin = new skin('shared/timeline');
	return $skin->make();
}
?>