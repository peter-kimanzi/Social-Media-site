<?php
$time_start = microtime(true);
require_once(__DIR__ . '/includes/autoload.php');

if(isset($_GET['a']) && isset($action[$_GET['a']])) {
	$page_name = $action[$_GET['a']];
} else {
	$page_name = 'welcome';
}

if(!isAjax()) {
	$TMPL['token_id'] = generateToken();
}

// Extra class for the content [main and sidebar]
$TMPL['content_class'] = ' content-'.$page_name;

require_once("./sources/{$page_name}.php");

$TMPL['styles'] = '';
// Load the head plugins
foreach($plugins as $plugin) {
	if(array_intersect(array("8"), str_split($plugin['type']))) {
		$TMPL['styles'] .= "\n<link href=\"".$CONF['url']."/plugins/".$plugin['name']."/".$plugin['name'].".css\" rel=\"stylesheet\" type=\"text/css\">";
	}
}

$TMPL['scripts'] = '';
foreach($plugins as $plugin) {
	if(array_intersect(array("9"), str_split($plugin['type']))) {
		$TMPL['scripts'] .= "\n<script type=\"text/javascript\" src=\"".$CONF['url']."/plugins/".$plugin['name']."/".$plugin['name'].".js\"></script>";
	}
}

$TMPL['site_url'] = $CONF['url'];

if(isAjax()) {
	echo json_encode(array('content' => PageMain(), 'title' => $TMPL['title']));
	mysqli_close($db);
	return;
}

$TMPL['content'] = PageMain();

if(!empty($user['username'])) {
	$TMPL['menu'] = menu($user);
	$TMPL['url_logo'] = permalink($CONF['url'].'/index.php?a=feed');
} else {
	$TMPL['menu'] = menu(false);
	$TMPL['url_logo'] = permalink($CONF['url'].'/index.php?a=welcome');
}

$TMPL['url'] = $CONF['url'];
$TMPL['footer'] = $settings['title'];
$TMPL['footer_url'] = permalink($CONF['url'].'/index.php?a=info&b=');
$TMPL['year'] = date('Y');
$TMPL['info_urls'] = info_urls();
$TMPL['powered_by'] = 'Powered by <a href="'.$url.'" target="_blank">'.$name.'</a>.';
$TMPL['language'] = getLanguage($CONF['url'], null, 1);
$TMPL['tracking_code'] = $settings['tracking_code'];
$TMPL['search_users_url'] = permalink($CONF['url'].'/index.php?a=search&q=');
$TMPL['search_tags_url'] = permalink($CONF['url'].'/index.php?a=search&tag=');
$TMPL['search_groups_url'] = permalink($CONF['url'].'/index.php?a=search&Discussions=');
$TMPL['search_pages_url'] = permalink($CONF['url'].'/index.php?a=search&pages=');
$LNG['search_for_people'] = $LNG['search_for_people'].($settings['pages'] ? $LNG['search_pages'] : '').($settings['Discussions'] ? $LNG['search_groups'] : '');

$skin = new skin('wrapper');

echo $skin->make();

mysqli_close($db);
?>
