<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $settings, $plugins;
	
	$select_pages = $db->query("SELECT * FROM `info_pages` ORDER BY `id` ASC");
	
	$page = array();
	
	while($row = $select_pages->fetch_assoc()) {
		$page[$row['url']] = array(skin::parse($row['title']), skin::parse($row['content']), $row['public']);
	}

	$skin = new skin('info/sidebar'); $sidebar = '';
	$TMPL['links'] = '';
	foreach($page as $url => $value) {
		if($value[2]) {
			$class = '';
			if($_GET['b'] == $url) {
				$class = ' sidebar-link-active';
			}
			$TMPL['links'] .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($CONF['url'].'/index.php?a=info&b='.$url).'" rel="loadpage">'.skin::parse($value[0]).'</a></div>';
		}
	}
	$sidebar = $skin->make();
	
	if(!empty($_GET['b']) && isset($page[$_GET['b']][0])) {
		$b = $_GET['b'];
		$TMPL['sidebar'] = $sidebar;
		$TMPL['url'] = $CONF['url'];
		$TMPL['title'] = skin::parse($page[$b][0]).' - '.$settings['title'];
		$TMPL['content'] = skin::parse($page[$b][1]);
		$TMPL['header'] = '<strong>'.skin::parse($page[$b][0]).'</strong>';
		$skin = new skin("info/content");
		return $skin->make();
	} else {
		header("Location: ".$CONF['url']);
	}
}
?>