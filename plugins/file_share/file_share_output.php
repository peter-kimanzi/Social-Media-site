<?php
function file_share_output($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	global $CONF, $LNG;

	$output = '';
	// Check if the message is a file and there's no type set
	if(substr($value, 0, 5) == 'file:' && !$type) {
		$files = json_decode(str_replace('file:', '', $value), true);
		
		foreach($files['files'] as $file) {
			$output .= '<div class="file-share-element">'.htmlspecialchars($file['name'].'.'.$file['ext']).'<span class="file-share-value">('.fsize($file['size']).') <a href="'.$CONF['url'].'/plugins/'.basename(__DIR__).'/uploads/'.$file['filename'].'" download="'.$file['name'].'" title="'.$LNG['plugin_file_share_download'].'"><div class="file-share-download"></div></a></span></div>';
		}
		if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
			$output = '<div class="file-share-container-chat">'.$output.'</div>';
		} else {
			$output = '<div class="file-share-container">'.$output.'</div><div class="message-divider"></div>';
		}
		
		return $output;
	}
}
?>