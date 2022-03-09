<?php

function video_call_activate() {
	global $db;
	$db->query("CREATE TABLE IF NOT EXISTS `video_calls` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` int(11) NOT NULL,
  `to` int(11) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `to` (`to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");

    $db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('video_call_twilio_account_sid', ''), ('video_call_twilio_key_sid', ''), ('video_call_twilio_key_secret', ''), ('video_call_dial_time', '30'), ('video_call_call_time', '12')");
}

?>