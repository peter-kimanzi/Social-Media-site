<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;
	
	if(empty($user['username'])) {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	} else {
		// Start displaying the Feed
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->username = $user['username'];
		$feed->id = $user['idu'];
		$feed->per_page = $settings['perpage'];
		$feed->time = $settings['time'];
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$TMPL['uid'] = $user['idu'];
		
		$TMPL_old = $TMPL; $TMPL = array();
		$skin = new skin('shared/rows'); $rows = '';
		
		if(empty($_GET['filter'])) {
			$_GET['filter'] = '';
		}
		// Allowed types
		if($_GET['filter'] == 'likes') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, 1, null, null, null, null, null, null, null, null, null);
		} elseif($_GET['filter'] == 'comments') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, 1, null, null, null, null, null, null, null, null);
		} elseif($_GET['filter'] == 'shared') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, 1, null, null, null, null, null, null, null);
		} elseif($_GET['filter'] == 'friendships') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, 1, null, null, null, null, null, null);
		} elseif($_GET['filter'] == 'chats') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, 1, null, null, null, null, null);
		} elseif($_GET['filter'] == 'birthdays') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, null, 1, null, null, null, null);
		} elseif($_GET['filter'] == 'groups' && $settings['groups']) {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, null, null, 1, null, null, null);
		} elseif($_GET['filter'] == 'pokes') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, null, null, null, 1, null, null);
		} elseif($_GET['filter'] == 'pages' && $settings['pages']) {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, null, null, null, null, 1, null);
		} elseif($_GET['filter'] == 'mentions') {
			$x = $feed->checkNewNotifications($settings['nperpage'], 2, 2, null, null, null, null, null, null, null, null, null, null, 1);
		} else {
			$x = $feed->checkNewNotifications(20, 2, 2, null, 1, 1, 1, 1, 1, 1, ($settings['groups'] ? 1 : 0), 1, ($settings['pages'] ? 1 : 0), 1);
		}
		$TMPL['messages'] = '<div class="page-container" id="notifications-page">'.$x.'</div>';
		
		$rows = $skin->make();
		
		$skin = new skin('feed/sidebar'); $sidebar = '';
		
		$TMPL['events'] = $feed->sidebarNotifications($_GET['filter'], $settings);
		$TMPL['ad3'] = generateAd($settings['ad3']);
		
		$sidebar = $skin->make();
		
		$TMPL = $TMPL_old; unset($TMPL_old);
		$TMPL['rows'] = $rows;
		$TMPL['sidebar'] = $sidebar;
	}

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = $LNG['title_notifications'].' - '.$settings['title'];

	$skin = new skin('shared/timeline');
	return $skin->make();
}
?>