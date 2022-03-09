<?php
function cookie_law_settings() {
    global $CONF, $LNG, $pluginsSettings;

    $colors = ['Black', 'Gray', 'Brown', 'Red', 'Blue', 'Green', 'Yellow', 'Orange', 'Purple', 'Pink'];

    $colorOptions = '';
    foreach($colors as $color) {
        $colorOptions .= '<option value="'.strtolower($color).'"'.(strtolower($pluginsSettings['cookie_law_color']) == strtolower($color) ? 'selected="selected"' : '').'>'.$color.'</option>';
    }

    $positions = [0 => 'top', 1 => 'bottom'];
    $positionOptions = '';
    foreach($positions as $key => $value) {
        $positionOptions .= '<option value="'.strtolower($key).'"'.(strtolower($pluginsSettings['cookie_law_position']) == strtolower($key) ? 'selected="selected"' : '').'>'.$LNG['plugin_cookie_law_'.$value].'</option>';
    }

    // Settings Content
    return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		<div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_cookie_law_position'].'</div>
            <div class="page-input-content">
                <select name="cookie_law_position">
                    '.$positionOptions.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_cookie_law_position_sub'].'</div>
            </div>
        </div>
		
		<div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_cookie_law_color'].'</div>
            <div class="page-input-content">
                <select name="cookie_law_color">
                    '.$colorOptions.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_cookie_law_color_sub'].'</div>
            </div>
        </div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_cookie_law_url'].'</div>
			<div class="page-input-content">
				<input type="text" name="cookie_law_url" value="'.$pluginsSettings['cookie_law_url'].'">
				<div class="page-input-sub">'.$LNG['plugin_cookie_law_url_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_cookie_law_save'].'">
	</div>
	</form></div><div>';
}

function cookie_law_save($values) {
    global $db;

    // Validate the inputs
    $values['file_share_max_files'] = (int)$values['cookie_law_position'];

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES('cookie_law_position', ?), ('cookie_law_color', ?), ('cookie_law_url', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('iss', $values['cookie_law_position'], $values['cookie_law_color'], $values['cookie_law_url']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>