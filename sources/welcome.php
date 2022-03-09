<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;
	
	if($settings['captcha']) {
		$TMPL['captcha'] = '<input type="text" name="captcha" placeholder="'.$LNG['captcha'].'" style="background-image: url('.$CONF['url'].'/includes/captcha.php?dir='.$LNG['lang_dir'].')" class="welcome-captcha">';
	}

	if($settings['fbapp'] && $settings['fbappid'] && $settings['fbappsecret']) {
        if(isset($_GET['facebook'])) {
            $reg = new register();
            $reg->db = $db;
            $reg->url = $CONF['url'];
            $reg->verified = $settings['verified'];
            $reg->email_like = $settings['email_like'];
            $reg->email_comment = $settings['email_comment'];
            $reg->email_new_friend = $settings['email_new_friend'];
            $reg->email_group_invite = $settings['email_group_invite'];
            $reg->email_page_invite = $settings['email_page_invite'];
            $reg->email_mention = $settings['email_mention'];
            $reg->sound_new_notification = 1;
            $reg->sound_new_chat = 1;
            $reg->fbapp = $settings['fbapp'];
            $reg->fbappid = $settings['fbappid'];
            $reg->fbappsecret = $settings['fbappsecret'];
            $reg->fbcode = $_GET['code'];
            $reg->fbstate = $_GET['state'];
            $TMPL['registerMsg'] = $reg->facebook();

            if($TMPL['registerMsg'] == 1) {
                if($settings['mail']) {
                    sendMail($reg->email, sprintf($LNG['welcome_mail'], $settings['title']), sprintf($LNG['user_created'], $settings['title'], $reg->username, $CONF['url'], $settings['title'], $CONF['url'], $settings['title']), $CONF['email']);
                }
                $_SESSION['message'] = 'welcome';
                header("Location: ".permalink($CONF['url']."/index.php?a=feed"));
            }
        } else {
            // Generate a session to prevent CSFR
            $_SESSION['state'] = md5(uniqid(rand(), TRUE));

            $TMPL['fblogin'] = '<div class="facebook-button"><a href="https://www.facebook.com/dialog/oauth?client_id='.$settings['fbappid'].'&redirect_uri='.urlencode($CONF['url'].'/index.php?facebook=true').'&scope=public_profile,email&state='.$_SESSION['state'].'" class="facebook-button">Facebook</a></div>';
        }
    }
	
	if(isset($_POST['register'])) {
		// Register usage
		$reg = new register();
		$reg->db = $db;
		$reg->url = $CONF['url'];
		$reg->site_email = $CONF['email'];
		$reg->title = $settings['title'];
		$reg->username = $TMPL['register_username'] = $_POST['username'];
		$reg->password = $TMPL['register_password'] = $_POST['password'];
		$reg->confirmpassword = $TMPL['confirmpassword'] = $_POST['confirmpassword'];
		$reg->refer = $TMPL['refer'] = $_POST['refer'];
		$reg->first_name = strip_tags_array((isset($_POST['first_name']) ? $_POST['first_name'] : null));
		$reg->last_name = strip_tags_array((isset($_POST['last_name']) ? $_POST['last_name'] : null));
		$reg->email = $TMPL['register_email'] = $_POST['email'];
		$reg->captcha = $_POST['captcha'] ?? null;
		$reg->captcha_on = $settings['captcha'];
		$reg->email_confirmation = $settings['email_activation'];
		$reg->verified = $settings['verified'];
		$reg->email_like = $settings['email_like'];
		$reg->email_comment = $settings['email_comment'];
		$reg->email_new_friend = $settings['email_new_friend'];
		$reg->email_group_invite = $settings['email_group_invite'];
		$reg->email_page_invite = $settings['email_page_invite'];
		$reg->email_mention = $settings['email_mention'];
		$reg->sound_new_notification = 1;
		$reg->sound_new_chat = 1;
		$reg->accounts_per_ip = $settings['aperip'];
		$reg->email_provider = $settings['email_provider'];
		$reg->agreement = isset($_POST['agreement']) ? $_POST['agreement'] : null;

		$TMPL['agreement_checked'] = isset($_POST['agreement']) ? ' checked="checked"' : '';
		
		$TMPL['registerMsg'] = $reg->process();

		if($TMPL['registerMsg'] == 1) {
			if($settings['mail']) {
				sendMail($_POST['email'], sprintf($LNG['welcome_mail'], $settings['title']), sprintf($LNG['user_created'], $settings['title'], $_POST['username'], $CONF['url'], $settings['title']), $CONF['email']);
			}
			$_SESSION['message'] = 'welcome';
			header("Location: ".permalink($CONF['url']."/index.php?a=feed"));
		}
	}
	
	if(isset($_POST['login'])) {
		// Log-in usage
		$log = new User();
		$log->db = $db;
		$log->url = $CONF['url'];
		$log->username = $TMPL['login_username'] = $_POST['username'];
		$log->password = $TMPL['login_password'] = $_POST['password'];
		$log->remember = (isset($_POST['remember']) ? $_POST['remember'] : null);

        $TMPL['remember_checked'] = isset($_POST['remember']) ? ' checked="checked"' : '';
		
		$auth = $log->auth(1);
		
		if(!is_array($auth)) {
			$TMPL['loginMsg'] = notificationBox('error', $auth, 1);
		} else {
			header("Location: ".permalink($CONF['url']."/index.php?a=feed"));
		}
	}

	if(isset($user['username'])) {
		header("Location: ".permalink($CONF['url']."/index.php?a=feed"));
	}
	
	if(isset($_GET['activate']) && isset($_GET['username'])) {
		// Register usage
		$reg = new register();
		$reg->db = $db;
		$reg->url = $CONF['url'];
		$reg->site_email = $CONF['email'];
		$reg->title = $settings['title'];
		$TMPL['loginMsg'] = $reg->activate_account($_GET['activate'], $_GET['username']);
	}
	
	// Start displaying the home-page users
	$result = $db->query("SELECT * FROM `users` WHERE `image` != 'default.png' ORDER BY `idu` DESC LIMIT 10 ");
	$users = [];
	while($row = $result->fetch_assoc()) {
		$users[] = $row;
	}
	
	$TMPL['rows'] = showUsers($users, $CONF['url']);
	
	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = $LNG['welcome'].' - '.$settings['title'];
	$TMPL['site_title'] = $settings['title'];
	$TMPL['agreement'] = sprintf($LNG['register_agreement'], permalink($settings['tos_url']), permalink($settings['privacy_url']));
	
	$TMPL['ad'] = $settings['ad1'];
	
	// Load the welcome plugins

    $TMPL['plugins'] = '';
	foreach($plugins as $plugin) {
		if(array_intersect(array("4"), str_split($plugin['type']))) {
			$data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email'];
			$TMPL['plugins'] .= plugin($plugin['name'], $data, 0);
		}
	}

	$TMPL['recover_url'] = permalink($CONF['url'].'/index.php?a=recover');
	$TMPL['welcome_url'] = permalink($CONF['url'].'/index.php?a=welcome');
	$skin = new skin('welcome/content');
	return $skin->make();
}
?>