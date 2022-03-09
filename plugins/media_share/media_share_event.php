<?php
function media_share_event($values) {
	global $LNG, $pluginsSettings;
	if(isset($values['plugin_chat'])) {
		$form = '<label for="chat-media-share" data-userid="'.(isset($values['user']['idu']) ? $values['user']['idu'] : '\'+id+\'').'" class="c-w-icon c-w-icon-media-share chat-media-share-btn" title="'.$LNG['plugin_media_share_upload'].'"></label>';
	} else {
		$form = '
		<div id="media-share-location" style="display: none;">
			<div class="media-share-input-container">
				<div id="media-share-info">
					<label for="media-share-files" class="plugin-button-normal media-share-button">'.$LNG['plugin_media_share_select_file'].'</label>
					<input name="media-share-files[]" id="media-share-files" size="27" type="file" accept=".'.implode(',.', array_merge(explode(',', $pluginsSettings['media_share_audio_extensions']), explode(',', $pluginsSettings['media_share_video_extensions']))).'">
					<div id="media-share-details">'.sprintf($LNG['plugin_media_share_per_file'], fsize($pluginsSettings['media_share_max_size'])).'</div>
				</div>
				<div id="media-share-list"></div>
			</div>
		</div>';
	}
	return $form;
}
?>