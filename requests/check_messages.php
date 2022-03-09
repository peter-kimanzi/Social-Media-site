<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}
if($_POST['last'] == 'undefined') return false;

if(!empty($_POST['last'])) {
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->user = $user;
	$feed->id = $user['idu'];
	$feed->username = $user['username'];
	$feed->per_page = $settings['perpage'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->profile = $_POST['profile'];
	$feed->profile_data = $feed->profileData($_POST['profile']);
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->max_size = $settings['sizemsg'];
	$feed->image_format = $settings['formatmsg'];
	$feed->message_length = $settings['message'];
	$feed->max_images = $settings['ilimit'];
	$feed->time = $settings['time'];
	$feed->plugins = loadPlugins($db);
	
	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	if($_POST['type'] == 2) {
		$feed->group_data = $feed->groupData(null, $_POST['filter']);
		$feed->group_member_data = $feed->groupMemberData($_POST['filter']);
		if(empty($feed->group_data['id'])) {
			return false;
		}
	} elseif($_POST['type'] == 3) {
        $feed->page_data = $feed->pageData(null, $_POST['filter']);
        if(empty($feed->page_data['id'])) {
            return false;
        }
    }
	
	echo $feed->checkNewMessages($_POST['last'], $_POST['filter'], $_POST['type']);
}
mysqli_close($db);
?>