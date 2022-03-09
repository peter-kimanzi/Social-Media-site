<?php
function poll($values) {
	$value	= $values['value'];
	$type 	= $values['type'];
	
	// If there's no type set
	if(!$type) {
		global $db;
		
		$id = mt_rand();
		
		// Check the number of answers 2-10
		if(isset($_POST['poll-answer']) && count(array_filter($_POST['poll-answer'])) > 1 && count(array_filter($_POST['poll-answer'])) < 11) {
			foreach($_POST['poll-answer'] as $key => $val) {
				if(!empty($val)) {
					$db->query(sprintf("INSERT INTO `polls_answers` (`question`, `answer`) VALUES ('%s', '%s')", $db->real_escape_string($id), $db->real_escape_string(substr($val, 0, 64))));
				}
			}
			
			if($_POST['poll-stop'] > 0 && $_POST['poll-stop'] < 32) {
				$stop = $_POST['poll-stop'];
			} else {
				$stop = 1;
			}
			
			$db->query(sprintf("INSERT INTO `polls_durations` (`poll_id`, `poll_start`, `poll_stop`) VALUES ('%s', '%s', '%s')", $db->real_escape_string($id), time(), $stop));
			
			$array = array('id' => $id);
			return 'poll:'.json_encode($array);
		}
	}
}
?>