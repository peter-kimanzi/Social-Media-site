<?php
function cookie_law_activate() {
	global $db;
    $db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('cookie_law_position', '0'), ('cookie_law_color', 'black'), ('cookie_law_url', '')");
}
?>