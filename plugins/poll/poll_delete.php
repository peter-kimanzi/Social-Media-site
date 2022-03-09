<?php
function poll_delete($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	
	// Check if the message is a poll and there's no type set
	if(substr($value, 0, 5) == 'poll:' && !$type) {
		global $db;
		$poll = json_decode(str_replace('poll:', '', $value), true);
		
		// Remove the poll options
		$db->query(sprintf("DELETE FROM `polls_answers` WHERE `question` = '%s'", $db->real_escape_string($poll['id'])));
		
		// Remove the poll durations
		$db->query(sprintf("DELETE FROM `polls_durations` WHERE `poll_id` = '%s'", $db->real_escape_string($poll['id'])));
		
		// Remove the poll results
		$db->query(sprintf("DELETE FROM `polls_results` WHERE `question` = '%s'", $db->real_escape_string($poll['id'])));
	}
}
?>