<?php

function dislike_activate() {
	global $db;
	$db->query("CREATE TABLE IF NOT EXISTS `dislikes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  `type` int(11) NOT NULL DEFAULT '0',
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `verify_dislike` (`post`,`by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
}

?>