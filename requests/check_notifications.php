<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

if(isset($user['username'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->username = $user['username'];
	$feed->id = $user['idu'];
	$feed->time = $settings['time'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->per_page = $settings['perpage'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->online_time = $settings['conline'];
	$feed->c_start = 0;
	
	if(isset($_POST['for']) && $_POST['for'] == 1) {
		echo $feed->checkNewNotifications($settings['nperwidget'], (isset($_POST['type']) ? $_POST['type'] : null), $_POST['for'], null, $user['notificationl'], $user['notificationc'], $user['notifications'], $user['notificationf'], $user['notificationd'], null, ($settings['groups'] ? $user['notificationg'] : 0), $user['notificationp'], ($settings['pages'] ? $user['notificationx'] : 0), $user['notificationm']);
	} elseif(isset($_POST['for']) && $_POST['for'] == 2) {
		echo $feed->checkNewNotifications($settings['nperwidget'], (isset($_POST['type']) ? $_POST['type'] : null), $_POST['for'], null, $user['notificationl'], $user['notificationc'], $user['notifications'], $user['notificationf'], $user['notificationd'], null, ($settings['groups'] ? $user['notificationg'] : 0), $user['notificationp'], ($settings['pages'] ? $user['notificationx'] : 0), $user['notificationm']);
	} else {
		echo $feed->checkNewNotifications($settings['nperwidget'], (isset($_POST['type']) ? $_POST['type'] : null), (isset($_POST['for']) ? $_POST['for'] : null), null, $user['notificationl'], $user['notificationc'], $user['notifications'], $user['notificationf'], $user['notificationd'], null, ($settings['groups'] ? $user['notificationg'] : 0), $user['notificationp'], ($settings['pages'] ? $user['notificationx'] : 0), $user['notificationm']);
	}
}

mysqli_close($db);
?>