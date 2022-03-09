<?php
function social_share_output($values) {
	$value	= $values['value'];
	$type	= $values['type'];
	$id		= $values['id'];
	global $CONF, $pluginsSettings;

    $socialServices = explode(',', $pluginsSettings['social_share_services']);

   /**
    *
	* array map: css class => array(service name, js value)
	*
	*/

    $services = [
        'facebook'      => 'Facebook',
        'twitter'       => 'Twitter',
        'pinterest'     => 'Pinterest',
        'tumblr'        => 'Tumblr',
        'email'         => 'Email',
        'vkontakte'     => 'VKontakte',
        'reddit'        => 'reddit',
        'linkedin'      => 'LinkedIn',
        'whatsapp'      => 'WhatsApp',
        'viber'         => 'Viber',
        'digg'          => 'Digg',
        'evernote'      => 'Evernote',
        'yummly'        => 'Yummly',
        'yahoo'         => 'Yahoo',
        'gmail'         => 'Gmail'
    ];

	$buttons = '';
	// Get the available buttons
	foreach($socialServices as $service) {
        $buttons .= '<div class="share-icon-container"><div class="share-icon-padding"><a id="'.$service.'-share" title="'.$services[$service].'" onclick="share_social(\''.$service.'\', \''.urlencode(permalink($CONF['url'].'/index.php?a=post&m='.$id)).'\', '.$id.')" rel="nofollow"><div class="share-social-icon '.$service.'-icon"></div></a></div></div>';
	}
	
	// If there's no button to output
	if(empty($buttons)) {
		return false;
	}
	
	// Output the social buttons
	$output = '
	<div class="social-share-container">
		<div class="social-share-content">
			'.$buttons.'
		</div>
	</div>
	<div class="message-divider"></div>';

	return $output;
}
?>