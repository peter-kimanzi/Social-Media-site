<?php
require_once(__DIR__ .'/functions.php');

function dislike_output($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	global $CONF, $db, $settings, $LNG;
	
	$query = $db->query(sprintf("SELECT COUNT(`id`) as `count` FROM `dislikes` WHERE `post` = '%s'", $db->real_escape_string($id)));
	$result = $query->fetch_assoc();

	$counter = $result['count'];
	
	// Verify the dislike state
	$dislike = verifyDislike($db, $id, $values['user_id']);
	
	if($dislike) {
		$state = $LNG['plugin_dislike_disliked'];
	} else {
		$state = $LNG['plugin_dislike_dislike'];
	}
	
	if($values['user_id'] == NULL) {
		$url = '<a href="'.$CONF['url'].'/" rel="loadpage">'.$state.'</a>';
	} else {
		$url = '<a onclick="doDislike('.$id.', 1)" id="doDislike'.$id.'">'.$state.'</a>';
	}
	
	// Output the social buttons
	$output = '<div class="message-actions"><div class="message-actions-content" id="message-action-dislike'.$id.'">'.$url.' <div class="dislike_container"><div class="dislike_btn"></div> '.$counter.'</div></div></div>';
	return $output;
}
?>