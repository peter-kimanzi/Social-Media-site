<?php
function social_share_activate() {
	global $db;
	$db->query("INSERT IGNORE INTO `plugins_settings` (`name`, `value`) VALUES ('social_share_services', 'facebook,twitter,pinterest,tumblr,email,vkontakte,reddit,linkedin,whatsapp,viber,digg,evernote,yummly,yahoo,gmail')");
}
?>