<?php
function media_share_delete($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	
	// Check if the message is a file and there's no type set
	if(substr($value, 0, 6) == 'media:') {
		$files = json_decode(str_replace('media:', '', $value), true);

		if(isset($files['videos'])) {
            foreach($files['videos'] as $file) {
                // Delete the stored files
                unlink(__DIR__ .'/uploads/'.$file['filename']);
            }
        }

        if(isset($files['audios'])) {
            foreach($files['audios'] as $file) {
                // Delete the stored files
                unlink(__DIR__ .'/uploads/'.$file['filename']);
            }
        }
	}
}
?>