<?php
function poll_event($values) {
	if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
		return false;
	} else {
		global $LNG;
		$form = '
		<div id="answers-list" style="display: none;">
			<div class="polls-input-container">
				<input type="text" class="polls-input" placeholder="'.$LNG['plugin_poll_answer'].' 1" name="poll-answer[]" maxlength="64"></div>
				<div class="polls-input-container"><input type="text" class="polls-input" placeholder="'.$LNG['plugin_poll_answer'].' 2" name="poll-answer[]" maxlength="64">
			</div>
		</div>
		<div class="polls-input-container" id="polls-options" style="display: none;">
			<input type="text" class="polls-input polls-half" placeholder="'.$LNG['plugin_poll_duration'].'" name="poll-stop"><div class="polls-input polls-half poll-text-right" id="polls-add">
				<a href="javascript:;"onclick="addAnswer(\''.$LNG['plugin_poll_answer'].'\')" id="polls-add-answer">'.$LNG['plugin_poll_add_answer'].'</a>
			</div>
		</div>';
		
		$button = '<input type="radio" name="type" value="" id="poll" class="input_hidden"><label for="poll" id="polls-button" class="plugin-button" title="Create a poll"><img src="'.$values['site_url'].'/plugins/poll/icons/polls.svg"></label>';
	}
	return $form.$button;
}
?>