<?php

function announcements_activate() {
	global $db;
	$db->query("CREATE TABLE IF NOT EXISTS `announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
  `content` text COLLATE utf8_unicode_ci NOT NULL,
  `type` int(11) NOT NULL,
  `duration` date NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;");
}

?>