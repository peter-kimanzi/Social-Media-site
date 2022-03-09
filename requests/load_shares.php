<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

$feed = new feed();
$feed->db = $db;
$feed->url = $CONF['url'];

if(isset($user['username'])) {
	$feed->user = $user;
	$feed->username = $user['username'];
	$feed->id = $user['idu'];
}

$feed->per_page = $settings['perpage'];
$feed->censor = $settings['censor'];
$feed->smiles = $settings['smiles'];
$feed->per_page = $settings['sperpage'];
$feed->time = $settings['time'];
$feed->c_start = 0;
$feed->profile = (isset($_POST['profile']) ? $_POST['profile'] : null);
$feed->profile_data = $feed->profileData((isset($_POST['profile']) ? $_POST['profile'] : null));

$result = $feed->getShares((isset($_POST['start']) && ctype_digit($_POST['start']) ? $_POST['start'] : 0), $_POST['id']);

echo $result;

mysqli_close($db);
?>