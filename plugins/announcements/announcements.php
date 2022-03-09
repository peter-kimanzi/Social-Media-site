<?php

function announcements($values) {
	global $db;
	// Display the banner
	$result = $db->query("SELECT * FROM `announcements` WHERE DATE(NOW()) <= DATE(`duration`) ORDER BY `id` DESC");
	$output = '';
	while($row = $result->fetch_assoc()) {
		if($row['type'] == 2) {
			$type = 'error';
		} elseif($row['type'] == 1) {
			$type = 'success';
		} else {
			$type = 'info';
		}
		$content = '<strong>'.$row['title'].'</strong><br>'.$row['content'];
		$output .= '<div style="padding: 0 10px;">'.notificationBox($type, $content, 1).'</div>';
	}
	return $output;
}

?>