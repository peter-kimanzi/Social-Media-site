<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;
	
	if(isset($user['username'])) {
		// Start displaying the Feed
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->user = $user;
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->per_page = $settings['perpage'];
		$feed->time = $settings['time'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->c_per_page = $settings['cperpage'];
		$feed->c_start = 0;
		$feed->online_time = $settings['conline'];
		$feed->registration_date = $user['date'];
		$feed->s_per_page = 5;
		$feed->friendsArray = $feed->getFriends($user['idu']);
		$feed->friendsCount = $feed->countFriends($feed->id, 1);
		$feed->updateStatus($user['offline']);
		$feed->pages_limit = $settings['pages_limit'];
		$feed->groups_limit = $settings['groups_limit'];
		$feed->plugins = $plugins;
		$TMPL_old = $TMPL; $TMPL = array();
		$skin = new skin('shared/rows'); $rows = '';
		
		if(empty($_GET['filter'])) {
			$_GET['filter'] = '';
		}
		if(empty($_GET['tag'])) {
			$_GET['tag'] = '';
		}
		
		list($timeline, $message) = $feed->getFeed(0, $_GET['filter']);
		
		$TMPL['messages'] = $timeline;

		if(isset($_SESSION['message']) && $_SESSION['message'] == 'welcome') {
			$TMPL['messages'] .= $feed->showWelcome('welcome_feed');
			$_SESSION['message'] = '';
		}

		$rows = $skin->make();
		
		$skin = new skin('feed/sidebar'); $sidebar = '';
		
		$TMPL['editprofile'] = $feed->fetchProfileWidget($user['username'], realName($user['username'], $user['first_name'], $user['last_name']), $user['image']);
		// Load the sidebar plugins
        $TMPL['plugins'] = '';
		foreach($plugins as $plugin) {
			if(array_intersect(array("2"), str_split($plugin['type']))) {
				$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['password']); unset($data['salted']);
				$TMPL['plugins'] .= plugin($plugin['name'], $data, 2);
			}
		}
		if($settings['pages']) {
			$TMPL['pages'] = $feed->sidebarPages();
		}
		if($settings['groups']) {
			$TMPL['groups'] = $feed->sidebarGroups();
		}
		$TMPL['birthdays'] = $feed->sidebarBirthdays();
		$TMPL['friends'] = $feed->sidebarFriends(0, 0);
		$TMPL['friendsactivity'] = $feed->sidebarFriendsActivity(20, 1);
		if($feed->friendsCount <= 10) {
			$TMPL['suggestions'] = $feed->sidebarSuggestions($user['interests']);
		}
		$TMPL['ad'] = generateAd($settings['ad2']);
		
		$sidebar = $skin->make();
		
		$skin = new skin('shared/top'); $top = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		// Load the sidebar plugins
		unset($TMPL['plugins']);
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
		
		$TMPL = $TMPL_old; unset($TMPL_old);
		$TMPL['top'] = $top;
		$TMPL['rows'] = $rows;
		$TMPL['sidebar'] = $sidebar;
	} else {
		header('Location: '.permalink($CONF['url'].'/index.php?a=welcome'));
	}
	
	if(isset($_GET['logout'])) {
        $logout = new User;
        $logout->db = $db;
        $logout->username = $user['username'];
        $logout->logOut(true);
        // If the user is a moderator
        if($user['user_group'] == 1) {
            (new Admin())->logOut();
        }
        header('Location: '.permalink($CONF['url'].'/index.php?a=welcome'));
	}

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = $LNG['title_feed'].' - '.$settings['title'];

	// Load the Feed page plugins
	unset($TMPL['plugins']);
    $TMPL['plugins'] = '';
	foreach($plugins as $plugin) {
		if(array_intersect(array("5"), str_split($plugin['type']))) {
			$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['password']); unset($data['salted']);
			$TMPL['plugins'] .= plugin($plugin['name'], $data, 0);
		}
	}
	
	$skin = new skin('shared/timeline');
	return $skin->make();
}
?>