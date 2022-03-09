<?php
class Dislike extends Feed {
	function getDislikes($id) {
		global $LNG;
		
		// Get the message information
		$query = $this->db->query(sprintf("SELECT `idu`,`username`,`private`,`public` FROM `messages`, `users` WHERE `messages`.`id` = '%s' AND `messages`.`uid` = `users`.`idu`", $this->db->real_escape_string($id)));
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
		
		// If the message can be disliked
		if(isset($private) == null) {
			$dislike = verifyDislike($this->db, $id, $this->id);
			
			if($dislike) {
				// Remove the dislike
				$this->db->query(sprintf("DELETE FROM `dislikes` WHERE `post` = '%s' AND `by` = '%s'", $this->db->real_escape_string($id), $this->id));
				$state = $LNG['plugin_dislike_dislike'];
			} else {
				// Add the Dislike
				$this->db->query(sprintf("INSERT INTO `dislikes` (`post`, `by`) VALUES ('%s', '%s')", $this->db->real_escape_string($id), $this->id));
				$state = $LNG['plugin_dislike_disliked'];
			}
			
			// Get the dislikes
			$query = $this->db->query(sprintf("SELECT COUNT(`id`) as `count` FROM `dislikes` WHERE `dislikes`.`post` = '%s'", $this->db->real_escape_string($id)));
			$result = $query->fetch_assoc();

			// Output the social buttons
			$output = '<a onclick="doDislike('.$id.')" id="doDislike'.$id.'">'.$state.'</a> <div class="dislike_container" id="dislike_btn'.$id.'"><div class="dislike_btn"></div> '.$result['count'].'</div>';
			return $output;
		}
	}
}
function verifyDislike($db, $id, $uid) {
	$result = $db->query(sprintf("SELECT * FROM `dislikes` WHERE `post` = '%s' AND `by` = '%s'", $db->real_escape_string($id), $db->real_escape_string($uid)));

	// If there is a Dislike
	return ($result->num_rows) ? 1 : 0;
}
?>