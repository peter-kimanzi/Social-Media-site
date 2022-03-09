<?php
function url_parser_delete($values) {
    $value	= $values['value'];
    $type	= $values['type'];
    $id		= $values['id'];

    // Check if the message is a poll and there's no type set
    if(substr($value, 0, 4) == 'url:' && !$type) {
        global $db;
        $image = json_decode(str_replace('url:', '', $value), true);

        // Delete the stored files
        unlink(__DIR__ .'/uploads/'.$image['image']);
    }
}
?>