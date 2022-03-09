<?php

// Attempt to set a custom default timezone
if($settings['time'] == 0 && $settings['timezone']) {
	date_default_timezone_set($settings['timezone']);
}

// Store the theme path and theme name into the CONF and TMPL
$TMPL['theme_path'] = $CONF['theme_path'];
$TMPL['theme_name'] = $CONF['theme_name'] = $settings['theme'];
$TMPL['theme_url'] = $CONF['theme_url'] = $CONF['theme_path'].'/'.$CONF['theme_name'];

$loggedIn = new User();
$loggedIn->db = $db;
$loggedIn->url = $CONF['url'];
$user = $loggedIn->auth();

// If the user is a moderator
if(isset($user['user_group']) && $user['user_group'] == 1) {
	// If the user is not already authenticated as the site owner
	// if(isset($_SESSION['is_admin']) == false) {
		$_SESSION['adminUsername'] = $user['username'];
		$_SESSION['adminPassword'] = $user['password'];
		$_SESSION['is_admin'] = true;
	// }
}

// If the user is suspended
if(isset($user['suspended']) && $user['suspended'] == 1) {
	$loggedIn->logOut();
}