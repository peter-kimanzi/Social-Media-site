<?php
function weather_settings() {
    global $CONF, $LNG, $pluginsSettings;

    $format = [0 => 'celsius', 1 => 'fahrenheit'];
    $formatOptions = '';
    foreach($format as $key => $value) {
        $formatOptions .= '<option value="'.strtolower($key).'"'.(strtolower($pluginsSettings['weather_format']) == strtolower($key) ? 'selected="selected"' : '').'>'.$LNG['plugin_weather_'.$value].'</option>';
    }

    $days = [0, 1, 2, 3, 4, 5];
    $daysOptions = '';
    foreach($days as $value) {
        $daysOptions .= '<option value="'.strtolower($value).'"'.(strtolower($pluginsSettings['weather_days']) == strtolower($value) ? 'selected="selected"' : '').'>'.$value.
        '</option>';
    }

    // Settings Content
    return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_weather_api_key'].'</div>
			<div class="page-input-content">
				<input type="text" name="weather_api_key" value="'.$pluginsSettings['weather_api_key'].'">
				<div class="page-input-sub">'.$LNG['plugin_weather_api_key_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_weather_default_location'].'</div>
			<div class="page-input-content">
				<input type="text" name="weather_default_location" value="'.$pluginsSettings['weather_default_location'].'">
				<div class="page-input-sub">'.$LNG['plugin_weather_default_location_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_weather_format'].'</div>
            <div class="page-input-content">
                <select name="weather_format">
                    '.$formatOptions.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_weather_format_sub'].'</div>
            </div>
        </div>
		
		<div class="page-input-container">
            <div class="page-input-title">'.$LNG['plugin_weather_days'].'</div>
            <div class="page-input-content">
                <select name="weather_days">
                    '.$daysOptions.'
                </select>
                <div class="page-input-sub">'.$LNG['plugin_weather_days_sub'].'</div>
            </div>
        </div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_weather_save'].'">
	</div>
	</form></div><div>';
}

function weather_save($values) {
    global $db;

    // Validate the inputs
    $values['weather_days'] = (int)$values['weather_days'];
    $values['weather_format'] = (int)$values['weather_format'];

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES('weather_days', ?), ('weather_format', ?), ('weather_default_location', ?), ('weather_api_key', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('iiss', $values['weather_days'], $values['weather_format'], $values['weather_default_location'], $values['weather_api_key']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>