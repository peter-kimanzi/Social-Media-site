<?php
function poll_output($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	global $CONF, $db, $settings, $LNG;

	// Check if the message is a poll and there's no type set
	if(substr($value, 0, 5) == 'poll:' && !$type) {
		
		$poll = json_decode(str_replace('poll:', '', $value), true);
		
		$query = sprintf("SELECT * FROM `polls_answers` WHERE `polls_answers`.`question` = '%s' ORDER BY `id` ASC", $db->real_escape_string($poll['id']));
		$result = $db->query($query);
		
		// If the table exist
		if($result) {
			$duration_query = $db->query(sprintf("SELECT * FROM `polls_durations` WHERE `poll_id` = '%s'", $db->real_escape_string($poll['id'])));
			$duration_result = $duration_query->fetch_assoc();
	
			$start = date("Y-m-d H:i:s", $duration_result['poll_start']);
			$stop = date("Y-m-d H:i:s", strtotime('+'.$duration_result['poll_stop'].' days', strtotime($start)));
			
			// Get the voting results
			$count = $db->query(sprintf("SELECT * FROM `polls_results` WHERE `question` = '%s'", $db->real_escape_string($poll['id'])));

			$rows = $ans = [];

			// Store the voting results
			while($row = $count->fetch_assoc()) {
				$rows[] = $row;
			}
			
			// Check if the logged-in user already voted, and store the answers results to check for numbers
			foreach($rows as $an) {
				if($an['by'] == $values['user_id']) {
					// If the user has voted
					$voted = $an['answer'];
				}
				$ans[] = $an['answer'];
			}
			
			// The total number of votes
			$total = count($rows);
			
			if(date("Y-m-d H:i:s") > $stop) {
				$status = sprintf($LNG['plugin_poll_final_results'], $total);
			} else {
				$status = '<span class="poll-button-container" onclick="pollVote(\''.$poll['id'].'\')">'.$LNG['plugin_poll_vote'].'</span><div class="polls-footer-text">'.sprintf($LNG['plugin_poll_votes'], $total).'</div>';
			}

			$answer = '';
			while($row = $result->fetch_assoc()) {
				// Check how many votes for the current answer
				$votes = count(array_intersect($ans, array($row['id'])));
				
				// Calculate the percentage
                if($total > 0) {
                    $percentage = ($votes * 100) / $total;
                } else {
                    $percentage = 0;
                }
				
				// If the user has already voted, the user is not logged-in or if the vote has ended
				if(isset($voted) || empty($values['user_id']) || date("Y-m-d H:i:s") > $stop) {
					$button = ($row['id'] == $voted) ? '<strong>'.number_format($percentage, 0).'%</strong>' : number_format($percentage, 0).'%';
					$style = ' style="width: '.number_format($percentage, 0).'%;'.(($percentage == 0) ? ' background: #EEE;' : '').'"';
					if(date("Y-m-d H:i:s") > $stop) {
						$status = sprintf($LNG['plugin_poll_final_results'], $total);
					} else {
						$status = sprintf($LNG['plugin_poll_votes'], $total);
					}
				} else {
					$button = '<input type="radio" name="poll-answer-'.$row['question'].'" value="'.$row['id'].'">';
					$style = ' style="width: 0%; background: #EEE;"';
				}
				$answer .= '<div class="poll-percentage" title="'.sprintf($LNG['plugin_poll_votes'], $votes).'">'.$button.'</div><div class="poll-answer-container"><div class="poll-answer-content"><div class="poll-answer-percentage"'.$style.'><div class="poll-answer-text">'.htmlspecialchars($row['answer']).'</div><div class="poll-answer-votes"></div></div></div></div>';
			}
		}
		
		if($answer) {
			$output = '<div class="poll-id-'.$poll['id'].'"><div class="polls-container">'.$answer.'</div><div class="polls-footer">'.$status.' </div><div class="message-divider"></div></div>';
		
			return $output;
		}
	}
}
?>