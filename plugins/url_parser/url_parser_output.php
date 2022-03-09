<?php
function url_parser_output($values) {
	$value		= $values['value'];
	$type		= $values['type'];
	global $CONF;
	
	// If there's no event type and there's an event value that matches "url:"
	if(empty($type) && substr($value, 0, 4) == 'url:') {
		$url = json_decode(str_replace('url:', '', $value), true);

		// Get the short form of the URL
		$url['url_short'] = parse_url($url['url'], PHP_URL_HOST);
		
		// If there's an image
		$url['image'] = ($url['image'] ? '<a href="'.$url['url'].'" class="link-poster" style="background-image: url('.$CONF['url'].'/plugins/url_parser/uploads/'.$url['image'].');" data-nd="" target="_blank" rel="nofollow"></a>' : '');
		
		$output = '<div class="link-container">
			'.$url['image'].'
			<div class="link-content">
				<div class="link-title"><a href="'.$url['url'].'" target="_blank" rel="nofollow">'.htmlentities($url['title'], ENT_QUOTES).'</a></div>
				<div class="link-description">'.htmlentities($url['description'], ENT_QUOTES).'</div>
				<div class="link-url">'.$url['url_short'].'</div>
            </div>
		</div><div class="message-divider"></div>';
		
		// Return the result
		return $output;
	}
}
?>