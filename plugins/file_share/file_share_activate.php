<?php
function file_share_activate() {
	global $db;
	$db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('file_share_allowed_extensions', 'zip,7z,rar,txt,pdf,docx,pptx,xlsx,jpg,png,gif,3gp,mp4,flv,mkv,mp3,wav'), ('file_share_max_files', '5'), ('file_share_max_size', '2097152'), ('file_share_max_upload_size', '110485760')");
}
?>