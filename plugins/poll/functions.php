<?php
class Poll extends Feed {
	function pollVote($id, $value) {
		
		// Get the message information
		$query = $this->db->query(sprintf("SELECT `id`,`idu`,`username`,`private`,`public`,`message`,`type`,`value` FROM `messages`, `users` WHERE `messages`.`value` = '%s' AND `messages`.`uid` = `users`.`idu`", $this->db->real_escape_string('poll:{"id":'.$id.'}')));
		$result = $query->fetch_assoc();
		
		// If the current user is not the owner of the message
		if($result['idu'] !== $this->id) {			
			$friendship = $this->verifyFriendship($this->id, $result['idu']);

			// Verify if the message
			if(!$result['public']) {
				$private = 1;
			} elseif($result['public'] == 2 && $friendship['status'] !== '1') {
				$private = 1;
			}
		}
		
		// If the message can be voted
		if(isset($private) == false) {
			$poll = json_decode(str_replace('poll:', '', $result['value']), true);
			
			$duration_query = $this->db->query(sprintf("SELECT * FROM `polls_durations` WHERE `poll_id` = '%s'", $this->db->real_escape_string($id)));
			$duration_result = $duration_query->fetch_assoc();
		
			$start = date("Y-m-d H:i:s", $duration_result['poll_start']);
			$stop = date("Y-m-d H:i:s", strtotime('+'.$duration_result['poll_stop'].' days', strtotime($start)));
			
			// If the Poll has expired
			if(date("Y-m-d H:i:s") > $stop) {
				return false;
			} else {
				// Check if a vote already exists
				$check = $this->db->query(sprintf("SELECT * FROM `polls_results` WHERE `question` = '%s' AND `by` = '%s'", $this->db->real_escape_string($id), $this->id));
				if(!$check->num_rows) {
					// Add the vote
					$this->db->query(sprintf("INSERT INTO `polls_results` (`question`, `answer`, `by`) VALUES ('%s', '%s', '%s')", $this->db->real_escape_string($id), $this->db->real_escape_string($value), $this->id));
				}
				return poll_output(array('message' => $result['message'], 'id' => $result['id'], 'type' => $result['type'], 'value' => $result['value'], 'user_id' => $this->id));
			}
		}
	}
}
?>