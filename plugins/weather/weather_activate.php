<?php
function weather_activate() {
	global $db;
	$db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('weather_days', '5'), ('weather_format', '0'), ('weather_api_key', ''), ('weather_default_location', 'New York')");
}
?>