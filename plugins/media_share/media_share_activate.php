<?php
function media_share_activate() {
	global $db;
	$db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('media_share_max_size', '2097152'), ('media_share_video_extensions', 'mp4'), ('media_share_audio_extensions', 'mp3'), ('media_share_services', 'youtube,vimeo,twitch,streamable,dailymotion,soundcloud,mixcloud,tunein,spotify,giphy,gfycat'), ('media_share_video', '1'), ('media_share_audio', '1')");
}
?>