<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;
	
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
	if(isset($_GET['type']) && $_GET['type'] == 'likes') {
		$feed->c_per_page = 0; // Hide all the commments
	} else {
		$feed->c_per_page = 50; // Show n of comments
	}
	$feed->c_start = 0;
	$feed->plugins = $plugins;
	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	$feed->is_post_page = 1;
	
	$TMPL_old = $TMPL; $TMPL = array();
	$skin = new skin('post/rows'); $rows = '';
	
	if(empty($_GET['filter'])) {
		$_GET['filter'] = '';
	}
	// If the message id is not set, or it doesn't consist from digits
	if(!isset($_GET['m']) || !ctype_digit($_GET['m'])) {
		header("Location: ".$CONF['url']);
	}

	$message = $feed->getMessage($_GET['m']);
	$TMPL['messages'] = $message[0];
	
	// Match the content from the message-message class in order to set it for the title tag
	preg_match_all('/<div.*(class="message-message").*>([\d\D]*)<\/div>/iU', $message[0], $title);
	if(empty($title[2][0])) {
		preg_match_all('/<div.*(class="message-header").*>([\d\D]*)<\/div>/iU', $message[0], $title);
		if($title[2]) {
			$private = 1;
		}
	}
	
	// If the output is empty redirect to home-page
	if(!$title[2]) {
		header("Location: ".$CONF['url']);
	}
	
	$rows = $skin->make();
	
	$skin = new skin('post/sidebar'); $sidebar = '';
	
	$TMPL['ad'] = generateAd($settings['ad5']);

	$sidebar = $skin->make();
	
	$TMPL = $TMPL_old; unset($TMPL_old);
	$TMPL['rows'] = $rows;
	$TMPL['sidebar'] = $sidebar;

	$TMPL['url'] = $CONF['url'];

	$TMPL['title'] = $LNG['title_post'].' - '.((isset($_GET['type']) && $_GET['type'] == 'likes') ? $LNG["{$_GET['type']}"].' - ' : '').substr(strip_tags($title[2][0]), 0, 40).((isset($private) == false) ? '...' : '').' - '.$settings['title'];
	$skin = new skin('shared/timeline');
	return $skin->make();
}
?>