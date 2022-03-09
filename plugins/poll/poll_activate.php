<?php
function poll_activate() {
	global $db;
	$db->query("CREATE TABLE IF NOT EXISTS `polls_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` int(11) NOT NULL,
  `answer` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `question` (`question`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
	$db->query("CREATE TABLE IF NOT EXISTS `polls_durations` (
  `poll_id` int(11) NOT NULL,
  `poll_start` int(11) NOT NULL,
  `poll_stop` int(11) NOT NULL,
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
	$db->query("CREATE TABLE IF NOT EXISTS `polls_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` int(11) NOT NULL,
  `answer` int(11) NOT NULL,
  `by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `question` (`question`),
  KEY `by` (`by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;");
}
?>