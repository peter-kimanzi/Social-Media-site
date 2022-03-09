<?php
function file_share_event($values) {
	global $LNG, $pluginsSettings;

	$button = '';
	if(isset($values['plugin_chat'])) {
		$form = '<label for="chat-file-share" data-userid="'.(isset($values['user']['idu']) ? $values['user']['idu'] : '\'+id+\'').'" class="c-w-icon c-w-icon-file-share chat-file-share-btn" title="'.$LNG['plugin_file_share_upload_file'].'"></label>';
	} else {
		$form = '
		<div id="file-share-location" style="display: none;">
			<div class="file-share-input-container">
				<div id="file-share-info">
					<label for="file-share-files" class="plugin-button-normal file-share-button">'.$LNG['plugin_file_share_select_files'].'</label>
					<input name="file-share-files[]" id="file-share-files" size="27" type="file" multiple="multiple" accept=".'.implode(',.', explode(',', $pluginsSettings['file_share_allowed_extensions'])).'">
					<div id="file-share-details">'.sprintf($LNG['plugin_file_share_up_to'], $pluginsSettings['file_share_max_files'], fsize($pluginsSettings['file_share_max_size']), fsize($pluginsSettings['file_share_max_upload_size'])).'</div>
				</div>
				<div id="file-share-list"></div>
			</div>
		</div>';
		
		$button = '<input type="radio" name="type" value="plugin" id="file-share" class="input_hidden"><label for="file-share" id="file-share-button" class="plugin-button" title="'.$LNG['plugin_file_share_upload_files'].'"><img src="'.$values['site_url'].'/plugins/'.basename(__DIR__).'/icons/files.svg"></label>';
	}
	return $form.$button;
}
?>