<?php
function cookie_law($values) {
	global $LNG, $pluginsSettings;

	$output = '';
	// Display the banner
	if(isset($_COOKIE['cookie_law']) == false) {

		$position = ($pluginsSettings['cookie_law_position'] ? 'cookie-law-banner-bottom' : 'cookie-law-banner-top');

		if($pluginsSettings['cookie_law_url']) {
            $more = '<a href="'.permalink($pluginsSettings['cookie_law_url']).'" target="_blank"><div class="cookie-law-button">'.$LNG['plugin_cookies_more_info'].'</div></a>';
        } else {
		    $more = '';
        }
		
		$output = '<div id="cookie-law-banner" class="'.$position.' cookie-law-banner-'.$pluginsSettings['cookie_law_color'].'"><div id="cookie-law-content">'.$LNG['plugin_cookies_text'].' '.$more.'<a href="javascript:;" onclick="cookie_law()"><div class="cookie-law-button">'.$LNG['plugin_cookies_ok'].'</div></a></div></div>';
	}
	return $output;
}
?>