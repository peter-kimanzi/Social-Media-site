<?php
function social_share_settings() {
    global $CONF, $LNG, $pluginsSettings;

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

    $servicesOutput = '';
    foreach($services as $value => $name) {
        $servicesOutput .= '<input type="checkbox" name="social_share_services[]" id="'.$value.'_service" value="'.$value.'"'.(in_array(strtolower($value), explode(',', $pluginsSettings['social_share_services'])) ? 'checked' : '').'><label for="'.$value.'_service">'.$name.'</label><br>';
    }

    // Settings Content
    return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_social_share_services'].'</div>
			<div class="page-input-content">
                '.$servicesOutput.'
				<div class="page-input-sub">'.$LNG['plugin_social_share_services_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_social_share_save'].'">
	</div>
	</form></div><div>';
}

function social_share_save($values) {
    global $db;

    // Validate the inputs
    $values['social_share_services'] = implode(',', $values['social_share_services']);

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES('social_share_services', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('s', $values['social_share_services']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>