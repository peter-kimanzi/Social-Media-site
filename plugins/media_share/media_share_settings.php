<?php
function media_share_settings() {
    global $CONF, $LNG, $pluginsSettings;

    $services = [
        'youtube'       => 'YouTube',
        'vimeo'         => 'Vimeo',
        'twitch'        => 'Twitch',
        'streamable'    => 'Streamable',
        'dailymotion'   => 'Dailymotion',
        // 'metacafe'      => 'MetaCafe',
        'soundcloud'    => 'SoundCloud',
        'mixcloud'      => 'Mixcloud',
        'tunein'        => 'TuneIn',
        'spotify'       => 'Spotify',
        'giphy'         => 'Giphy',
        'gfycat'        => 'Gfycat'
    ];

    $servicesOutput = '';
    foreach($services as $value => $name) {
        $servicesOutput .= '<input type="checkbox" name="media_share_services[]" id="'.$value.'_service" value="'.$value.'"'.(in_array(strtolower($value), explode(',', $pluginsSettings['media_share_services'])) ? 'checked' : '').'><label for="'.$value.'_service">'.$name.'</label><br>';
    }

    $avOpt = [0 => 'off', 1 => 'on'];
    $videoOutput = $audioOutput = '';
    foreach($avOpt as $key => $value) {
        $videoOutput .= '<option value="'.strtolower($key).'"'.(strtolower($pluginsSettings['media_share_video']) == strtolower($key) ? 'selected="selected"' : '').'>'.$LNG['plugin_media_share_'.$value].'</option>';

        $audioOutput .= '<option value="'.strtolower($key).'"'.(strtolower($pluginsSettings['media_share_audio']) == strtolower($key) ? 'selected="selected"' : '').'>'.$LNG['plugin_media_share_'.$value].'</option>';
    }

    // Settings Content
    return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		
		<div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_media_share_video'].'</div>
            <div class="page-input-content">
                <select name="media_share_video">
                    '.$videoOutput.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_media_share_video_sub'].'</div>
            </div>
        </div>
        
        <div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_media_share_video_extensions'].'</div>
			<div class="page-input-content">
				<input type="text" name="media_share_video_extensions" value="'.$pluginsSettings['media_share_video_extensions'].'">
				<div class="page-input-sub">'.$LNG['plugin_media_share_video_extensions_sub'].'</div>
			</div>
		</div>
        
        <div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_media_share_audio'].'</div>
            <div class="page-input-content">
                <select name="media_share_audio">
                    '.$audioOutput.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_media_share_audio_sub'].'</div>
            </div>
        </div>
        
        <div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_media_share_audio_extensions'].'</div>
			<div class="page-input-content">
				<input type="text" name="media_share_audio_extensions" value="'.$pluginsSettings['media_share_audio_extensions'].'">
				<div class="page-input-sub">'.$LNG['plugin_media_share_audio_extensions_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_media_share_max_size'].'</div>
			<div class="page-input-content">
				<input type="text" name="media_share_max_size" value="'.round(($pluginsSettings['media_share_max_size'] / 1024) / 1024).'">
				<div class="page-input-sub">'.$LNG['plugin_media_share_max_size_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_media_share_services'].'</div>
			<div class="page-input-content">
                '.$servicesOutput.'
				<div class="page-input-sub">'.$LNG['plugin_media_share_services_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_media_share_save'].'">
	</div>
	</form></div><div>';
}

function media_share_save($values) {
    global $db;

    // Validate the inputs
    $values['media_share_max_size'] = (((int)$values['media_share_max_size']) * 1024) * 1024;
    $values['media_share_services'] = implode(',', $values['media_share_services']);
    $values['media_share_video'] = (int)$values['media_share_video'];
    $values['media_share_audio'] = (int)$values['media_share_audio'];
    $values['media_share_video_extensions'] = strtolower(str_replace(' ', '', $values['media_share_video_extensions']));
    $values['media_share_audio_extensions'] = strtolower(str_replace(' ', '', $values['media_share_audio_extensions']));

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES('media_share_max_size', ?), ('media_share_services', ?), ('media_share_video', ?), ('media_share_audio', ?), ('media_share_video_extensions', ?), ('media_share_audio_extensions', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('isiiss', $values['media_share_max_size'], $values['media_share_services'], $values['media_share_video'], $values['media_share_audio'],  $values['media_share_video_extensions'], $values['media_share_audio_extensions']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>