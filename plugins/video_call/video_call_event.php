<?php
function video_call_event($values) {
	global $LNG;
	
	require_once(__DIR__ .'/vendor/autoload.php');

    $output = '';
	if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
        $output = '
        <div class="c-w-icon c-w-icon-video-call chat-video-call-btn" title="'.sprintf($LNG['plugin_video_call_start_video'], (isset($values['user']['idu']) ? realName($values['user']['username'], $values['user']['first_name'], $values['user']['last_name']) : '\'+realname+\'')).'" onclick="video_call_manage(0, '.(isset($values['user']['idu']) ? $values['user']['idu'] : '\'+id+\'').', 1);"></div>
        
        <div class="c-w-icon c-w-icon-audio-call chat-video-call-btn" title="'.sprintf($LNG['plugin_video_call_start_audio'], (isset($values['user']['idu']) ? realName($values['user']['username'], $values['user']['first_name'], $values['user']['last_name']) : '\'+realname+\'')).'" onclick="video_call_manage(0, '.(isset($values['user']['idu']) ? $values['user']['idu'] : '\'+id+\'').', 0);"></div>
        ';
	}
	return $output;
}
?>