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
		$feed->c_per_page = $settings['cperpage'];
		$feed->c_start = 0;
		$feed->m_per_page = $settings['mperpage'];
		$feed->online_time = $settings['conline'];
		$feed->sound_new_chat = $user['sound_new_chat'];
		$feed->updateStatus($user['offline']);
		$feed->plugins = $plugins;
		$TMPL['uid'] = $user['idu'];
		$TMPL['fid'] = htmlspecialchars($_GET['id']);
		
		// Seconds to microseconds
		$TMPL['chatr'] = ($settings['chatr'] * 1000);
		
		$TMPL_old = $TMPL; $TMPL = array();
		$skin = new skin('messages/rows'); $rows = '';
		
		if(empty($_GET['filter'])) {
			$_GET['filter'] = '';
		}
		// Allowed types
		$TMPL['messages'] = $feed->getChat($_GET['id'], $feed->profileData($_GET['u']));
		
		$rows = $skin->make();
		
		$skin = new skin('messages/sidebar'); $sidebar = '';
		
		$TMPL['users'] = $feed->onlineUsers(1, $_GET['u']);
		
		$sidebar = $skin->make();
		
		$TMPL = $TMPL_old; unset($TMPL_old);
		$TMPL['rows'] = $rows;
		$TMPL['sidebar'] = $sidebar;
	}

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = $LNG['title_messages'].' - '.$settings['title'];

	$skin = new skin('messages/content');
	return $skin->make();
}
?>