<?php
function file_share_delete($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	
	// Check if the message is a file and there's no type set
	if(substr($value, 0, 5) == 'file:' && !$type) {
		$files = json_decode(str_replace('file:', '', $value), true);
		
		foreach($files['files'] as $file) {
			// Delete the stored files
			unlink(__DIR__ .'/uploads/'.$file['filename']);
		}
	}
}
?>