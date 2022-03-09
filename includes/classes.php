<?php


function getSettings() {
	$querySettings = "SELECT * from `settings`";
	return $querySettings;
}
function menu($user) {
	global $TMPL, $LNG, $CONF, $db, $settings, $plugins;

	$admin_url = (isset($_SESSION['is_admin']) ? '<a href="'.$CONF['url'].'/index.php?a=admin" rel="loadpage"><div class="menu_btn" id="admin_btn" title="'.$LNG['admin_panel'].'"><img src="'.$CONF['url'].'/'.$CONF['theme_url'].'/images/icons/admin.png"></div></a>' : '');

	if($user !== false) {
		$skin = new skin('shared/menu'); $menu = '';

		$TMPL_old = $TMPL; $TMPL = array();

		$TMPL['realname'] = realName($user['username'], $user['first_name'], $user['last_name']);
		$TMPL['avatar'] = permalink($CONF['url'].'/image.php?t=a&w=50&h=50&src='.$user['image']);
		$TMPL['username'] = $user['username'];
		$TMPL['url'] = $CONF['url'];
		$TMPL['theme_url'] = $CONF['theme_url'];
		$TMPL['intervaln'] = $settings['intervaln'];
		$TMPL['intervalm'] = $settings['intervalm'];
		$TMPL['chatr'] = ($settings['chatr'] * 1000);
		$TMPL['smiles'] = $settings['smiles'] ? chatSmiles() : '';

	   /**
		* Array Map
		* array => { url, name, dynamic load, class type}
		*/
		$links = array(	array('profile&u='.$user['username'], realName($user['username'], $user['first_name'], $user['last_name']), 1, 0),
						array('feed', $LNG['title_feed'], 1, 0),
						array('notifications', $LNG['title_notifications'], 1, 0),
						array('page', $LNG['pages'], 1, 0),
						array('group',$LNG['groups'], 1, 0),
						array('settings', $LNG['title_settings'], 1, 0)
						);
						
						
		$extralink = array(array('support', $LNG['support'], 1,0));
		
		$logoutmenu = array(array('feed&logout', $LNG['log_out'], 0, 0));

        $TMPL['links'] = $TMPL['plugins'] = $divider = '';
		foreach($links as $element => $value) {
			if($value) {
				$TMPL['links'] .= $divider.'<a href="'.permalink($CONF['url'].'/index.php?a='.$value[0]).'" '.($value[2] ? ' rel="loadpage"' : '').'><div class="menu-dd-row'.(($value[3] == 1) ? ' menu-dd-extra' : '').(($value[3] == 2) ? ' menu-dd-mobile' : '').'">'.$value[1].'</div></a>';
				$divider = '<div class="menu-divider '.(($value[3] == 2) ? ' menu-dd-mobile' : '').'"></div>';
			}
		}
		
		
		foreach($extralink as $element => $value) {
			if($value) {
				$TMPL['links'] .= $divider.'<a href="https://careerpalglobal.com/'.$value[0].'"><div class="menu-dd-row'.(($value[3] == 1) ? ' menu-dd-extra' : '').(($value[3] == 2) ? ' menu-dd-mobile' : '').'">'.$value[1].'</div></a>';
				$divider = '<div class="menu-divider '.(($value[3] == 2) ? ' menu-dd-mobile' : '').'"></div>';
			}
		}


        foreach($logoutmenu as $element => $value) {
			if($value) {
				$TMPL['links'] .= $divider.'<a href="'.permalink($CONF['url'].'/index.php?a='.$value[0]).'" '.($value[2] ? ' rel="loadpage"' : '').'><div class="menu-dd-row'.(($value[3] == 1) ? ' menu-dd-extra' : '').(($value[3] == 2) ? ' menu-dd-mobile' : '').'">'.$value[1].'</div></a>';
				$divider = '<div class="menu-divider '.(($value[3] == 2) ? ' menu-dd-mobile' : '').'"></div>';
			}
		}


		$TMPL['admin_url'] = $admin_url;
		$TMPL['audio_container'] = audioContainer('Notification', $user['sound_new_notification']).audioContainer('Chat', $user['sound_new_chat']);

		$TMPL['privacy_url'] = permalink($CONF['url'].'/index.php?a=settings&b=privacy');
		$TMPL['invite_url'] = permalink($CONF['url'].'/index.php?a=settings&b=invite');
		$TMPL['notifications_url'] = permalink($CONF['url'].'/index.php?a=notifications');
		$TMPL['chats_url'] = permalink($CONF['url'].'/index.php?a=notifications&filter=chats');
		$TMPL['friendships_url'] = permalink($CONF['url'].'/index.php?a=notifications&filter=friendships');
		$TMPL['settings_notifications_url'] = permalink($CONF['url'].'/index.php?a=settings&b=notifications');
		$TMPL['profile_url'] = permalink($CONF['url'].'/index.php?a=profile&u=');
		$TMPL['settings_url'] = permalink($CONF['url'].'/index.php?a=messages&u=\'+username+\'&id=\'+id+\'');

		foreach($plugins as $plugin) {
			if(array_intersect(array("e"), str_split($plugin['type']))) {
				$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; $data['plugin_chat'] = 1; unset($data['password']); unset($data['salted']);
				$TMPL['plugins'] .= plugin($plugin['name'], $data, 3);
			}
		}

		$menu = $skin->make();

		$TMPL = $TMPL_old; unset($TMPL_old);
		return $menu;
	} else {
		// Else show the LogIn Register button
		return '<a href="'.permalink($CONF['url'].'/index.php?a=welcome').'" rel="loadpage" title="'.$LNG['connect'].'"><div class="topbar-button">'.$LNG['connect'].'</div></a>'.$admin_url;
	}
}
function info_urls() {
	global $CONF, $db;

	$pages = $db->query("SELECT `url`, `title` FROM `info_pages` WHERE `public` = 1 ORDER BY `id` ASC");

	$output = '';
	while($row = $pages->fetch_assoc()) {
		$output .= '<span><a href="'.permalink($CONF['url'].'/index.php?a=info&b='.$row['url']).'" rel="loadpage">'.skin::parse($row['title']).'</a></span>';
	}

	return $output;
}
function chatSmiles() {
	global $CONF, $LNG;
	require_once(__DIR__ .'/emojis.php');

	$default = 'people';

	$output = '<div class="emojis-container">';
	$buttons = '';
	foreach($emojis as $category => $list) {
		$output .= '<div class="emojis-list" id="emojis-'.$category.'"'.($default == $category ? ' style="display: block;"' : '').'>';
		foreach($list[0] as $emoji) {
			$output .= '<a onclick="addSmile(\''.addslashes($emoji).'\')">'.$emoji.'</a>';
		}
		$output .= '</div>';
		$buttons .= '<div class="emoji-category'.($default == $category ? ' emoji-category-active' : '').'" id="emoji-button-'.$category.'"onclick="showEmojis(\''.$category.'\')" title="'.$LNG['emoji_'.$category].'">'.$list[1][0].'</div>';
	}
	$output .= '</div>';
	return $output.'<div class="emoji-divider"></div><div class="emojis-buttons">'.$buttons.'</div>';
}
function notificationBox($type, $message, $extra = null) {
	// Extra 1: Add the -modal class name
	if($extra == 1) {
		$extra = ' notification-box-extra';
	}
	return '<div class="notification-box'.$extra.' notification-box-'.$type.'">
			<p>'.$message.'</p>
			<div class="notification-close notification-close-'.$type.'"></div>
			</div>';
}
class register {
	public $db; 					// Database Property
	public $url; 					// Installation URL Property
	public $username;				// The inserted username
	public $password;				// The inserted password
	public $confirmpassword;		// The inserted password to confirm
	public $refer;                  //Refer Code
	public $email;					// The inserted email
	public $captcha;				// The inserted captcha
	public $captcha_on;				// Store the Admin Captcha settings
	public $verified;				// Store the Admin Verified settings
	public $email_like;				// The general e-mail like setting [if allowed, it will turn on emails on likes]
	public $email_comment;			// The general e-mail like setting [if allowed, it will turn on emails on comments]
	public $email_new_friend;		// The general e-mail new friend setting [if allowed, it will turn on emails on new friendships]

	function facebook() {
		if($this->fbapp) {
			$getToken = $this->getFbToken($this->fbappid, $this->fbappsecret, $this->url.'/index.php?facebook=true', $this->fbcode);
			$user = $this->parseFbInfo($getToken['access_token']);

			if($getToken == null || $_SESSION['state'] == null || $_SESSION['state'] != $this->fbstate || empty($user->email)) {
				header("Location: ".$this->url);
				exit();
			}
			if(!empty($user->email)) {
				$this->email = $user->email;

				$this->first_name = $user->first_name;
				$this->last_name = $user->last_name;
				$user = $this->verify_if_email_exists(1);

				// If user already exist
				if($user) {
					if($user['suspended'] == 1) {
						global $LNG;
						header("Location: ".$this->url);
						return notificationBox('error', $LNG['account_suspended'], 1);
					}

					// Set sessions and log-in
					$_SESSION['username'] = $user['username'];
					$_SESSION['password'] = $user['password'];

					// Redirect user
					header("Location: ".$this->url);
				} else {
					$this->profile_image = $this->parseFbPicture($getToken['access_token']);
					$this->generateUsername();
					$this->rawPassword = $this->generatePassword(8);
					$this->password = password_hash($this->rawPassword, PASSWORD_DEFAULT);
					$this->query();

					$_SESSION['username'] = $this->username;
					$_SESSION['password'] = $this->password;

					return 1;
				}
			}
		}
	}

	function generateUsername($type = null) {
		// If type is set, generate a random username
		if($type) {
			$this->username = $this->parseUsername().rand(0, 999);
		} else {
			$this->username = $this->parseUsername();
		}

		// Replace the '.' sign with '_' (allows @user_mention)
		$this->username = str_replace('.', '_', $this->username);

		// Check if the username exists
		$checkUser = $this->verify_if_user_exist();

		if($checkUser) {
			$this->generateUsername(1);
		}
	}

	function parseUsername() {
		if(ctype_alnum($this->first_name) && ctype_alnum($this->last_name)) {
			return $this->username = $this->first_name.'.'.$this->last_name;
		} elseif(ctype_alnum($this->first_name)) {
			return $this->first_name;
		} elseif(ctype_alnum($this->last_name)) {
			return $this->last_name;
		} else {
			// Parse email address
			$email = explode('@', $this->email);
			$email = preg_replace("/[^a-z0-9]+/i", "", $email[0]);
			if(ctype_alnum($email)) {
				return $email;
			} else {
				return rand(0, 9999);
			}
		}
	}

	function generatePassword($length) {
		// Allowed characters
		$chars = str_split("abcdefghijklmnopqrstuvwxyz0123456789");

		$password = '';
		// Generate password
		for($i = 1; $i <= $length; $i++) {
			// Get a random character
			$n = array_rand($chars, 1);

			// Store random char
			$password .= $chars[$n];
		}
		return $password;
	}

	function getFbToken($app_id, $app_secret, $redirect_url, $code) {
		// Build the token URL
		$url = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&redirect_uri='.urlencode($redirect_url).'&client_secret='.$app_secret.'&code='.$code;

        $httpClient = new GuzzleHttp\Client();

        $response = null;

        try {
            $content = $httpClient->request('GET', $url, ['timeout' => 5]);

            $body = $content->getBody();

            // Get the file
            $response = json_decode($body, true);
        } catch(Exception $e) {

        }

		// Return parameters
		return $response;
	}

	function parseFbInfo($access_token) {
		// Build the Graph URL
		$url = "https://graph.facebook.com/me?fields=id,email,first_name,gender,last_name,link,locale,name,timezone,updated_time,verified&access_token=".$access_token;

        $httpClient = new GuzzleHttp\Client();

        $response = null;
        try {
            $content = $httpClient->request('GET', $url, ['timeout' => 5]);

            $body = $content->getBody()->getContents();

            // Get the file
            $response = json_decode($body);
        } catch(Exception $e) {

        }

        // Return user
		if(isset($response->name)) {
			return $response;
		}
		return null;
	}

	function parseFbPicture($access_token) {
		// Build the Graph URL
		$url = "https://graph.facebook.com/me/picture?width=500&height=500&access_token=".$access_token;

        $httpClient = new GuzzleHttp\Client();

        // Generate the file name
        $file_name = mt_rand().'_'.mt_rand().'_'.mt_rand().'.jpg';
        $file_path = __DIR__ .'/../uploads/avatars/';

        try {
            $httpClient->request('GET', $url, ['sink' => $file_path.$file_name, 'timeout' => 5]);
        } catch(Exception $e) {

        }

		// If the file wasn't written
		if(!file_exists($file_path.$file_name)) {
			return false;
		}

		// Return the filename
		return $file_name;
	}

	function process() {
		global $LNG;

		// Prevents bypassing the FILTER_VALIDATE_EMAIL
		$this->email = htmlspecialchars($this->email, ENT_QUOTES, 'UTF-8');

		$arr = $this->validate_values(); // Must be stored in a variable before executing an empty condition
		if(empty($arr)) { // If there is no error message then execute the query;
			$this->password = password_hash($this->password, PASSWORD_DEFAULT);
			$query = $this->query();
			if($query) {
				return $query;
			}

			// Set a session and log-in the user
			$_SESSION['username'] = $this->username;
			$_SESSION['password'] = $this->password;

			//Redirect the user to his personal profile
			//header("Location: ".$this->url."/something");

			// Return (int) 1 if everything was validated
			$x = 1;

			// return $LNG['user_success'];
		} else { // If there is an error message
			foreach($arr as $err) {
				return notificationBox('error', $LNG["$err"], 1); // Return the error value for translation file
			}
		}
		return $x;
	}

	function verify_if_user_exist() {
		$query = sprintf("SELECT `username` FROM `users` WHERE `username` = '%s'", $this->db->real_escape_string(mb_strtolower($this->username)));
		$result = $this->db->query($query);

		return ($result->num_rows == 0 && !in_array(mb_strtolower($this->username), array('about', 'likes', 'friends', 'groups', 'pages', 'delete', 'search', 'messages'))) ? 0 : 1;
	}

	function verify_accounts_per_ip() {
		if($this->accounts_per_ip) {
			$query = $this->db->query(sprintf("SELECT COUNT(`ip`) FROM `users` WHERE `ip` = '%s'", $this->db->real_escape_string(getUserIP())));

			$result = $query->fetch_row();
			if($result[0] < $this->accounts_per_ip) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function verify_if_email_exists($type = null) {
		// Type 0: Normal check
		// Type 1: Facebook check & return type
		if($type) {
			$query = sprintf("SELECT `username`, `password`, `suspended` FROM `users` WHERE `email` = '%s' AND `suspended` != 2", $this->db->real_escape_string(mb_strtolower($this->email)));
		} else {
			$query = sprintf("SELECT `email`, `suspended` FROM `users` WHERE `email` = '%s' AND `suspended` != 2", $this->db->real_escape_string(mb_strtolower($this->email)));
		}
		$result = $this->db->query($query);

		return ($result->num_rows == 0) ? 0 : $result->fetch_assoc();
	}

	function verify_captcha() {
		if($this->captcha_on) {
			if($this->captcha == "{$_SESSION['captcha']}" && !empty($this->captcha)) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

	function activate_account($salt, $username) {
		if($salt == 'resend') {
			global $LNG;
			$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `username` = '%s' AND `suspended` = 2", $this->db->real_escape_string($username)));
			$result = $query->fetch_assoc();

			// Check if an activation email has been sent in the last day
			if(date("Y-m-d", strtotime($result['date'])) < date("Y-m-d")) {
				global $LNG;
				$salt = generateSalt();

				// Activate the account
				$this->db->query(sprintf("UPDATE `users` SET `salted` = '%s', `date` = '%s' WHERE `username` = '%s' AND `suspended` = 2", $salt, date("Y-m-d H:i:s"), $this->db->real_escape_string($username)));

				// Send activate account email
				sendMail($result['email'], sprintf($LNG['ttl_confirm_email']), sprintf($LNG['confirm_email'], $result['username'], $this->title, $this->url.'/index.php?a=welcome&activate='.$salt.'&username='.$result['username'], $this->url, $this->title), $this->site_email);
				return notificationBox('info', $LNG['re_activate_sent'], 1);
			} else {
				return notificationBox('error', $LNG['re_activate_already'], 1);
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `username` = '%s' AND `salted` = '%s' AND `suspended` = 2", $this->db->real_escape_string($username), $this->db->real_escape_string($salt)));
			$result = $query->fetch_assoc();
			if($query->num_rows) {
				// Activate the account
				$this->db->query(sprintf("UPDATE `users` SET `salted` = '', `suspended` = 0 WHERE `username` = '%s'", $this->db->real_escape_string($username)));

				// Delete any pending accounts
				$this->db->query(sprintf("DELETE FROM `users` WHERE `email` = '%s' AND `suspended` = 2", $this->db->real_escape_string($result['email'])));

				global $LNG;
				return notificationBox('success', $LNG['account_activated'], 1);
			}
		}
	}

	function validate_values() {
		// Create the array which contains the Language variable
		$error = array();

		// Define the Language variable for each type of error
		if($this->verify_accounts_per_ip() == false) {
			$error[] = 'user_limit';
		}
		if($this->verify_if_user_exist() !== 0) {
			$error[] .= 'user_exists';
		}
		if($this->verify_if_email_exists() !== 0) {
			$error[] .= 'email_exists';
		}
		if(empty($this->username) || empty($this->password) || empty($this->email) || empty($this->confirmpassword)) {
			$error[] .= 'all_fields';
		}
        if (empty($this->agreement)) {
            $error[] .= 'agreement_required';
        }
		if(strlen($this->password) < 6) {
			$error[] .= 'password_too_short';
		}
		if(!ctype_alnum($this->username)) {
			$error[] .= 'user_alnum';
		}
		if(strlen($this->username) <= 2 || strlen($this->username) >= 33) {
			$error[] .= 'user_too_short';
		}
		if ($this->password !== $this->confirmpassword) {
			$error[] .= 'confirm_password';
		 }
		if(!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
			$error[] .= 'invalid_email';
		}
		if($this->verify_captcha() == false) {
			$error[] .= 'invalid_captcha';
		}

		$exp = explode('@', $this->email);
		if(!in_array(end($exp), explode(',', str_replace(', ', ',', $this->email_provider))) && $this->email_provider) {
			$error[] .= 'invalid_email';
		}

		return $error;
	}

	function query() {
		if(isset($this->email_confirmation) && $this->email_confirmation) {
			$salt = generateSalt();
			$suspended = 2;
		} else {
			$salt = '';
			$suspended = '0';
		}

		$query = sprintf("INSERT INTO `users` (`username`, `password`, `first_name`, `last_name`, `email`, `date`, `image`, `privacy`, `cover`, `verified`, `online`, `salted`, `suspended`, `ip`, `notificationl`, `notificationc`, `notifications`, `notificationd`, `notificationf`, `notificationg`, `notificationx`, `notificationp`, `notificationm`, `email_comment`, `email_like`, `email_new_friend`, `email_page_invite`, `email_group_invite`, `email_mention`, `sound_new_notification`, `sound_new_chat`, `private`,`refer`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', 'default.png', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s','%s');", $this->db->real_escape_string(mb_strtolower($this->username)), $this->password, $this->db->real_escape_string($this->first_name), $this->db->real_escape_string($this->last_name), $this->db->real_escape_string($this->email), date("Y-m-d H:i:s"), (isset($this->profile_image) ? $this->profile_image : 'default.png'), 1, $this->verified, time(), $salt, $suspended, $this->db->real_escape_string(getUserIp()), 1, 1, 1, 1, 1, 1, 1, 1, 1, $this->email_comment, $this->email_like, $this->email_new_friend, $this->email_page_invite, $this->email_group_invite, $this->email_mention, 1, 1, 2,$this->refer);
		$this->db->query($query);

		// If the account needs to be activated
		if(isset($this->email_confirmation) && $this->email_confirmation) {
			global $LNG;
			// Send activate account email
			sendMail($this->email, sprintf($LNG['ttl_confirm_email']), sprintf($LNG['confirm_email'], $this->username, $this->title, $this->url.'/index.php?a=welcome&activate='.$salt.'&username='.$this->username, $this->url, $this->title), $this->site_email);

			return notificationBox('info', $LNG['activate_email'], 1);
		} else {
			// Delete any previously pending confirmation accounts
			$this->db->query(sprintf("DELETE FROM `users` WHERE `email` = '%s' AND `suspended` = 2", $this->db->real_escape_string($this->email)));
		}
	}
}

class User {
	public $db; 		// Database Property
	public $url; 		// Installation URL Property
	public $username;	// Username Property
	public $password;	// Password Property
	public $remember;	// Option to remember the usr / pwd (_COOKIE) Property
	public $token;		// The Remember Me token

   /**
    * The authentication process
	*
	* @param	string	$type	0: checks if the user is already logged-in; 1: log-in form process
    */
	function auth($type = null) {
		global $LNG;

		if(isset($_COOKIE['username']) && isset($_COOKIE['userToken'])) {
			$this->username = $_COOKIE['username'];
			$auth = $this->get(1);

			if($auth['username']) {
				$logged = true;
			} else {
				$logged = false;
			}
		} elseif(isset($_SESSION['username']) && isset($_SESSION['password'])) {
			$this->username = $_SESSION['username'];
			$this->password = $_SESSION['password'];
			$auth = $this->get();

			if($this->password == $auth['password']) {
				$logged = true;
			} else {
			    $logged = false;
            }
		} elseif($type) {
			$auth = $this->get();

			if(!empty($auth['password']) && password_verify($this->password, $auth['password'])) {
				if($auth['suspended'] == 2) {
					return sprintf($LNG['account_not_activated'], $this->url.'/index.php?a=welcome&activate=resend&username='.$this->username);
				} elseif($auth['suspended'] == 1) {
					return $LNG['account_suspended'];
				}

				if($this->remember == 1) {
					setcookie("username", $auth['username'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
					setcookie("userToken", $auth['login_token'], time() + 30 * 24 * 60 * 60, COOKIE_PATH);
				}
				$_SESSION['username'] = $auth['username'];
				$_SESSION['password'] = $auth['password'];

				$logged = true;
				session_regenerate_id();
			} else {
				return $LNG['invalid_user_pw'];
			}
		}

		if(isset($logged) && $logged == true) {
			return $auth;
		} elseif(isset($logged) && $logged == false) {
			$this->logOut();
			return $LNG['invalid_user_pw'];
		}

		return false;
	}

	function get($type = null) {
		if($type) {
			$extra = sprintf(" AND `login_token` = '%s'", $this->db->real_escape_string($_COOKIE['userToken']));
		} else {
			$extra = '';
		}
		// If the username input string is an e-mail, switch the query
		if(filter_var($this->db->real_escape_string($this->username), FILTER_VALIDATE_EMAIL)) {
			$query = sprintf("SELECT * FROM `users` WHERE `email` = '%s'%s", $this->db->real_escape_string($this->username), $extra);
		} else {
			$query = sprintf("SELECT * FROM `users` WHERE `username` = '%s'%s", $this->db->real_escape_string($this->username), $extra);
		}

		// If the query can't be executed (e.g: use of special characters in inputs)
		if(!$result = $this->db->query($query)) {
			return 0;
		}

		$user = $result->fetch_assoc();

		return $user;
	}

	function logOut($rt = null) {
		if($rt == true) {
			$this->resetToken();
		}
		setcookie("userToken", '', time()-3600, COOKIE_PATH);
		setcookie("username", '', time()-3600, COOKIE_PATH);
		unset($_SESSION['username']);
		unset($_SESSION['password']);
		unset($_SESSION['token_id']);
	}

	function resetToken() {
		$this->db->query(sprintf("UPDATE `users` SET `login_token` = '%s' WHERE `username` = '%s'", generateSalt(), $this->db->real_escape_string($this->username)));
	}
}

class Admin {
	public $db; 		// Database Property
	public $url; 		// Installation URL Property
	public $username;	// Username Property
	public $password;	// Password Property

	/**
     * Select an admin
     *
     * @param	int     $type   Switch the query between verification and retrieving
     * @return	array
     */
    public function get($type = null) {
		$query = sprintf("SELECT * FROM `admin` WHERE `username` = '%s'", $this->db->real_escape_string($this->username));

		$result = $this->db->query($query);

		// If no admin account has been found
		if($result->num_rows == 0) {
			// Check the user is a moderator
			if(filter_var($this->db->real_escape_string($this->username), FILTER_VALIDATE_EMAIL)) {
				$query = sprintf("SELECT * FROM `users` WHERE `email` = '%s' AND `user_group` = 1 AND `suspended` = 0", $this->db->real_escape_string($this->username));
			} else {
				$query = sprintf("SELECT * FROM `users` WHERE `username` = '%s' AND `user_group` = 1 AND `suspended` = 0", $this->db->real_escape_string($this->username));
			}

			$result = $this->db->query($query);
			if($result->num_rows == 0) {
				return 0;
			}
		}

		$output = $result->fetch_assoc();

        return $output;
    }

	/**
     * Check whether the user can be authed or not
     *
     * @return	array | bool
     */
    function auth() {
        // If the user has previously been authenticated
        if(isset($_SESSION['adminUsername']) && isset($_SESSION['adminPassword'])) {
            $this->username = $_SESSION['adminUsername'];
            $this->password = $_SESSION['adminPassword'];
            $auth = $this->get(1);

            if($this->password = $auth['password']) {
				$logged = true;
            } else {
                return false;
            }
        }
        // If the user is authenticating
        else {
            $auth = $this->get(0);

            // Set the sessions
            $_SESSION['adminUsername'] = $this->username;

            if(isset($auth['password']) && password_verify($this->password, $auth['password'])) {
                $_SESSION['adminPassword'] = $auth['password'];

                // If the user is a moderator, authenticate him as a user too
                if(isset($auth['user_group']) && $auth['user_group'] == 1) {
                    $log = new User();
                    $log->db = $this->db;
                    $log->username = $_SESSION['adminUsername'];
                    $log->password = $this->password;
                    $x = $log->auth(1);

                    if(!is_array($x)) {
                        return false;
                    }
                }
                $logged = true;
				session_regenerate_id();
            }
        }

        if(isset($logged)) {
            $_SESSION['is_admin'] = true;
            return $auth;
        }

        return false;
    }

    /**
     * @param   string  $password
     */
    function setPassword($password) {
        $_SESSION['adminPassword'] = password_hash($password, PASSWORD_DEFAULT);
    }

	function logOut() {
		unset($_SESSION['adminUsername']);
        unset($_SESSION['adminPassword']);
        unset($_SESSION['is_admin']);
		unset($_SESSION['token_id']);
	}
}

class updateSettings {
	public $db;		// Database Property
	public $url;	// Installation URL Property

	function validate_password($password) {
		$query = $this->db->query(sprintf("SELECT `password` FROM `admin` WHERE `username` = '%s'", $this->db->real_escape_string($_SESSION['adminUsername'])));
		$result = $query->fetch_assoc();

		if(password_verify($_POST['current_password'], $result['password'])) {
			return 1;
		}
		return 0;
	}

	function truncate_data($data) {
		// Select the columns
		$query = $this->db->query("SHOW COLUMNS FROM `settings`");

		while($result = $query->fetch_assoc()) {
		    $output = [];
			foreach($data as $key => $val) {
				// If the data matches the column and the column type is varchar
				if($result['Field'] == $key && substr($result['Type'], 0, 8) === 'varchar(') {
					// Strip out any extra characters that exceed the maximum field length
					$output[$key] = substr($val, 0, filter_var($result['Type'], FILTER_SANITIZE_NUMBER_INT));
				}
				if($result['Field'] == $key && (substr($result['Type'], 0, 4) === 'int(' || substr($result['Type'], 0, 8) === 'tinyint(')) {
					// Strip out any extra characters that exceed the maximum field length
					$output[$key] = intval($val);
				}
			}
		}

		return $output;
	}

	function query_array($table, $data) {
		// Verify if the user has a valid token
		if($data['token_id'] == $_SESSION['token_id']) {
			unset($data['token_id']);

			// If a logo has been selected, and the file was uploaded with no error and the logo is in PNG format
			if(isset($_FILES['logo']) && $_FILES['logo']['error'] == 0 && pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION) == 'png') {
				global $CONF;

				move_uploaded_file($_FILES['logo']['tmp_name'], __DIR__ . '/../'.$CONF['theme_url'].'/images/logo.png');

				// Set a flag to notify that the logo has been changed
				$logo = true;
			}

			// Truncate any extra characters
			$data = array_merge($data, $this->truncate_data($data));

			// Get the columns of the query-ed table
			$available = $this->getColumns($table);

			if($table == 'admin') {
				if(isset($data['password']) && !isset($data['current_password']) || isset($data['current_password']) && !$this->validate_password($data['current_password'])) {
					return 2;
				}

				if(isset($data['password']) && strlen($data['password']) < 6) {
					return 4;
				}

				if(isset($data['password']) && $data['password'] !== $data['repeat_password']) {
					return 3;
				}

				unset($data['repeat_password'], $data['current_password']);
			}

			foreach ($data as $key => $value) {
				// Check if all arrays introduced are available table fields
				if(!array_key_exists($key, $available)) {
					$x = 1;
					return 0;
				}
			}

			// If all array keys are valid database columns
            if(isset($x) == false) {
				foreach ($data as $column => $value) {
					$columns[] = sprintf("`%s` = '%s'", $column, $this->db->real_escape_string($value));
				}
				$column_list = implode(',', $columns);

				// Prepare the database for specific page
				if($table == 'admin') {
					// Prepare the statement
					$stmt = $this->db->prepare("UPDATE `$table` SET `password` = ? WHERE `username` = ?");
					$password = password_hash($data['password'], PASSWORD_DEFAULT);
					$stmt->bind_param("ss", $password, $_SESSION['adminUsername']);
					$_SESSION['adminPassword'] = $password;
				} else {
					// Prepare the statement
					$stmt = $this->db->prepare("UPDATE `$table` SET $column_list");
				}

				// Execute the statement
				$stmt->execute();

				// Save the affected rows
				$affected = $stmt->affected_rows;

				// Close the statement
				$stmt->close();

				// If there was anything affected return 1
				return ($affected || isset($logo)) ? 1 : 0;
			}
		} else {
			return 0;
		}
	}

	function getColumns($table) {
		if($table == 'admin') {
			$query = $this->db->query("SHOW columns FROM `$table` WHERE Field NOT IN ('id', 'username')");
		} else {
			$query = $this->db->query("SHOW columns FROM `$table`");
		}
		// Define an array to store the results
		$columns = array();

		// Fetch the results set
		while ($row = $query->fetch_array()) {
			// Store the result into array
			$columns[] = $row[0];
		}

		// Return the array;
		return array_flip($columns);
	}

	function getThemes() {
		global $CONF, $LNG;

		$themes = scandir('./'.$CONF['theme_path'].'/');

		$output = '';
		foreach($themes as $theme) {
			if($theme != '.' && $theme != '..' && $theme != 'index.html' && file_exists('./'.$CONF['theme_path'].'/'.$theme.'/info.php')) {
				$allowedThemes[] = $theme;
				include('./'.$CONF['theme_path'].'/'.$theme.'/info.php');

				if($CONF['theme_name'] == $theme) {
					$state = '<div class="users-button button-active"><a>'.$LNG['active'].'</a></div>';
				} else {
					$state = '<div class="users-button button-normal"><a href="'.$CONF['url'].'/index.php?a=admin&b=themes&theme='.$theme.'&token_id='.$_SESSION['token_id'].'">'.$LNG['activate'].'</a></div>';
				}

				if(file_exists('./'.$CONF['theme_path'].'/'.$theme.'/icon.png')) {
					$image = '<img src="'.$CONF['url'].'/'.$CONF['theme_path'].'/'.$theme.'/icon.png">';
				}  else {
					$image = '';
				}
				$output .= '<div class="users-container">
					<div class="message-content">
						<div class="message-inner">
							'.$state.'
							<div class="message-avatar">
								<a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">
									'.$image.'
								</a>
							</div>
							<div class="message-top">
								<div class="message-author">
									<a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$name.'</a> '.$version.'
								</div>
								<div class="message-time">
									'.$LNG['by'].': <a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$author.'</a>
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
		}

		return array($output, $allowedThemes);
	}

	function getLanguages() {
		global $CONF, $LNG, $settings;

		$languages = scandir('./languages/');

		$LNGO = $LNG;
		$by = $LNG['by'];
		$default = $LNG['default'];
		$make = $LNG['make_default'];

		$output = '';
		foreach($languages as $language) {
			if($language != '.' && $language != '..' && substr($language, -4, 4) == '.php') {
				$language = substr($language, 0, -4);
				$allowedLanguages[] = $language;

				include('./languages/'.$language.'.php');

				if($settings['language'] == $language) {
					$state = '<div class="users-button button-active"><a>'.$default.'</a></div>';
				} else {
					$state = '<div class="users-button button-normal"><a href="'.$CONF['url'].'/index.php?a=admin&b=languages&language='.$language.'&token_id='.$_SESSION['token_id'].'">'.$make.'</a></div>';
				}

				$output .= '<div class="users-container">
					<div class="message-content">
						<div class="message-inner">
							'.$state.'
							<div class="message-top message-no-avatar">
								<div class="message-author">
									<a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$name.'</a>
								</div>
								<div class="message-time">
									'.$by.': <a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$author.'</a>
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
		}

		$LNG = $LNGO;

		return array($output, $allowedLanguages);
	}

	function getInfoPages() {
		global $CONF, $LNG;

		$pages = $this->db->query("SELECT * FROM `info_pages` ORDER BY `id` ASC");

		$output = '';
		while($row = $pages->fetch_assoc()) {
			$row['content'] = skin::parse($row['content']);
			$output .= '<div class="users-container">
				<div class="message-content">
					<div class="message-inner">
						<div class="users-button button-normal"><a href="'.$CONF['url'].'/index.php?a=admin&b=info_pages&id='.$row['id'].'" rel="loadpage">'.$LNG['edit'].'</a></div>
						<div class="message-top message-no-avatar">
							<div class="message-author">
								<a href="'.permalink($CONF['url'].'/index.php?a=info&b='.$row['url']).'" target="_blank">'.skin::parse($row['title']).'</a>
							</div>
							<div class="message-time">
								'.((strlen($row['content']) > 65) ? substr(strip_tags($row['content']), 0, 65).'...' : strip_tags($row['content'])).'
							</div>
						</div>
					</div>
				</div>
			</div>';
		}

		return $output;
	}

	function createInfoPage($values, $type) {
		global $CONF;
		// Type 0: Create page
		// Type 1: Update page
		if($values['token_id'] != $_SESSION['token_id']) {
			return false;
		}

		// Type 1: Edit the page
		global $LNG;
		$values['page_title'] = substr(strip_tags($values['page_title']), 0, 64);
		$values['page_url'] = substr(htmlspecialchars(strip_tags($values['page_url'])), 0, 64);
		$values['page_public'] = ($values['page_public'] == 1 ? 1 : 0);

		// Verify URL
		$checkUrl = $this->db->query(sprintf("SELECT `id`, `url` FROM `info_pages` WHERE `url` = '%s'", $this->db->real_escape_string($values['page_url'])));

		$resultUrl = $checkUrl->fetch_assoc();

		if(empty($values['page_title']) || empty($values['page_url']) || empty($values['page_content'])) {
			$error = $LNG['all_fields'];
		}

		if($type) {
			// Check if the URL already exists on another page
			if($checkUrl->num_rows && $resultUrl['id'] != $_GET['id']) {
				$error = $LNG['url_exists'];
			}
		} else {
			if($checkUrl->num_rows) {
				$error = $LNG['url_exists'];
			}
		}

		if(isset($error)) {
			return notificationBox('error', $error);
		}

		if($type) {
			// Prepare the statement
			$stmt = $this->db->prepare("UPDATE `info_pages` SET `title` = ?, `url` = ?, `public` = ?, `content` = ? WHERE `id` = ?");
			$stmt->bind_param('sssss', $values['page_title'], $values['page_url'], $values['page_public'], $values['page_content'], $_GET['id']);

			// Execute the statement
			$stmt->execute();

			// Save the affected rows
			$affected = $stmt->affected_rows;

			$stmt->close();

			if($affected) {
				return notificationBox('success', $LNG['settings_saved']);
			} else {
				return notificationBox('info', $LNG['nothing_changed']);
			}
		} else {
			$this->db->query(sprintf("INSERT INTO `info_pages` (`title`, `url`, `public`, `content`) VALUES ('%s', '%s', '%s', '%s')",
			$this->db->real_escape_string($values['page_title']),
			$this->db->real_escape_string($values['page_url']),
			$this->db->real_escape_string($values['page_public']),
			$this->db->real_escape_string($values['page_content'])));

			header("Location: ".permalink($CONF['url'].'/index.php?a=info&b='.$values['page_url']));
		}
	}

	function deleteInfoPage($id) {
		// Prepare the statement
		$stmt = $this->db->prepare("DELETE FROM `info_pages` WHERE `id` = ?");
		$stmt->bind_param('s', $id);
		$stmt->execute();
		$affected = $stmt->affected_rows;
		$stmt->close();
		return ($affected ? 1 : 0);
	}

	function getPlugins() {
		global $CONF, $LNG;

		$listplugins = loadPlugins($this->db);

		$active = $allowedPlugins = [];
		foreach($listplugins as $currplugin) {
			$active[] = $currplugin['name'];
		}

		$plugins = scandir('./plugins/');

		$output = '';
		foreach($plugins as $plugin) {
			if($plugin != '.' && $plugin != '..' && file_exists('./'.$CONF['plugin_path'].'/'.$plugin.'/info.php')) {
				$allowedPlugins[] = $plugin;
				include('./'.$CONF['plugin_path'].'/'.$plugin.'/info.php');

				$state = '';

				if(in_array($plugin, $active)) {
					$state .= '<div class="users-button button-active"><a href="'.$CONF['url'].'/index.php?a=admin&b=plugins&plugin='.$plugin.'&plugin_type='.$type.'&plugin_priority='.$priority.'&token_id='.$_SESSION['token_id'].'">'.$LNG['deactivate'].'</a></div>';
					// Check if there is any settings page for the plugin
					if(file_exists(__DIR__ .'/../'.$CONF['plugin_path'].'/'.$plugin.'/'.$plugin.'_settings.php')) {
						$state .= '<div class="users-button button-normal"><a href="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$plugin.'" rel="loadpage">'.$LNG['settings'].'</a></div>';
					}
				} else {
					$state = '<div class="users-button button-normal"><a href="'.$CONF['url'].'/index.php?a=admin&b=plugins&plugin='.$plugin.'&plugin_type='.$type.'&plugin_priority='.$priority.'&activated=true&token_id='.$_SESSION['token_id'].'">'.$LNG['activate'].'</a></div>';
				}

				if(file_exists('./'.$CONF['plugin_path'].'/'.$plugin.'/icon.png')) {
					$image = '<img src="'.$CONF['url'].'/'.$CONF['plugin_path'].'/'.$plugin.'/icon.png">';
				}  else {
					$image = '';
				}
				$output .= '<div class="users-container">
					<div class="message-content">
						<div class="message-inner">
							'.$state.'
							<div class="message-avatar">
								<a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">
									'.$image.'
								</a>
							</div>
							<div class="message-top">
								<div class="message-author">
									<a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$name.'</a> '.$version.'
								</div>
								<div class="message-time">
									'.$LNG['by'].': <a href="'.$url.'" target="_blank" title="'.$LNG['author_title'].'">'.$author.'</a>
								</div>
							</div>
						</div>
					</div>
				</div>';
			}
		}
		return array($output, $allowedPlugins);
	}

	function activatePlugin($name, $values) {
		global $CONF;
		if($_GET['token_id'] == $_SESSION['token_id']) {
			// Name: Plugin name
			// Type: The plugin type

			$query = $this->db->query(sprintf("SELECT * FROM `plugins` WHERE `name` = '%s'", $this->db->real_escape_string($name)));

			$result = $query->fetch_assoc();

			$fp = __DIR__ .'/../'.$CONF['plugin_path'].'/'.$name.'/'.$name;

			if($result['name']) {
				if(isset($_GET['activated']) && $_GET['activated']) return false;
				$this->db->query(sprintf("DELETE FROM `plugins` WHERE `name` = '%s'", $this->db->real_escape_string($name)));
				if(file_exists($fp.'_deactivate.php')) {
					require_once($fp.'_deactivate.php');
					call_user_func($name.'_deactivate');
				}
			} else {
				$this->db->query(sprintf("INSERT INTO `plugins` (`name`, `type`, `priority`) VALUES ('%s', '%s', '%s')", $this->db->real_escape_string($name), $this->db->real_escape_string($values['type']), $this->db->real_escape_string($values['priority'])));
				if(file_exists($fp.'_activate.php')) {
					require_once($fp.'_activate.php');
					call_user_func($name.'_activate');
				}
			}
		}
	}
}

class updateUserSettings {
	public $db;		// Database Property
	public $url;	// Installation URL Property
	public $id;		// Logged in user id

	function validate_password($password) {
		$query = $this->db->query(sprintf("SELECT `password` FROM `users` WHERE `idu` = '%s'", $this->id));
		$result = $query->fetch_assoc();

		if(password_verify($password, $result['password'])) {
			return 1;
		}
		return 0;
	}

	function validate_inputs($data) {
		if(isset($data['password']) && !isset($data['current_password']) || isset($data['current_password']) && !$this->validate_password($data['current_password'])) {
			return array('wrong_current_password');
		}

		if(isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
			return array('valid_email');
		}

		if(isset($data['website']) && ((!filter_var($data['website'], FILTER_VALIDATE_URL) && !empty($data['website'])) || (substr($data['website'], 0, 7) != 'http://' && substr($data['website'], 0, 8) != 'https://' && !empty($data['website'])))) {
			return array('valid_url');
		}

		if(isset($data['email']) && $this->verify_if_email_exists($this->id, $data['email'])) {
			return array('email_exists');
		}

		if(isset($data['country']) && !countries(0, $data['country'])) {
			return array('valid_country');
		}

		if(isset($data['bio']) && strlen($data['bio']) > 160) {
			return array('bio_description', 160);
		}

		if(isset($data['password']) && strlen($data['password']) < 6) {
			return array('password_too_short');
		}

		if(isset($data['password']) && $data['password'] !== $data['repeat_password']) {
			return array('password_not_match');
		}
	}

	function truncate_data($data) {
		// Select the columns
		$query = $this->db->query("SHOW COLUMNS FROM `users`");

		while($result = $query->fetch_assoc()) {
		    $output = [];
			foreach($data as $key => $val) {
				// If the data matches the column and the column type is varchar
				if($result['Field'] == $key && substr($result['Type'], 0, 8) === 'varchar(') {
					// Strip out any extra characters that exceed the maximum field length
					$output[$key] = substr($val, 0, filter_var($result['Type'], FILTER_SANITIZE_NUMBER_INT));
				}
			}
		}

        return $output;
	}

	function query_array($table, $data) {
		global $LNG;
		// Verify if the user has a valid token
		if(isset($data['token_id']) && $data['token_id'] == $_SESSION['token_id']) {
			// Truncate any extra characters
			foreach($data as $key => $val) {
				if($key != 'password' && $key != 'current_password' && $key != 'repeat_password') {
					$data[$key] = htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
				}
			}

			$data = array_merge($data, $this->truncate_data($data));

			// Validate the inputs
			$validate = $this->validate_inputs($data);
			if($validate) {
				return notificationBox('error', sprintf($LNG["{$validate[0]}"], (isset($validate[1]) ? $validate[1] : null)));
			}
			// If the birthdate data is set
			if(isset($data['day']) && isset($data['month']) && isset($data['year'])) {
				// If the user has selected any birthdate
				if(!empty($data['day']) && !empty($data['month']) && !empty($data['year'])) {
					$data['born'] = date("Y-m-d", mktime(0, 0, 0, $data['month'], $data['day'], $data['year']));
				} else {
					$data['born'] = NULL;
				}
			}

			// Unset unused values
			unset($data['day'], $data['month'], $data['year'], $data['repeat_password'], $data['current_password'], $data['token_id']);

			// Send the suspend notification
			if(isset($data['suspended']) && $data['suspended'] == 1) {
				// Send suspended account email
				sendMail($data['email'], sprintf($LNG['ttl_suspended_account_mail']), sprintf($LNG['suspended_account_mail'], realName($data['username'], $data['first_name'], $data['last_name']), $this->url, $this->title), $this->email);
			}

			// Unset the username (this is being set in the admin panel and used when sending emails)
			unset($data['username']);

			// Get the columns of the query-ed table
			$available = $this->getColumns($table);

			foreach ($data as $key => $value) {
				// Check if password array key exist and set a variable if so
				if($key == 'password') {
					$password = true;
				}

				// Check if all arrays introduced are available table fields
				if(!array_key_exists($key, $available)) {
					$x = 1;
					break;
				}
			}

			// If the password array key exists, encrypt the password
			if(isset($password)) {
				$data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
                $_SESSION['password'] = $data['password'];
			}

			// If all array keys are valid database columns
			if(isset($x) == false) {
				foreach ($data as $column => $value) {
					if($column == 'born' && empty($value)) {
						$columns[] = sprintf("`%s` = NULL", $column);
					} else {
						$columns[] = sprintf("`%s` = '%s'", $column, $this->db->real_escape_string($value));
					}
				}
				$column_list = implode(',', $columns);

				// Prepare the statement
				$stmt = $this->db->prepare("UPDATE `$table` SET $column_list WHERE `idu` = '{$this->id}'");

				// Execute the statement
				$stmt->execute();

				// Save the affected rows
				$affected = $stmt->affected_rows;

				// Close the statement
				$stmt->close();

				// If there was anything affected return 1
				if($affected) {
					return notificationBox('success', $LNG['settings_saved']);
				} else {
					return notificationBox('info', $LNG['nothing_changed']);
				}
			}
		} else {
			return notificationBox('info', $LNG['nothing_changed']);
		}
	}

	function getColumns($table) {
		$query = $this->db->query("SHOW columns FROM `$table` WHERE Field NOT IN ('idu', 'date', 'salted')");

		// Define an array to store the results
		$columns = array();

		// Fetch the results set
		while ($row = $query->fetch_array()) {
			// Store the result into array
			$columns[] = $row[0];
		}

		// Return the array;
		return array_flip($columns);
	}

	function verify_if_email_exists($id, $email) {
		$query = sprintf("SELECT `idu`, `email` FROM `users` WHERE `idu` <> '%s' AND `email` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string(mb_strtolower($email)));
		$result = $this->db->query($query);

		return ($result->num_rows == 0) ? 0 : 1;
	}

	function getSettings() {
		$result = $this->db->query(sprintf("SELECT * FROM `users` WHERE `idu` = '%s'", $this->db->real_escape_string($this->id)));

		return $result->fetch_assoc();
	}

	function getBlockedUsers($start = null) {
		global $LNG;

		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `blocked`.`id` < \''.$this->db->real_escape_string($start).'\'';
		}

		$query = $this->db->query(sprintf("SELECT * FROM `blocked`, `users` WHERE `blocked`.`by` = '%s' AND `blocked`.`uid` = `users`.`idu` %s ORDER BY `id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $start, ($this->per_page + 1)));

		// Declare the rows array
		$rows = [];
		$output = $loadmore = '';
		while($row = $query->fetch_assoc()) {
			// Store the result into the array
			$rows[] = $row;
		}

		// Decide whether the load more will be shown or not
		if(array_key_exists($this->per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		foreach($rows as $row) {
			$output .= '<div class="users-container">
						<div class="message-content" id="blocked'.$row['idu'].'">
							<div class="message-inner">
								<div class="users-button button-normal"><a onclick="doBlock('.$row['idu'].', 1)">'.$LNG['unblock'].'</a></div>
								<div class="message-avatar" id="avatar'.$row['id'].'">
									<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
										<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
									</a>
								</div>
								<div class="message-top">
									<div class="message-author" id="author13" rel="loadpage">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>
									</div>
									<div class="message-time">
										'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
									</div>
								</div>
							</div>
						</div>
					</div>';
			$start = $row['id'];
		}

		if($loadmore) {
			$output .= '<div class="load_more" id="more_users"><a onclick="loadBlocked(\''.($start).'\')" id="load-more">'.$LNG['view_more_messages'].'</a></div></div>';
		}
		return $output;
	}
	
	function inviteUsers() {
        $query = $this->db->query(sprintf("SELECT `username` FROM `users` WHERE `idu` = '%s'", $this->id));
		$result = $query->fetch_assoc();
		$querie = $this->db->query(sprintf("SELECT	count(`refer`) FROM `users` WHERE	`refer`	='%s'",$result['username']));
		$total	= $querie->fetch_array();
		

			$output .= '
			
			<div class="copy">
					<strong class="h3">Invite Link:</strong>
			   		<form class="form">
				 		<input class="input" type="text" value="'.permalink($this->url.'/index.php?a=welcome&refer='.$result['username']).'">
				 		<button class="button" type="button">Copy</button>
			   		</form>
			   		<p>You have invited <strong>'.$total[0].'</strong> Friend(s).</P>
			 	</div> 
    
			
			';
		return $output;
	}
	
	
}
class recover {
	public $db;			// Database Property
	public $url;		// Installation URL Property
	public $username;	// The username to recover

	function checkUser() {
		// Query the database and check if the username exists
		if(filter_var($this->db->real_escape_string($this->username), FILTER_VALIDATE_EMAIL)) {
			$query = sprintf("SELECT `username`,`email` FROM `users` WHERE `email` = '%s' AND `suspended` = 0", $this->db->real_escape_string(mb_strtolower($this->username)));
		} else {
			$query = sprintf("SELECT `username`,`email` FROM `users` WHERE `username` = '%s' AND `suspended` = 0", $this->db->real_escape_string(mb_strtolower($this->username)));
		}

		$result = $this->db->query($query);

		// If a valid username is found
		if ($result->num_rows > 0) {
			// Fetch Associative values
			$assoc = $result->fetch_assoc();

			// Generate the salt for that username
			$generateSalt = $this->generateSalt($assoc['username']);

			// If the salt was generated
			if($generateSalt) {

				// Return the username, email and salted code
				return array($assoc['username'], $assoc['email'], $generateSalt);
			}
		}
	}

	function generateSalt($username) {
		// Generate the salted code
		$salt = generateSalt();

		// Prepare to update the database with the salted code
		$stmt = $this->db->prepare("UPDATE `users` SET `salted` = '{$this->db->real_escape_string($salt)}' WHERE `username` = '{$this->db->real_escape_string(mb_strtolower($username))}'");

		// Execute the statement
		$stmt->execute();

		// Save the affected rows
		$affected = $stmt->affected_rows;

		// Close the query
		$stmt->close();

		// If there was anything affected return 1
		if($affected) {
			return $salt;
		} else {
			return false;
		}
	}

	function changePassword($username, $password, $salt) {
		// Query the database and check if the username and the salted code exists
		$query = sprintf("SELECT `username` FROM `users` WHERE `username` = '%s' AND `salted` = '%s'", $this->db->real_escape_string(mb_strtolower($username)), $this->db->real_escape_string($salt));
		$result = $this->db->query($query);

		// If a valid match was found
		if ($result->num_rows > 0) {
			$password = password_hash($password, PASSWORD_DEFAULT);

			// Change the password
			$stmt = $this->db->prepare("UPDATE `users` SET `password` = '{$password}', `salted` = '' WHERE `username` = '{$this->db->real_escape_string(mb_strtolower($username))}'");

			// Execute the statement
			$stmt->execute();

			// Save the affected rows
			$affected = $stmt->affected_rows;

			// Close the query
			$stmt->close();
			if($affected) {
				return true;
			} else {
				return false;
			}
		}
	}
}
class manageUsers {
	public $db;			// Database Property
	public $url;		// Installation URL Property
	public $per_page;	// Limit per page

	function getUsers($start, $type = null) {
	    global $CONF;
		// Type 0: Get all the registered users
		// Type 1: Get all the verified users
		// Type 2: Get all the moderators
		global $LNG;
		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = 'WHERE ';
		} else {
			// Else, build up the query
			$start = 'WHERE `idu` < \''.$this->db->real_escape_string($start).'\' AND ';
		}
		$extra = '';
		$suspended = '`suspended` = 0';
		if($type == 1) {
			$extra = 'AND `verified` = 1';
		} elseif($type == 2) {
			$extra = 'AND `user_group` = 1';
		} elseif($type == 3) {
			$suspended = '`suspended` = 1';
		} else {
			$type = 0;
		}

		// Query the database and get the latest 20 users
		// If load more is true, switch the query for the live query
		$query = sprintf("SELECT * FROM `users` %s %s %s ORDER BY `idu` DESC LIMIT %s", $start, $suspended, $extra, $this->db->real_escape_string($this->per_page + 1));

		$result = $this->db->query($query);
		$rows = [];
		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		$users = $loadmore = '';

		if(array_key_exists($this->per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		foreach($rows as $row) {
			$users .= '<div class="users-container">
						<div class="message-content">
							<div class="message-inner">
								<div class="users-button button-normal"><a href="'.$this->url.'/index.php?a=admin&b=users&e='.$row['idu'].'" rel="loadpage">'.$LNG['edit'].'</a></div>
								<div class="message-avatar" id="avatar'.$row['idu'].'">
									<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
										<img src="'.$this->url.'/image.php?src='.$row['image'].'&t=a&w=50&h=50">
									</a>
								</div>
								<div class="message-top">
									<div class="message-author" id="author13" rel="loadpage">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].($row['verified'] ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'</a>
									</div>
									<div class="message-time">
										'.realName($row['email']).'
									</div>
								</div>
							</div>
						</div>
					</div>';
			$last = $row['idu'];
		}
		if($loadmore) {
			$users .= '<div class="load_more" id="more_users"><a onclick="manage_the('.$last.', 0, '.saniscape($type).')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}

		// Return the array set
		return $users;
	}

	function getUser($id, $profile = null) {
		if($profile) {
			$query = sprintf("SELECT `idu`, `username`, `email`, `first_name`, `last_name`, `location`, `website`, `bio`, `facebook`, `twitter`, `born`, `verified` FROM `users` WHERE `username` = '%s' AND `suspended` != 2", $this->db->real_escape_string($profile));
		} else {
			$query = sprintf("SELECT `idu`, `username`, `email`, `first_name`, `last_name`, `location`, `website`, `bio`, `facebook`, `twitter`, `born`, `verified` FROM `users` WHERE `idu` = '%s' AND `suspended` != 2", $this->db->real_escape_string($id));
		}
		$result = $this->db->query($query);

		// If the user exists
		if($result->num_rows > 0) {

			$row = $result->fetch_assoc();

			return $row;
		} else {
			return false;
		}
	}

	function deleteUser($id) {
		// Prepare the statement to delete the user from the database
		$stmt = $this->db->prepare("DELETE FROM `users` WHERE `idu` = '{$this->db->real_escape_string($id)}'");

		// Execute the statement
		$stmt->execute();

		// Save the affected rows
		$affected = $stmt->affected_rows;

		// Close the statement
		$stmt->close();

		// If the user was returned
		if($affected) {
			$feed = new feed();
			$feed->db = $this->db;
			$feed->id = $id;

			// Get all the liked pages by the user and subtract the like from them
			$query_likes = $this->db->query(sprintf("SELECT `post` FROM `likes` WHERE `by` = '%s' AND `type` = '2'", $this->db->real_escape_string($id)));

			while($row = $query_likes->fetch_assoc()) {
				$this->db->query(sprintf("UPDATE `pages` SET `likes` = `likes`-1 WHERE `id` = '%s'", $row['post']));
			}

			// Get all the comments by the user and subtract the comments from posts
			$query_comments = $this->db->query(sprintf("SELECT `mid` FROM `comments` WHERE `uid` = '%s'", $this->db->real_escape_string($id)));

			while($row = $query_comments->fetch_assoc()) {
				$this->db->query(sprintf("UPDATE `messages` SET `comments` = `comments`-1 WHERE `id` = '%s'", $row['mid']));
			}

			// Get all the liked pages by the user and subtract the like from them
			$query_shared = $this->db->query(sprintf("SELECT `value` FROM `messages` WHERE `uid` = '%s' AND `type` = 'shared'", $this->db->real_escape_string($id)));

			while($row = $query_shared->fetch_assoc()) {
				$this->db->query(sprintf("UPDATE `messages` SET `shares` = `shares` - 1 WHERE `id` = '%s'", $row['value']));
			}

			// Get all the groups where the user is a member of and subtract the membership from them
			$query_group = $this->db->query(sprintf("SELECT `group` FROM `groups_users` WHERE `user` = '%s' AND `status` = '1'", $this->db->real_escape_string($id)));

			while($row = $query_group->fetch_assoc()) {
				$this->db->query(sprintf("UPDATE `groups` SET `members` = `members`-1 WHERE `id` = '%s'", $row['group']));
			}

			// Delete the images from messages
			$feed->deleteMessagesImages($id);

			// Delete the images from chats
			$feed->deleteChatImages($id);

			// Delete images from comments
			$feed->deleteCommentsImages($id);

			// Get all the messages id
			$mids = $feed->getMessagesIds();

			$sids = $feed->getMessagesIds(null, null, null, $mids);

			// If there are any messages shared
			if($sids) {
				$feed->deleteShared($sids);
			}

			// Delete the likes from liked messages and comments
			$this->db->query(sprintf("UPDATE `messages` SET `likes` = `likes`-1, `time` = `time` WHERE `id` IN (SELECT `post` FROM `likes` WHERE `by` = '%s' AND `type` = 0 ORDER BY `likes` ASC)", $this->db->real_escape_string($id)));
			$this->db->query(sprintf("UPDATE `comments` SET `likes` = `likes`-1, `time` = `time` WHERE `id` IN (SELECT `post` FROM `likes` WHERE `by` = '%s' AND `type` = 1 ORDER BY `likes` ASC)", $this->db->real_escape_string($id)));

			// Delete the shared messages by other users
			$this->db->query("DELETE FROM `messages` WHERE `type` = 'shared' AND `value` IN ({$mids})");

			// Delete all the messages
			$this->db->query("DELETE FROM `messages` WHERE `uid` = '{$this->db->real_escape_string($id)}'");

			// Delete all the comments
			$this->db->query("DELETE FROM `comments` WHERE `uid` = '{$this->db->real_escape_string($id)}'");

			// Delete the likes
			$this->db->query("DELETE FROM `likes` WHERE `by` = '{$this->db->real_escape_string($id)}'");

			// Delete the likes made by other users to the user's messages
			$this->db->query("DELETE FROM `likes` WHERE `post` IN ({$mids})");

			// Delete the reports
			$this->db->query("DELETE FROM `reports` WHERE `by` = '{$this->db->real_escape_string($id)}'");

			// Delete all the friendships
			$this->db->query("DELETE FROM `friendships` WHERE `user1` = '{$this->db->real_escape_string($id)}' OR `user2` = '{$this->db->real_escape_string($id)}'");

			// Delete all the chats
			$this->db->query("DELETE FROM `chat` WHERE `from` = '{$this->db->real_escape_string($id)}' OR `to` = '{$this->db->real_escape_string($id)}'");

			// Delete all the conversation notifications
			$this->db->query("DELETE FROM `conversations` WHERE `from` = '{$this->db->real_escape_string($id)}' OR `to` = '{$this->db->real_escape_string($id)}'");

			// Delete all the blocks
			$this->db->query("DELETE FROM `blocked` WHERE `uid` = '{$this->db->real_escape_string($id)}' OR `by` = '{$this->db->real_escape_string($id)}'");

			// Delete all the notifications
			$this->db->query("DELETE FROM `notifications` WHERE `from` = '{$this->db->real_escape_string($id)}' OR `to` = '{$this->db->real_escape_string($id)}'");

			// Delete the user from groups
			$this->db->query("DELETE FROM `groups_users` WHERE `user` = '{$this->db->real_escape_string($id)}'");

			// Get the current groups created by the user
			$query = $this->db->query(sprintf("SELECT `groups`.`id`, `groups`.`cover` FROM `groups_users`, `groups` WHERE `groups_users`.`user` = '%s' AND `groups_users`.`group` = `groups`.`id` AND `permissions` = 2 ORDER BY `groups`.`id` ASC", $this->db->real_escape_string($feed->id)));

			while($rows = $query->fetch_assoc()) {
				// Delete group related things (group, group users, group messages)
				$feed->deleteGroup($rows['id'], 1);
			}

			// Get the current pages created by the user
			$query = $this->db->query(sprintf("SELECT `pages`.`id` FROM `pages` WHERE `pages`.`by` = '%s' ORDER BY `pages`.`id` ASC", $this->db->real_escape_string($feed->id)));

			while($rows = $query->fetch_assoc()) {
				// Delete group related things (group, group users, group messages)
				$feed->deletePage($rows['id'], 1);
			}

			return 1;
		} else {
			return 0;
		}
	}
}
class manageReports {
	public $db;			// Database Property
	public $url;		// Installation URL Property
	public $per_page;	// Limit per page

	function getReports($start) {
		global $LNG;
		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `reports`.`id` < \''.$this->db->real_escape_string($start).'\'';
		}
		// Query the database and get the latest 20 users
		// If load more is true, switch the query for the live query

		$query = sprintf("SELECT * FROM `reports` LEFT JOIN `users` ON `reports`.`by` = `users`.`idu` WHERE `state` = 0 %s ORDER BY `reports`.`id` DESC LIMIT %s", $start, $this->db->real_escape_string($this->per_page + 1));

		$result = $this->db->query($query);

		$rows = [];
		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

        $users = $loadmore = '';

		if(array_key_exists($this->per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		foreach($rows as $row) {
			if($row['type'] == 0) {
				$post = $row['parent'].'#comment'.$row['post'];
				$type = $LNG['rep_comment'];
			} else {
				$post = $row['post'].'#message'.$row['post'];
				$type = $LNG['message'];
			}

			$users .= '<div class="users-container" id="report'.$row['id'].'">
						<div class="message-content">
							<div class="message-inner">
								<div class="users-button button-normal"><a onclick="manage_report('.$row['id'].', '.$row['type'].', '.$row['post'].', 1)" title="'.$LNG['admin_reports_delete'].'">'.$LNG['delete'].'</a></div>
								<div class="users-button button-normal"><a onclick="manage_report('.$row['id'].', '.$row['type'].', '.$row['post'].', 0)" title="'.$LNG['admin_reports_ignore'].'">'.$LNG['ignore'].'</a></div>
								<div class="users-button button-normal"><a href="'.permalink($this->url.'/index.php?a=post&m='.$post).'" title="'.$LNG['admin_reports_view'].'" target="_blank">'.$LNG['view'].'</a></div>
								<div class="message-avatar" id="avatar'.$row['idu'].'">
									<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
										<img src="'.$this->url.'/image.php?src='.$row['image'].'&t=a&w=50&h=50">
									</a>
								</div>
								<div class="message-top">
									<div class="message-author" id="author13" rel="loadpage">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>
									</div>
									<div class="message-time">
										'.$type.'
									</div>
								</div>
							</div>
						</div>
					</div>';
			$last = $row['id'];
		}
		if($loadmore) {
			$users .= '<div class="load_more" id="more_users"><a onclick="manage_the('.$last.', 1)" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}

		// Return the array set
		return $users;
	}

	function manageReport($id, $type, $post, $kind) {
		if($kind == 1) {
			// Prepare the statement to delete the message/comment from the database
			$query = $this->db->query(sprintf("SELECT `uid` FROM `%s` WHERE `id` = '%s'", ($type ? 'messages' : 'comments'), $this->db->real_escape_string($post)));
			$row = $query->fetch_assoc();

			$feed = new feed();
			$feed->id = $row['uid'];
			$feed->db = $this->db;
			$feed->plugins = $this->plugins;
			$feed->delete($post, $type);

			$this->db->query("UPDATE `reports` SET `state` = '2' WHERE `post` = '{$this->db->real_escape_string($post)}' AND `type` = '{$this->db->real_escape_string($type)}'");
			return 1;
		} else {
			// Make the report safe
			$stmt = $this->db->prepare("UPDATE `reports` SET `state` = '1' WHERE `post` = '{$this->db->real_escape_string($post)}' AND `type` = '{$this->db->real_escape_string($type)}'");

			// Execute the statement
			$stmt->execute();

			// Save the affected rows
			$affected = $stmt->affected_rows;

			// Close the statement
			$stmt->close();

			// If the row has been affected
			return ($affected) ? 1 : 0;
		}
	}
}
class feed {
	public $db;					// Database Property
	public $url;				// Installation URL Property
	public $title;				// Installation WebSite Title
	public $email;				// Installation Default E-mail
	public $id;					// The ID of the user
	public $username;			// The username
	public $user_email;			// The email of the current username
	public $per_page;			// The per_page limit for feed
	public $c_start;			// The row where to start the nex
	public $c_per_page;			// Comments per_page limit
	public $s_per_page;			// Subscribers per page (dedicated profile page)
	public $m_per_page;			// Conversation Messages (Chat) per page
	public $time;				// The time option from the admin panel
	public $censor;				// List of censored words
	public $max_size;			// Image size allowed for upload (messages)
	public $image_format;		// Image formats allowed for upload (messages)
	public $message_length;		// The maximum message length allowed for messages/comments
	public $max_images;			// The maxium images allowed to be uploaded per message
	public $is_admin;			// The option for is_admin to show the post no matter what
	public $profile;			// The current viewed user profile
	public $profile_id;			// The profile id of the current viewed user profile
	public $profile_data;		// The public variable which holds all the data for queried user
	public $friendsArray;		// The friends list Array([value],[count])
	public $l_per_post;			// Likes per post (small thumbs)
	public $online_time;		// The amount of time an user is being kept as online
	public $chat_length;		// The maximum chat length allowed for conversations
	public $email_comment;		// The admin settings for allowing e-mails on comments to be sent
	public $email_like;			// The admin settings for allowing e-mails on likes to be sent
	public $email_new_friend;	// The admin settings for allowing e-mails on new friendship to be sent
	public $smiles;				// The admin settings for displaying smiles in messages
	public $pages_limit;		// The maximum amount of pages a user can create
	public $groups_limit;		// The maximum amount of groups a user can create

	function getMessages($query, $type, $typeVal) {
		// QUERY: Holds the query string
		// TYPE: [loadFeed, loadProfile, loadHashtags]
		// TYPEVAL: Values for the JS functions
		global $LNG, $CONF;

		// Run the query
		$result = $this->db->query($query);

		// Set the result into an array
		$rows = array();
		while($row = $result->fetch_assoc()) {
			$rows[] = $row;
		}

		// If the Feed is empty, display a welcome message
		if(empty($rows) && $type == 'loadHashtags') {
			return $this->showError('no_results');
		}

		// Define the $loadmore variable
		$loadmore = '';

		// If there are more results available than the limit, then show the Load More Comments
		if(array_key_exists($this->per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		// Define the $messages variable
		$messages = $extra = '';

		// If it's set profile, then set $profile
		if(isset($this->profile) && $this->profile) {
			$extra = ', \''.$this->profile.'\'';
		} elseif(isset($this->hashtags) && $this->hashtags) {
			$extra  = ', \''.$this->hashtags.'\'';
		}

		// Start outputting the content
		$i = 0;
		foreach($rows as $row) {
			$po = '';
			foreach($this->plugins as $plugin) {
				if(array_intersect(array("7"), str_split($plugin['type']))) {
					$po .= plugin($plugin['name'], array('message' => $row['message'], 'id' => $row['id'], 'type' => $row['type'], 'value' => $row['value'], 'user_id' => $this->id), 1);
				}
			}
			// If the request is being made from groups
			if(isset($this->group_data['id']) && $this->group_data['id']) {
				// Add the latest viewed message on the group
				if($i == 0 && isset($this->group_member_data['status']) && $this->group_member_data['status'] == 1) {
					// If the user is a member of the group
					$this->groupActivity(1, $row['id']);
				}
			}
			$time = $row['time']; $b = '';
			if($this->time == '0') {
				$time = date("c", strtotime($row['time']));
			} elseif($this->time == '2') {
				$time = $this->ago(strtotime($row['time']));
			} elseif($this->time == '3') {
				$date = strtotime($row['time']);
				$time = date('Y-m-d', $date);
				$b = '-standard';
			}

			// Define the style variable (resets the last value)
			$style = $verified = '';
			if($row['public'] == 1) {
				$public = '<div class="privacy-icons public-icon" title="'.$LNG['public'].'"></div>';
			} elseif($row['public'] == 2) {
				$public = '<div class="privacy-icons friends-icon" title="'.$LNG['friends'].'"></div>';
			} else {
				$public = '<div class="privacy-icons private-icon" title="'.$LNG['private'].'"></div>';
				$style = ' style="display: none"';
			}
			if(empty($this->username)) {
				$menu = '';
				$style = ' style="display: none"';
			} else {
				$menulist = '<a href="'.permalink($this->url.'/index.php?a=post&m='.$row['id']).'" target="_blank"><div class="message-menu-row">'.$LNG['show_in_tab'].'</div></a>
				<div class="message-menu-divider"></div>';

				if($this->username == $row['username']) {
					$menulist .= '
					<div class="message-menu-row" onclick="edit_message('.$row['id'].')" id="edit_text'.$row['id'].'">'.$LNG['edit'].'</div>
					<div class="message-menu-row" onclick="deleteModal('.$row['id'].', 1)">'.$LNG['delete'].'</div>
					'.($row['group'] || $row['page'] ? '' : '<div class="message-menu-divider"></div>
					<div class="message-menu-row" onclick="privacy('.$row['id'].', 1)">'.$LNG['public'].'</div>
					<div class="message-menu-row" onclick="privacy('.$row['id'].', 2)">'.$LNG['friends'].'</div>
					<div class="message-menu-row" onclick="privacy('.$row['id'].', 0)">'.$LNG['private'].'</div>');
				} else {
					$menulist .= '<div class="message-menu-row" onclick="report_the('.$row['id'].', 1)">'.$LNG['report'].'</div>';
				}

				$grouplist = '';
				if($row['group'] && isset($this->group_member_data) && in_array($this->group_member_data['permissions'], array(1, 2)) && $this->id != $row['idu']) {
					$grouplist = '
					<div class="message-menu-divider"></div>
					<div class="message-menu-row" onclick="group(2, '.$row['id'].', '.$row['group'].', '.$row['uid'].', \'\')">'.$LNG['delete_message'].'</div>
					<div class="message-menu-row" onclick="group(0, 0, '.$row['group'].', '.$row['uid'].', '.$row['id'].')">'.$LNG['remove_user'].'</div>
					';
				}
				$menu = '
				<div class="message-menu" onclick="messageMenu('.$row['id'].', 1)"></div>
				<div id="message-menu'.$row['id'].'" class="message-menu-container">
					'.$menulist.'
					'.$grouplist.'
				</div>';
			}

			$shared_title = $sharedMedia = $sharedContent = $group_title = $getPage = $this->page_data = '';
			if($row['group']) {
				$dataType = 2;
				// If the message is viewed from the post page
				if(isset($this->is_post_page)) {
					$getGroup = $this->db->query(sprintf("SELECT * FROM `groups` WHERE `id` = '%s'", $row['group']));
					$group = $getGroup->fetch_assoc();
					// If the group is private, check the privacy
					if($group['privacy'] && $this->is_admin == 0) {
						$this->group_member_data = $this->groupMemberData($group['id']);
						if(!$this->groupPermission($group, $this->group_member_data)) {
							header('Location: '.permalink($this->url.'/index.php?a=group&name='.$group['name']));
						}
					}
					$group_title = ' '.sprintf($LNG['group_title'], permalink($this->url.'/index.php?a=group&name='.$group['name']), $group['title']);
				}
			} elseif($row['page']) {
			    $dataType = 3;
            } elseif($this->profile) {
				$dataType = 1;
			} else {
				$dataType = 0;
			}

			if($row['type'] == 'shared') {
				$getOriginal = $this->db->query(sprintf("SELECT * FROM `messages`, `users` WHERE `messages`.`id` = '%s' AND `messages`.`uid` = `users`.`idu`", $row['value']));
				$shared = $getOriginal->fetch_assoc();

				// If the shared message is from a page
				if($shared['page']) {
					$getPage = $this->pageData(null, $shared['page']);
				}
				// If the original message is public (anyone can see it)
				if($shared['public'] == 1) {
					// Include the media output
					$sharedContent = $shared['message'];
					$sharedMedia = $this->getMessageType($shared['type'], $shared['value'], 0);
				} else {
					// If the message is private, only display half of the message's content
					$countLetters = round(strlen($shared['message']) / 2);
					$sharedContent = ($shared['message'] ? substr($shared['message'], 0, $countLetters).'...' : '');
				}

				$shared_url = (isset($getPage['name']) && $getPage['name'] ? permalink($this->url.'/index.php?a=page&name='.$getPage['name']) : permalink($this->url.'/index.php?a=profile&u='.$shared['username']));
				$shared_ttl = (isset($getPage['name']) && $getPage['name'] ? $getPage['title'] : realName($shared['username'], $shared['first_name'], $shared['last_name']));

				$shared_title = ' '.sprintf($LNG['shared_title'], $shared_url, $shared_ttl, permalink($this->url.'/index.php?a=post&m='.$row['value']));
			}
			// If is not on a "Page" page, but the post is from a page
			if(empty($this->page_data['name']) && $row['page']) {
				$this->page_data = $this->pageData(null, $row['page']);
			}
            if ($row['page']) {
                if ($this->page_data['verified'] == 1) {
                    $verified = '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_page'].'"></span>';
                }
            } else {
                if ($row['verified']) {
                    $verified = '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>';
                }
            }
			$profile_url = (isset($this->page_data['by']) && $row['idu'] == $this->page_data['by'] ? permalink($this->url.'/index.php?a=page&name='.$this->page_data['name']) : permalink($this->url.'/index.php?a=profile&u='.$row['username']));
			$profile_img = permalink((isset($this->page_data['by']) && $row['idu'] == $this->page_data['by'] ? $this->url.'/image.php?t=a&w=50&h=50&src='.$this->page_data['image'] : $this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']));
			$profile_name = (isset($this->page_data['by']) && $row['idu'] == $this->page_data['by'] ? $this->page_data['title'] : realName($row['username'], $row['first_name'], $row['last_name']));
			$page = isset($this->page_data['id']) && $this->page_data['id'] ? 1 : 0;
			$row['idu'] = isset($this->page_data['id']) && $this->page_data['id'] ? $this->page_data['id'] : $row['idu'];
			$messages .= '
			<div class="message-container last-message" id="message'.$row['id'].'" data-filter="'.str_replace('\'', '', $typeVal) .'" data-last="'.$row['id'].'" data-username="'.$this->profile.'" data-type="'.$dataType.'" data-userid="'.$row['uid'].'">
				<div class="message-content">
					<div class="message-inner">
						<div class="message-avatar" id="avatar-p-'.$row['id'].'">
							<a href="'.$profile_url.'" rel="loadpage">
								<img onmouseover="profileCard('.$row['idu'].', '.$row['id'].', 0, 0, '.$page.');" onmouseout="profileCard(0, 0, 0, 1, '.$page.');" onclick="profileCard(0, 0, 1, 1, '.$page.');" src="'.$profile_img.'">
							</a>
						</div>
						<div class="message-top">
							'.$menu.'
							<div class="message-author" id="author-p-'.$row['id'].'">
								<a href="'.$profile_url.'" rel="loadpage">'.$profile_name.$verified.'</a>'.$shared_title.$group_title.'
							</div>
							<div class="message-time">
								<span id="time-p-'.$row['id'].'"><a href="'.permalink($this->url.'/index.php?a=post&m='.$row['id']).'" rel="loadpage">
									<div class="timeago'.$b.'" title="'.$time.'">
										'.$time.'
									</div>
								</a></span><span id="privacy'.$row['id'].'">'.$public.'</span>
								<div id="message_loader'.$row['id'].'"></div>
							</div>
						</div>
						<div class="message-message" id="message_text'.$row['id'].'">
							'.nl2br($this->parseMessage($row['message'])).'
						</div>
						'.($sharedContent ? '<div class="message-message"><div class="message-shared">'.nl2br($this->parseMessage($sharedContent)).'</div></div>' : '').'
					</div>
					<div class="message-divider"></div>
					'.($sharedMedia ? $sharedMedia : $this->getMessageType($row['type'], $row['value'], $row['id'])).$po.'
					<div class="message-replies">
						<div class="message-actions"><div class="message-actions-content" id="message-action'.$row['id'].'">'.$this->getActions($row['id'], $row['likes'], $row['comments'], $row['shares']).'</div></div>
						<div class="message-replies-content" id="comments-list'.$row['id'].'">
							'.$this->getComments($row['id'], null, $this->c_start, ($this->id == $row['uid'] ? 1 : 0)).'
						</div>
					</div>
					<div class="message-comment-box-container" id="comment_box_'.$row['id'].'"'.$style.'>
						<div class="message-reply-avatar">
							'.((!empty($this->user)) ? '<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.(isset($this->page_data['by']) && $this->id == $this->page_data['by'] ? $this->page_data['image'] : $this->user['image']).'">') : '').'
						</div>
						<div class="message-comment-box-form">
							<textarea id="comment-form'.$row['id'].'" onclick="showButton('.$row['id'].')" placeholder="'.$LNG['leave_comment'].'" class="comment-reply-textarea"></textarea>
							<label for="commentimage'.$row['id'].'" class="c-w-icon c-w-icon-picture comment-image-btn" title="'.$LNG['chat_picture'].'" data-active-comment="'.$row['id'].'"></label>
						</div>
						<div class="comments-buttons">
							<div id="comments-controls'.$row['id'].'" class="comments-controls" style="display: none;">
								<div class="comment-btn button-active">
									<a id="post-comment" onclick="postComment('.$row['id'].')">'.$LNG['post'].'</a>
								</div>
								<div id="queued-comment-files'.$row['id'].'"></div>
							</div>
							<input type="file" name="commentimage" id="commentimage'.$row['id'].'" style="display: none;" accept="image/*">
						</div>
						<div class="delete_preloader" id="post_comment_'.$row['id'].'"></div>
					</div>
				</div>
			</div>';
			$start = $row['id'];
			$i++;
		}

		// If the $loadmore button is set, then show the Load More Messages button
		if($loadmore) {
			$messages .= '<div class="load_more" id="more_messages"><a onclick="'.$type.'('.$start.', '.$typeVal.''.$extra.')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}
		return array($messages, 0);
	}

	function getFeed($start, $value, $from = null) {
		// From: Load posts starting with a certain ID

		$this->friends = $this->getFriendsList();
		$this->pages = $this->getPagesList();

		if(!empty($this->friends)) {
			$this->friendsList = $this->id.','.$this->friends;
		} else {
			$this->friendsList = $this->id;
		}

		// Disable the per_page limit if $from is set
		if(is_numeric($from)) {
			$this->per_page = 9999;
			$from = 'AND `messages`.`id` > \''.$this->db->real_escape_string($from).'\'';
		} else {
			$from = '';
		}

		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `messages`.`id` < \''.$this->db->real_escape_string($start).'\'';
		}

		// Get the user feed
		if(empty($this->pages)) {
			$query = sprintf("SELECT * FROM `messages` USE INDEX(`news_feed`) LEFT JOIN `users` ON `users`.`idu` = `messages`.`uid` AND `users`.`suspended` = 0 WHERE (`messages`.`uid` IN (%s) AND `messages`.`page` = 0 AND `messages`.`group` = 0 AND `messages`.`public` != 0 %s%s) ORDER BY `messages`.`id` DESC LIMIT %s", $this->friendsList, $start, $from, ($this->per_page + 1));
		}
		// Get the user feed and pages feed
		else {
			$query = sprintf("(SELECT * FROM `messages` USE INDEX(`news_feed`) LEFT JOIN `users` ON `users`.`idu` = `messages`.`uid` AND `users`.`suspended` = 0 WHERE (`messages`.`uid` IN (%s) AND `messages`.`group` = 0 AND `messages`.`page` = 0 AND `messages`.`public` != 0 %s%s) ORDER BY `messages`.`id` DESC LIMIT %s) UNION (SELECT * FROM `messages` LEFT JOIN `users` ON `users`.`idu` = `messages`.`uid` AND `users`.`suspended` = 0 WHERE (`messages`.`page` IN (%s) AND `messages`.`public` != 0 %s%s) ORDER BY `messages`.`id` DESC LIMIT %s) ORDER BY `id` DESC LIMIT %s", $this->friendsList, $start, $from, ($this->per_page + 1), $this->pages, $start, $from, ($this->per_page + 1), ($this->per_page + 1));
		}

		return $this->getMessages($query, 'loadFeed', '\''.saniscape($value).'\'');
	}

	function getProfile($start, $value, $from = null) {
		$profile = $this->profile_data;
		$this->profile_id = $profile['idu'];

		$index = '`uid`';
		// If the username exist
		if(!empty($profile['idu'])) {
		    $private = '';
			if($profile['suspended'] == 2) {
				$private = 'profile_not_exists';
			} elseif($profile['suspended'] == 1) {
				$private = 'profile_suspended';
			} else {
				if($this->is_admin) {
					$private = 0;
				} elseif($this->id == $this->profile_id) {
					$private = 0;
				} else {
					$friendship = $this->verifyFriendship($this->id, $this->profile_id);

					// If the profile is set to friends only and there is no friendship
					if($profile['private'] == 2 && $friendship['status'] !== '1') {
						$private = 'profile_semi_private';
					}

					// If the profile is fully private
					elseif($profile['private'] == 1) {
						$private = 'profile_private';
					}
					// If the profile is blocked
					elseif($this->getBlocked($this->profile_id, 2)) {
						$private = 'profile_blocked';
					}
				}
			}
			if($private) {
				return $this->showError($private);
			}
			// Allowed types
			$this->listTypes = $this->listTypes('profile');
			$this->listDates = $this->listDates('profile');

			// Disable the per_page limit if $from is set
			if(is_numeric($from)) {
				$this->per_page = 99;
				$from = 'AND messages.id > \''.$this->db->real_escape_string($from).'\'';
				$index = '`uid`, PRIMARY';
			} else {
				$from = '';
			}

			// If the $start value is 0, empty the query;
			if($start == 0) {
				$start = '';
			} else {
				// Else, build up the query
				$start = 'AND `messages`.`id` < \''.$this->db->real_escape_string($start).'\'';
			}

			// Decide if the query will include only public messages or not
			// If the user that views the profile is not the owner
            $public = '';
			if($this->id !== $this->profile_data['idu']) {
				// Check if is admin or not
				if($this->is_admin) {
					$public = '';
				} else {
					// Check if there is any friendship relation
					$friendship = $this->verifyFriendship($this->id, $this->profile_data['idu']);

					if($friendship['status'] == '1') {
						$public = "AND `messages`.`public` <> 0";
					} else {
						$public = "AND `messages`.`public` = 1";
					}
				}
			}

			$type = $date = '';

			// Check for active filters
			if(in_array($value, $this->listTypes)) {
				$type = sprintf("AND `messages`.`type` = '%s'", $this->db->real_escape_string($value));
				$index = '`uid`, `type`';
			} elseif(in_array($value, $this->listDates)) {
				$date = sprintf("AND `time` >= '%s' AND `time` < '%s'", $this->db->real_escape_string($value).'-01-01 00:00:00', ($this->db->real_escape_string($value)+1).'-01-01 00:00:00');
				$index = '`uid`, `time`';
			}

			$query = sprintf("SELECT * FROM `messages` USE INDEX(%s), `users` WHERE `messages`.`uid` = '%s' %s AND `messages`.`group` = 0 AND `messages`.`page` = 0 AND `messages`.`uid` = `users`.`idu` %s %s %s ORDER BY `messages`.`id` DESC LIMIT %s", $index, $this->db->real_escape_string($profile['idu']), $type.$date, $public, $start, $from, ($this->per_page + 1));

			return $this->getMessages($query, 'loadProfile', '\''.saniscape($value).'\'');
		} else {
			return $this->showError('profile_not_exists');
		}
	}

	function getGroup($start, $group, $from = null) {
		// From: Load posts starting with a certain ID
		if($this->group_data['name']) {
			// Check the Group's privacy
			if($this->group_data['privacy']) {
				if($this->is_admin) {
					$private = 0;
				} elseif(!$this->groupPermission($this->group_data, $this->group_member_data)) {
					$private = 1;
				}
				if(isset($private) && $private)return $this->showError('group_private');
			}

			// Disable the per_page limit if $from is set
			if(is_numeric($from)) {
				$this->per_page = 9999;
				$from = 'AND `messages`.`id` > \''.$this->db->real_escape_string($from).'\'';
			} else {
				$from = '';
			}

			// If the $start value is 0, empty the query;
			if($start == 0) {
				$start = '';
			} else {
				// Else, build up the query
				$start = 'AND `messages`.`id` < \''.$this->db->real_escape_string($start).'\'';
			}

			// The query to select the subscribed users
			$query = sprintf("SELECT * FROM `messages` LEFT JOIN `users` ON `users`.`idu` = `messages`.`uid` WHERE `users`.`suspended` = 0 AND `messages`.`group` = '%s' AND `messages`.`public` = 1 %s %s ORDER BY `messages`.`id` DESC LIMIT %s", $group, $start, $from, ($this->per_page + 1));

			return $this->getMessages($query, 'loadGroup', $group);
		} else {
			return $this->showError('group_not_exists');
		}
	}

	function getPage($start, $page, $from = null) {
		// From: Load posts starting with a certain ID

		// Disable the per_page limit if $from is set
		if(is_numeric($from)) {
			$this->per_page = 9999;
			$from = 'AND `messages`.`id` > \''.$this->db->real_escape_string($from).'\'';
		} else {
			$from = '';
		}

		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `messages`.`id` < \''.$this->db->real_escape_string($start).'\'';
		}

		// The query to select the subscribed users
		$query = sprintf("SELECT * FROM `messages`, `users` WHERE `page` = '%s' AND `users`.`suspended` = 0 AND `messages`.`public` = 1 AND `messages`.`uid` = `users`.`idu` %s %s ORDER BY `messages`.`id` DESC LIMIT %s", $page, $start, $from, ($this->per_page + 1));

		return $this->getMessages($query, 'loadPage', $page);
	}

	function groupPermission($group, $user, $type = null) {
		// Type 1: Check if the user can post
		// Type 0: Check if the user can view the group's messages
		if($type == 1) {
			// If the user is in group
			if(isset($user['status']) && $user['status'] == 1) {
				// If the group settings allow only admins to post
				if($group['posts']) {
					// Check if the user is an administrator
					if(in_array($user['permissions'], array(1, 2))) {
						return 1;
					} else {
						return false;
					}
				}
				return 1;
			}
		} else {
			// If the group is public
			if($group['privacy'] == 0) {
				return 1;
			}
			// If the group is private
			if($group['privacy'] == 1) {
				// If the user is in group
				if($user['status'] == 1) {
					return 1;
				}
			}
		}
		return false;
	}

	function getPagesList() {
		$query = $this->db->query(sprintf("SELECT `post` FROM `likes` WHERE `by` = '%s' AND `type` = 2 ORDER BY `post` ASC", $this->db->real_escape_string($this->id)));

		$pages = array();

		while($row = $query->fetch_assoc()) {
			$pages[] = $row['post'];
		}

		return implode(',', $pages);
	}

	function getFriendsList($type = null) {
		// Type 0: Returns both confirmed and pending friendships
		// Type 1: Returns only confirmed friendships

		if($type) {
			$status = "";
		} else {
			$status = "AND `status` = '1'";
		}

		// The query to select the friends list
		$query = sprintf("SELECT `user2` as `friends` FROM `friendships` WHERE `user1` = '%s' %s UNION ALL SELECT `user1` as `friends` FROM `friendships` WHERE `user2` = '%s' %s ORDER BY `friends` ASC", $this->db->real_escape_string($this->id), $status, $this->db->real_escape_string($this->id), $status);

		// Run the query
		$result = $this->db->query($query);

		// The array to store the subscribed users
		$friends = array();
		while($row = $result->fetch_assoc()) {
			$friends[] = $row['friends'];
		}

		// Close the query
		$result->close();

		// Return the friends list (e.g: 13,22,19)
		// return implode(',', array_slice($friends, 0, 2000));
		return implode(',', $friends);
	}

	function pageData($name = null, $id = null) {
		if($id) {
			$query = sprintf("SELECT * FROM `pages` WHERE `id` = '%s'", $this->db->real_escape_string($id));
		} else {
			$query = sprintf("SELECT * FROM `pages` WHERE `name` = '%s'", $this->db->real_escape_string($name));
		}

		// Run the query
		$result = $this->db->query($query);

		return $result->fetch_assoc();
	}

	function groupData($name = null, $id = null) {
		if($id) {
			$query = sprintf("SELECT * FROM `groups` WHERE `id` = '%s'", $this->db->real_escape_string($id));
		} else {
			$query = sprintf("SELECT * FROM `groups` WHERE `name` = '%s'", $this->db->real_escape_string($name));
		}

		// Run the query
		$result = $this->db->query($query);

		return $result->fetch_assoc();
	}

	function groupOwner($id) {
		// Return the group owner ID (Admin panel)
		$query = sprintf("SELECT * FROM `groups_users` WHERE `group` = '%s' AND `permissions` = 2", $this->db->real_escape_string($id));

		// Run the query
		$result = $this->db->query($query);

		return $result->fetch_assoc();
	}

	function groupMemberData($group = null) {
		if($group && $this->id) {
			$query = $this->db->query(sprintf("SELECT `groups_users`.`status`, `groups_users`.`permissions` FROM `groups_users` WHERE `groups_users`.`group` = '%s' AND `groups_users`.`user` = '%s'", $this->db->real_escape_string($group), $this->db->real_escape_string($this->id)));

			return $query->fetch_assoc();
		}
	}

	function fetchGroup($group) {
		global $LNG, $CONF;
		$group['cover'] = ((!empty($group['cover'])) ? $group['cover'] : 'default.png');
		$cover = '<div class="twelve columns">
					<div class="cover-container">
						<div class="cover-content">
							<a style="border-radius:50%;" onclick="gallery(\''.$group['cover'].'\', \''.($group['id'] ?? null).($group['title'] ?? null).'\', \'covers\', 0)" id="'.$group['cover'].'"><div class="cover-image" style="background-position: center; background-image: url('.permalink($this->url.'/image.php?t=c&w=900&h=200&src='.$group['cover']).')">
							</div></a>

							<div class="cover-description">
								'.(isset($group['name']) && $group['name'] ? '
								<div class="cover-buttons cover-buttons-group">
									'.$this->coverButtons(1).'
								</div>
								<div class="cover-description-content cover-group-content">
									<div class="cover-username-container"><div class="cover-username"><a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name']).'" rel="loadpage">'.realName($group['title']).'</a></div></div>
									<div class="cover-description-buttons"><div id="group-btn-'.$group['id'].'" class="friend-btn">'.$this->joinGroup(0).'</div></div>
								</div>
								' : '').'
							</div>
						</div>
					</div>
				</div>';
		return $cover;
	}

	function joinGroup($type) {
		global $LNG, $CONF;
		
    	//get invitecode from form
    	$inputInviteCode  = $_POST['invitecode'];
    
    	//get the invite code from the database
    		
    	$getinvitecode = $this->db->query(sprintf("SELECT `invitecode`,`id`FROM `groups` WHERE `id` ='%s'",$this->group_data['id']));
    	
    	$gic = $getinvitecode->fetch_assoc();
		
		// Type 0: Return buttons

		// If the user is not logged-in, or has been group blocked
		if(!$this->id) {
			return false;
		} elseif(isset($this->group_member_data['status']) && $this->group_member_data['status'] == '2') {
			return false;
		} elseif(isset($this->group_member_data['permissions']) && $this->group_member_data['permissions'] == '2') {
			return false;
		}

		if($type == 1) {
			$old_id = $this->id;
			$this->id = '';
			if(isset($this->group_member_data['status']) && $this->group_member_data['status'] == '1') {
				// Remove the user
				$this->groupMember(0, $old_id);
			} elseif(isset($this->group_member_data['status']) && $this->group_member_data['status'] == '0') {
				// Remove the user
				$this->groupMember(0, $old_id);
			} else {
				$mgq = $this->db->query(sprintf("SELECT COUNT(*) as `count` FROM `groups_users` WHERE `user` = '%s'", $this->db->real_escape_string($old_id)));
				$mgr = $mgq->fetch_assoc();
				if($mgr['count'] > $this->groups_limit) {
					return false;
				}

				// If the group is private, request to join
				if($this->group_data['privacy'] == 1) {
					$this->db->query(sprintf("INSERT INTO `groups_users` (`group`, `user`, `status`, `permissions`) VALUES ('%s', '%s', '%s', '%s')", $this->group_data['id'], $old_id, 0, 0));
				} else{
				 
				    
					// Add in group
					$this->db->query(sprintf("INSERT INTO `groups_users` (`group`, `user`, `status`, `permissions`) VALUES ('%s', '%s', '%s', '%s')", $this->group_data['id'], $old_id, 1, 0));
					$this->groupActivity(3, null, $this->group_data['id'], $old_id);
					// Get the user group status
					$this->db->query(sprintf("UPDATE `groups` SET `members` = `members` + 1, `time` = `time` WHERE `id` = '%s'", $this->group_data['id']));
				}
			}
            
			$this->id = $old_id;
			$this->group_member_data = $this->groupMemberData($this->group_data['id']);
			return $this->joinGroup(0);
		} else {
			if(isset($this->group_member_data['status']) && $this->group_member_data['status'] == '1') {
				$text = $LNG['leave_group'];
				$output = '<div class="group-button approve-button group-join" title="'.$text.'" onclick="group(6, 0, '.$this->group_data['id'].')"></div>';
			} elseif(isset($this->group_member_data['status']) && $this->group_member_data['status'] == '0') {
				$text = $LNG['pending_approval'];
				$output = '<div class="group-button pending-button group-join" title="'.$text.'" onclick="group(6, 0, '.$this->group_data['id'].')"></div>';
			} else {
				$text = $LNG['join_group'];
				
				if($gic['invitecode'] == $inputInviteCode ){
				    $joingroupbtn  = '
				        <div class="group-button join-button" title="'.$text.'" onclick="group(6, 0, '.$this->group_data['id'].')"></div>
				    ';
				    
				 }
				$output = '
				<div style=" margin-right: 20px;">
    				<form style="overflow-x: hidden;" action="" method="POST">
                		<input type="text" name="invitecode" value="'.$inputInviteCode.'" placeholder="'.$LNG['group_invite_code'].'" 
                		id="invitecode"
                		>
                	</form>
                	'.$joingroupbtn.'
                </div>
				';
			}
		}
		
		return $output;
	}

	function sidebarGroupInfo($group) {
		global $LNG;

		$born = explode('-', $group['time']);

		// Make it into integer instead of a string (removes the 0, e.g: 03=>3, prevents breaking the language)
		$month = intval($born[1]);

		// Start checking the values
		if($month) {
			$birthdate = $LNG["month_$month"].' '.substr($born[2], 0, 2).', '.$born[0];
		}

		$extra = ($group['posts'] ? $LNG['admins_posts'] : $LNG['members_posts']);

		$rows = array(
			$LNG['created_on']	=> array('calendar', $birthdate),
			$LNG['privacy']		=> array('privacy', ($group['privacy'] ? $LNG['private'].$extra : $LNG['public'].$extra)),
			$LNG['description']	=> array('info', $group['description'])
		);

		$info = '<div class="sidebar-container widget-group-info"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['profile_about'].''.((isset($this->group_member_data['permissions']) && $this->group_member_data['permissions'] == 2) ? ' <span class="sidebar-header-link"><a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name'].'&r=edit').'" rel="loadpage">'.$LNG['admin_ttl_edit'].'</a></span>' : '').'</div>';

		foreach($rows as $column => $value) {
			if($value[1]) {
				$info .= '<div class="sidebar-list"><div class="about-icon about-'.$value[0].'"></div>'.$column.': <strong>'.$value[1].'</strong></div>';
			}
		}

		$info .= '</div></div>';

		return $info;
	}

	function sidebarPageInfo($page) {
		global $LNG;

		$born = explode('-', $page['time']);

		// Make it into integer instead of a string (removes the 0, e.g: 03=>3, prevents breaking the language)
		$month = intval($born[1]);

		// Start checking the values
		if($month) {
			$birthdate = $LNG["month_$month"].' '.substr($born[2], 0, 2).', '.$born[0];
		}

		$extra = (isset($page['posts']) && $page['posts'] ? $LNG['admins_posts'] : $LNG['members_posts']);

		$rows = array(
			$LNG['likes'] => array('like', ($page['likes'] > 0 ? '<a href="'.permalink($this->url.'/index.php?a=page&name='.$this->page_data['name'].'&r=likes').'" rel="loadpage">'.$page['likes'].' '.$LNG['people'].'</a>' : '')),
			$LNG['category'] => array('work', $LNG['page_'.$page['category']]),
			$LNG['address'] => array('address', $page['address']),
			$LNG['phone'] => array('phone', $page['phone']),
			$LNG['ttl_website'] => array('website', (!empty($page['website']) ? '<a href="'.$page['website'].'" target="_blank" rel="nofollow">'.$page['website'].'</a>' : '')),
			$LNG['description']	=> array('info', $page['description'])
		);

		$info = '<div class="sidebar-container widget-page-info"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['profile_about'].''.(($this->page_data['by'] == $this->id) ? ' <span class="sidebar-header-link"><a href="'.permalink($this->url.'/index.php?a=page&name='.$page['name'].'&r=edit').'" rel="loadpage">'.$LNG['admin_ttl_edit'].'</a></span>' : '').'</div>';

		foreach($rows as $column => $value) {
			if($value[1]) {
				$info .= '<div class="sidebar-list"><div class="about-icon about-'.$value[0].'"></div>'.$column.': <strong>'.$value[1].'</strong></div>';
			}
		}

		$info .= '</div></div>';

		return $info;
	}

	public function profileData($username = null, $id = null) {
		// The query to select the profile
		// If the $id is set (used in Add Friend function for profiles) then search for the ID
		if($id) {
			$query = sprintf("SELECT `idu`, `username`, `email`, `first_name`, `last_name`, `country`, `location`, `address`, `school`, `work`, `website`, `bio`, `date`, `facebook`, `twitter`, `image`, `private`, `suspended`, `privacy`, `born`, `cover`, `verified`, `gender`, `interests`, `email_new_friend`, `offline`, `online` FROM `users` WHERE `idu` = '%s'", $this->db->real_escape_string($id));
		} else {
			$query = sprintf("SELECT `idu`, `username`, `email`, `first_name`, `last_name`, `country`, `location`, `address`, `school`, `work`, `website`, `bio`, `date`, `facebook`, `twitter`, `image`, `private`, `suspended`, `privacy`, `born`, `cover`, `verified`, `gender`, `interests`, `email_new_friend`, `offline`, `online` FROM `users` WHERE `username` = '%s'", $this->db->real_escape_string($username));
		}

		// Run the query
		$result = $this->db->query($query);

		return $result->fetch_assoc();
	}

	function coverExtraButtons() {
		global $LNG;

		// If the user is logged-in and is not on his profile
		if(!empty($this->username) && $this->username !== $this->profile) {
			$items = array($this->getBlocked($this->profile_data['idu'], null, 1), (!$this->getBlocked($this->profile_data['idu'], 2) ? $this->poke($this->profile_data['idu']) : ''));
		}

		if(!empty($items)) {
		    $buttons = '';
			foreach($items as $value) {
				$buttons .= $value;
			}

			return '<div class="bullets-button" onclick="messageMenu(\'-profile-extra\', 1)" id="profile-extra"></div><div id="message-menu-profile-extra" class="message-menu-container bullets-menu-container">'.$buttons.'</div>';
		}
	}

	function likePage($type = null) {
		global $LNG;
		$query = $this->db->query(sprintf("SELECT * FROM `likes` WHERE `post` = '%s' AND `by` = '%s' AND `type` = 2", $this->db->real_escape_string($this->page_data['id']), $this->db->real_escape_string($this->id)));

		if($query->num_rows > 0) {
			$value = 'unlike';
			$title = $LNG['dislike'];
			if($type) {
				$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` = '%s' AND `by` = '%s' AND `type` = 2", $this->db->real_escape_string($this->page_data['id']), $this->db->real_escape_string($this->id)));
				$value = 'like';
				$title = $LNG['like'];
				$this->db->query(sprintf("UPDATE `pages` SET `likes` = `likes` - 1, `time` = `time` WHERE id = '%s'", $this->db->real_escape_string($this->page_data['id'])));
			}
		} else {
			$value = 'like';
			$title = $LNG['like'];
			if($type) {
				$this->db->query(sprintf("INSERT INTO `likes` (`post`, `by`, `type`) VALUES ('%s', '%s', 2)", $this->db->real_escape_string($this->page_data['id']), $this->db->real_escape_string($this->id)));
				$value = 'unlike';
				$title = $LNG['dislike'];
				$this->db->query(sprintf("UPDATE `pages` SET `likes` = `likes` + 1, `time` = `time` WHERE id = '%s'", $this->db->real_escape_string($this->page_data['id'])));
			}
		}

		return '<div class="page-button page-button-'.strtolower($value).'" onclick="doLike('.$this->page_data['id'].', 2)" title="'.$title.'"></div>';
	}

	function fetchPage($page) {
		global $LNG, $CONF;
		$cover = '<div class="twelve columns">
					<div class="cover-container">
						<div class="cover-content">
							<a onclick="gallery(\''.$page['cover'].'\', \''.$page['id'].$page['name'].'\', \'covers\', 0)" id="'.$page['cover'].'"><div class="cover-image" style="background-position: center; background-image: url('.permalink($this->url.'/image.php?t=c&w=900&h=200&src='.$page['cover']).')">
							</div></a>
							<div class="cover-description">
								<div class="cover-avatar-content">
									<div class="cover-avatar" style="border-radius:50%; border:3px solid #9c27b0">
										<a style="border-radius:50%;" onclick="gallery(\''.$page['image'].'\', \''.$page['id'].$page['name'].'\', \'avatars\', 0)" id="'.$page['image'].'"><span id="avatar'.$page['id'].$page['name'].'"><img src="'.permalink($this->url.'/image.php?t=a&w=150&h=150&src='.$page['image']).'"></span></a>
									</div>
								</div>
								<div class="cover-buttons">
									'.$this->coverButtons(2).'
								</div>
								<div class="cover-description-content">
								<span id="author'.$page['id'].$page['name'].'"></span><span id="time'.$page['id'].$page['name'].'"></span><div class="cover-username-container"><div class="cover-username"><a href="'.permalink($this->url.'/index.php?a=page&name='.$page['name']).'" rel="loadpage">'.$page['title'].'</a>'.((!empty($page['verified'])) ? '<img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_page'].'">' : '').'</div></div>
								'.($this->id ? '<div class="cover-description-buttons"><div id="page-btn-'.$page['id'].'" class="friend-btn">'.$this->likePage().'</div></div>' : '').'
								</div>
							</div>
						</div>
					</div>
				</div>';
		return $cover;
	}

	function fetchProfile($profile) {
		global $LNG, $CONF;
		$profile['cover'] = ((!empty($profile['cover'])) ? $profile['cover'] : 'default.png');
		$profile['image'] = ((!empty($profile['image'])) ? $profile['image'] : 'default.png');
		$cover = '<div class="twelve columns">
					<div class="cover-container">
						<div class="cover-content">
							<a onclick="gallery(\''.$profile['cover'].'\', \''.$profile['idu'].$profile['username'].'\', \'covers\', 0)" id="'.$profile['cover'].'"><div class="cover-image" style="background-position: center; background-image: url('.permalink($this->url.'/image.php?t=c&w=900&h=200&src='.$profile['cover']).'">
							</div></a>
							<div class="cover-description">
								<div class="cover-avatar-content" >
									<div class="cover-avatar" style="border-radius:50%; border:3px solid #9c27b0" >
										<a style="border-radius:50%;" onclick="gallery(\''.$profile['image'].'\', \''.$profile['idu'].$profile['username'].'\', \'avatars\', 0)" id="'.$profile['image'].'"><span id="avatar'.$profile['idu'].$profile['username'].'"><img src="'.permalink($this->url.'/image.php?t=a&w=150&h=150&src='.$profile['image']).'"></span></a>
									</div>
								</div>
								'.($profile['idu'] ? '<div class="cover-buttons">'.$this->coverButtons(0).'</div>
								'.($profile['suspended'] == 0 ? '<div class="cover-description-content">
								<span id="author'.$profile['idu'].$profile['username'].'"></span><span id="time'.$profile['idu'].$profile['username'].'"></span><div class="cover-username-container"><div class="cover-username"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$profile['username']).'" rel="loadpage">'.realName($profile['username'], $profile['first_name'], $profile['last_name']).'</a>'.((!empty($profile['verified'])) ? '<img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'">' : '').'</div></div>
								<div class="cover-description-buttons">'.$this->coverExtraButtons().'<div id="friend'.$profile['idu'].'" class="friend-btn">'.$this->friendship(null, null, null).'</div>'.$this->chatButton($profile['idu'], $profile['username'], 1).'</div></div>' : '') : '').'
							</div>
						</div>
					</div>
				</div>';
		return $cover;
	}

	function poke($user_id, $type = null) {
		// Select the likes, if any
		// Type 0: Get the current state
		// Type 1: Do the Poke
		global $LNG;
		$query = $this->db->query(sprintf("SELECT * FROM `notifications` WHERE `from` = '%s' AND `to` = '%s' AND `type` = '8'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($user_id)));

		// If a like already exists, dislike
		if($query->num_rows > 0) {
			if($type) {
				$this->db->query(sprintf("DELETE FROM `notifications` WHERE `from` = '%s' AND `to` = '%s' AND `type` = '8'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($user_id)));
				$value = $LNG['poke'];
			} else {
				$value = $LNG['poked'];
			}
		} else {
			if($type) {
				$this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `type`) VALUES ('%s', '%s', '8')", $this->db->real_escape_string($this->id), $this->db->real_escape_string($user_id)));
				$value = $LNG['poked'];
			} else {
				$value = $LNG['poke'];
			}
		}
		if($type) {
			return $value;
		} else {
			return '<div class="message-menu-row" onclick="poke('.saniscape($user_id).')" id="poke'.saniscape($user_id).'">'.$value.'</div>';
		}
	}

	function countGroupMembers($name = null, $type = null) {
		// Type 0: Count the Group Members
		// Type 1: Count the Group Admins
		// Type 2: Count the Group Membership Requests
		// Type 3: Count the Group Blocked Members

		if($type == 1) {
			$status = 1;
			$type = ' AND `groups_users`.`permissions` IN (1,2)';
		} elseif($type == 2) {
			$status = 0;
			$type = '';
		} elseif($type == 3) {
			$status = 2;
			$type = '';
		} else {
			$status = 1;
			$type = '';
		}

		$query = $this->db->query(sprintf("SELECT COUNT(`groups_users`.`id`) FROM `groups_users` WHERE `groups_users`.`group` = '%s' AND `groups_users`.`status` = '%s' %s", $name, $status, $type));

		// Store the array results
		$result = $query->fetch_array();

		// Return the likes value
		return $result[0];
	}

	function coverButtons($type) {
		// Type 0: Return the buttons for profile covers
		// Type 1: Return the buttons for group covers
		global $LNG;

		// array map: value => array(get_param, get_param_value, value)
		if($type == 1) {
			$buttons = array(
						$LNG['discussion'] => array('', '', ''),
						$LNG['members'] => array('&r=', 'members', $this->countGroupMembers($this->group_data['id'], 0)),
						$LNG['admins'] => array('&r=', 'admins', $this->countGroupMembers($this->group_data['id'], 1)),
						(isset($this->group_member_data['permissions']) && in_array($this->group_member_data['permissions'], array(1, 2)) && $this->group_member_data['status'] ? $LNG['requests'] : '') => array('&r=', 'requests', $this->countGroupMembers($this->group_data['id'], 2)),
						(isset($this->group_member_data['permissions']) && in_array($this->group_member_data['permissions'], array(1, 2)) && $this->group_member_data['status'] ? $LNG['blocked'] : '') => array('&r=', 'blocked', $this->countGroupMembers($this->group_data['id'], 3)),
						(isset($this->group_member_data['permissions']) && $this->group_member_data['permissions'] == 2 && $this->group_member_data['status'] ? $LNG['edit'] : '') => array('&r=', 'edit', '')
						);
		} elseif($type == 2) {
			$buttons = array(
						$LNG['timeline'] => array('', '', ''),
						$LNG['likes'] => array('&r=', 'likes', ''),
						($this->page_data['by'] == $this->id ? $LNG['edit'] : '') => array('&r=', 'edit', '')
						);
		} else {
			global $settings;
			if($settings['groups']) {
				$groups = $this->countGroups();
			} else {
			    $groups = null;
            }
			if($settings['pages']) {
				$likes = $this->getLikes();
			} else {
			    $likes = null;
            }
			$pictures = $this->getPictures();
			$buttons = array(
						$LNG['timeline'] => array('', '', ''),
						$LNG['about'] => array('&r=', 'about', ''),
						($pictures ? $LNG['sidebar_picture'] : '') => array('&filter=', 'picture', $pictures),
						(($this->friendsCount) ? $LNG['friends'] : '') => array('&r=', 'friends', $this->friendsCount),
						($likes ? $LNG['likes'] : '') => array('&r=', 'likes', $likes),
						($groups ? $LNG['groups'] : '') => array('&r=', 'groups', $groups)
						);
		}

		$button = '';
		foreach($buttons as $value => $name) {
			// Check whether the value is empty or not in order to return the button
            $class = '';
			if($value) {
				if($type == 1) {
					$link = 'group&name='.$_GET['name'].$name[0].$name[1];
				} elseif($type == 2) {
					$link = 'page&name='.$_GET['name'].$name[0].$name[1];
				} else {
					$link = 'profile&u='.((!empty($this->profile)) ? $this->profile : $this->username).$name[0].$name[1];
				}

                if(isset($_GET['r']) && $name[1] == $_GET['r'] && empty($_GET['filter']) && empty($_GET['friends']) && empty($_GET['search'])) {
                    $class = ' cover-button-active';
                } elseif(isset($_GET['filter']) && $name[1] == $_GET['filter'] && empty($_GET['r'])) {
                    $class = ' cover-button-active';
                } elseif(empty($name[1]) && empty($_GET['r']) && empty($_GET['filter']) && empty($_GET['friends']) && empty($_GET['search'])) {
                    $class = ' cover-button-active';
                }

                $button .= '<a class="cover-button'.$class.'" rel="loadpage" href="'.permalink($this->url.'/index.php?a='.$link).'">'.$value.(($name[2]) ? '<span class="cover-button-value">('.$name[2].')</span>' : '').'</a>';
			}
		}

		$button .= '<div class="message-btn button-normal" onclick="messageMenu(\'profile\', 1)" id="profile-button"><div class="group-button menu-button" id="profile-btn"></div></div><div id="message-menuprofile" class="message-menu-container menu-profile-container">';

		foreach($buttons as $value => $name) {
		    $class = '';
			// Check whether the value is empty or not in order to return the button
			if($value) {
				if($type == 1) {
					$link = 'group&name='.$_GET['name'].$name[0].$name[1];
				} elseif($type == 2) {
					$link = 'page&name='.$_GET['name'].$name[0].$name[1];
				} else {
					$link = 'profile&u='.((!empty($this->profile)) ? $this->profile : $this->username).$name[0].$name[1];
				}

				if(isset($_GET['r']) && $name[1] == $_GET['r'] && empty($_GET['filter'])) {
				    $class = ' profile-menu-active';
                } elseif(isset($_GET['filter']) && $name[1] == $_GET['filter'] && isset($_GET['filter']) && empty($_GET['r'])) {
                    $class = ' profile-menu-active';
                } elseif(empty($name[1]) && empty($_GET['r']) && empty($_GET['filter']) && empty($_GET['friends'])) {
                    $class = ' profile-menu-active';
                }

                $button .= '<a class="'.$class.'" rel="loadpage" href="'.permalink($this->url.'/index.php?a='.$link).'"><div class="message-menu-row">'.$value.(($name[2]) ? ' <span class="profile-menu-value">('.$name[2].')</span>' : '').'</div></a>';
			}
		}

		$button .='
			</div>
		';

		return $button;
	}

	function countLikes($post, $type) {
		$query = $this->db->query(sprintf("SELECT count(`id`) FROM `likes` WHERE `post` = '%s' AND `type` = '%s'", $this->db->real_escape_string($post), $this->db->real_escape_string($type)));

		$result = $query->fetch_array();
		return $result[0];
	}

	function profileCard($data, $type) {
		global $LNG, $CONF;
		// Type 0: Profiles
		// Type 1: Pages
		if($type) {
			$name = $data['title'];
			$url = permalink($this->url.'/index.php?a=page&name='.$data['name']);
			$likes = $data['likes'];
			$rows = array('like' => array($LNG['likes'], ($likes > 0 ? $likes.' '.$LNG['people'] : '')), 'work' => array($LNG['category'], $LNG['page_'.$data['category']]));
			// If the user is logged-in
			if($this->id) {
				$buttons = array(1, '<div id="page-card-'.$this->page_data['id'].'">'.$this->likePage().'</div>');
			}
		} else {
			$name = realName($data['username'], $data['first_name'], $data['last_name']);
			$url = permalink($this->url.'/index.php?a=profile&u='.$data['username']);
			$friends = $this->sidebarFriends(0, 1);
			// If the profile is public then retrieve profile information
			if($data['private'] == 0 || $data['idu'] == $this->id) {
				$rows = array('friends' => array($LNG['friends'], $friends), 'work' => array($LNG['works_at'], $data['work']), 'school' => array($LNG['studied_at'], $data['school']));
			}
			// If the user is logged-in
			if($this->id) {
				$buttons = array(($this->friendship(null, null, null) ? 1 : 0), '<div id="friend-card-'.$data['idu'].'">'.$this->friendship(null, null, null).'</div>'.$this->chatButton($data['idu'], $data['username'], 1));
			}
		}
		// Parse profile information
		$i = 0;
		$info = '';
		foreach($rows as $key => $value) {
			if($i == 2) {
				break;
			}
			if(!empty($value[1]) && !empty($key)) {
				$info .= '<div class="profile-card-row"><div class="about-icon about-'.$key.'"></div>'.$value[0].': '.$value[1].'</div>';
				$i++;
			}
		}

		$card = '
			<div class="profile-card-cover">
				<img src="'.permalink($this->url.'/image.php?t=c&w=300&h=100&src='.$data['cover']).'">
				<div class="profile-card-info">
					<a href="'.$url.'" rel="loadpage"><div class="cover-username">'.$name.''.((!empty($data['verified'])) ? '<img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.($type ? $LNG['verified_page'] : $LNG['verified_profile']).'" height="16" width="16">' : '').'</div></a>
				</div>
			</div>
			<div class="profile-card-avatar">
				<a href="'.$url.'"><img src="'.permalink($this->url.'/image.php?t=a&w=112&h=112&src='.$data['image']).'"></a>
			</div>
			<div class="profile-card-bio">'.$info.'</div>
			'.((!empty($buttons[0])) ? '
			<div class="profile-card-divider"></div>
			<div class="profile-card-buttons"><div class="profile-card-buttons-container">'.$buttons[1].'</div></div>' : '').'
		';
		return $card;
	}

	function getAbout($profile) {
		global $LNG;

        $social = $address = $website = $interests = $bio = $work = $school = $country = $birthdate = $gender = $aboutSection = $basicSection = $contactSection = $educationSection = $info = '';

		// Contact Information section
		if($profile['country'] && $profile['location']) {
			$country = $profile['location'].', '.$profile['country'];
		} elseif($profile['country']) {
			$country = $profile['country'];
		} elseif($profile['location']) {
			$country = $profile['location'];
		}

		if($profile['address']) {
			$address = $profile['address'];
		}

		$location = (($address) ? '<div>'.$address.'</div>' : '').''.(($country) ? '<div>'.$country.'</div>' : '');

		if($profile['facebook'] || $profile['twitter']) {
			$social .= ($profile['facebook']) ? '<div><a href="http://facebook.com/'.$profile['facebook'].'" target="_blank" rel="nofllow">Facebook</a></div>' : '';
			$social .= ($profile['twitter']) ? '<div><a href="http://twitter.com/'.$profile['twitter'].'" target="_blank" rel="nofllow">Twitter</a></div>' : '';
		}

		if($social) {
			$social = '<div class="about-text">'.$social.'</div>';
		}

		if($profile['website']) {
			$website = '<a href="'.$profile['website'].'" target="_blank" rel="nofollow">'.$profile['website'].'</a>';
		}

		// Basic Information section
		if($profile['gender']) {
			$gender = ($profile['gender'] == 1) ? $LNG['male'] : $LNG['female'];
		}

		if($profile['interests']) {
			$interests = ($profile['interests'] == 1) ? $LNG['men'] : $LNG['women'];
		}

		if($profile['born']) {
            // Explode the born value [[0]=>Y,[1]=>M,[2]=>D];
            $born = explode('-', $profile['born']);;

            // Make it into integer instead of a string (removes the 0, e.g: 03=>3, prevents breaking the language)
            $month = intval($born[1]);

            // Start checking the values
            if ($month) {
                $birthdate = $LNG["month_$month"] . ' ' . $born[2] . ', ' . $born[0];
            }
        }

		// Work and Education Information
		if($profile['school']) {
			$school = $profile['school'];
		}

		if($profile['work']) {
			$work = $profile['work'];
		}

		// About section
		if($profile['bio']) {
			$bio = $profile['bio'];
		}

		if($location || $website || $social) {
			$contactSection = 1;
		}
		if($gender || $birthdate || $interests) {
			$basicSection = 1;
		}
		if($work || $school) {
			$educationSection = 1;
		}
		if($bio) {
			$aboutSection = 1;
		}
		if(!$aboutSection && !$basicSection && !$contactSection && !$educationSection) {
			$info = $LNG['no_info_avail'];
		}
		$about = '
		<div class="message-container">
			<div class="message-content">
				<div class="message-inner">
					'.$info.'
					'.(($contactSection) ? '<div><strong>'.$LNG['contact_information'].'</strong></div>
					'.(($location)	? '<div class="about-row"><div class="about-text">'.$LNG['address'].'</div><div class="about-text">'.$location.'</div></div>' : '').'
					'.(($website) 	? '<div class="about-row"><div class="about-text">'.$LNG['ttl_website'].'</div><div class="about-text">'.$website.'</div></div>' : '').'
					'.(($social) 	? '<div class="about-row"><div class="about-text">'.$LNG['other_accounts'].'</div>'.$social.'</div>' : '').'<br>' : '').'
					'.(($basicSection) ? '<div><strong>'.$LNG['basic_information'].'</strong></div>
					'.(($gender)	? '<div class="about-row"><div class="about-text">'.$LNG['ttl_gender'].'</div><div class="about-text">'.$gender.'</div></div>' : '').'
					'.(($birthdate)	? '<div class="about-row"><div class="about-text">'.$LNG['ttl_birthdate'].'</div><div class="about-text">'.$birthdate.'</div></div>' : '').'
					'.(($interests) ? '<div class="about-row"><div class="about-text">'.$LNG['interests'].'</div><div class="about-text">'.$interests.'</div></div>' : '').'<br>' : '').'
					'.(($educationSection) ? '<div><strong>'.$LNG['work_and_education'].'</strong></div>
					'.(($work)		? '<div class="about-row"><div class="about-text">'.$LNG['works_at'].'</div><div class="about-text">'.$work.'</div></div>' : '').'
					'.(($school) 	? '<div class="about-row"><div class="about-text">'.$LNG['studied_at'].'</div><div class="about-text">'.$school.'</div></div>' : '').'<br>' : '').'
					'.(($aboutSection) ? '<div class="about-row"><strong>'.$LNG['about'].'</strong></div>
					'.(($bio) ? '<div class="about-row"><div class="about-text">'.$LNG['ttl_bio'].'</div><div class="about-text">'.$bio.'</div></div>' : '') : '').'
				</div>
			</div>
		</div>';
		return $about;
	}

	function fetchProfileWidget($username, $name, $image) {
		global $LNG;
		$widget =  '<div class="sidebar-container widget-welcome">
						<div class="sidebar-content">
							<div class="sidebar-header">'.$LNG['welcome'].'</div>
							<div class="sidebar-inner">
								<div class="sidebar-avatar"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$username).'" rel="loadpage"><img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$image).'"></a></div>
								<div class="sidebar-avatar-desc">
									<a href="'.permalink($this->url.'/index.php?a=profile&u='.$username).'" rel="loadpage">'.((!empty($name) ? $name : $username)).'</a>
									<div class="sidebar-avatar-edit"><a href="'.permalink($this->url.'/index.php?a=settings').'" rel="loadpage">'.$LNG['admin_ttl_edit_profile'].'</a></div>
								</div>
							</div>
						</div>
					</div>';
		return $widget;
	}

	function checkNewMessages($last, $filter = null, $type = null) {
		global $LNG;
		// Type 0: Feed
		// Type 1: Profile
		// Type 2: Group
		if($type == 1) {
			$message = $this->getProfile(0, $filter, $last);
		} elseif($type == 2) {
			$message = $this->getGroup(0, $filter, $last);
		} elseif($type == 3) {
            $message = $this->getPage(0, $filter, $last);
        } else {
			$message = $this->getFeed(0, $filter, $last);
		}
		return $message[0];
	}

	function sidebarProfileInfo($profile) {
		global $LNG;

		$work = $school = $country = $birthdate = $gender = '';

		if($profile['born']) {
            // Explode the born value [[0]=>Y,[1]=>M,[2]=>D];
            $born = explode('-', $profile['born']);

            // Make it into integer instead of a string (removes the 0, e.g: 03=>3, prevents breaking the language)
            $month = intval($born[1]);

            // Start checking the values
            if($month) {
                $birthdate = $LNG["month_$month"].' '.$born[2].', '.$born[0];
            }
        }

		if($profile['country'] && $profile['location']) {
			$country = $profile['location'].', '.$profile['country'];
		} elseif($profile['country']) {
			$country = $profile['country'];
		} elseif($profile['location']) {
			$country = $profile['location'];
		}
		if($profile['school']) {
			$school = $profile['school'];
		}
		if($profile['work']) {
			$work = $profile['work'];
		}
		if($profile['gender']) {
			$gender = ($profile['gender'] == 1) ? $LNG['male'] : $LNG['female'];
		}

		$rows = array(
			$LNG['works_at']			=> array('work', $work),
			$LNG['studied_at']			=> array('school', $school),
			$LNG['lives_in']			=> array('location', $country),
			$LNG['born_on']				=> array('calendar', $birthdate),
			$LNG['ttl_gender']			=> array(($profile['gender'] == 1 ? 'male' : 'female'), $gender),
			$LNG['friends']				=> array('friends', $this->sidebarFriends(0, 1))
		);

		$info = '<div class="sidebar-container widget-about"><div class="sidebar-content"><div class="sidebar-header"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$this->profile.'&r=about').'" rel="loadpage">'.$LNG['profile_about'].'</a>'.(($this->profile == $this->username) ? ' <span class="sidebar-header-link"><a href="'.permalink($this->url.'/index.php?a=settings').'" rel="loadpage">'.$LNG['admin_ttl_edit'].'</a></span>' : '').'</div>';

		foreach($rows as $column => $value) {
			if($value[1]) {
				$info .= '<div class="sidebar-list"><div class="about-icon about-'.$value[0].'"></div>'.$column.': <strong>'.$value[1].'</strong></div>';
			}
		}

		$info .= '</div></div>';

		return $info;
	}

	function checkNewNotifications($limit, $type = null, $for = null, $start = null, $ln = null, $cn = null, $sn = null, $fn = null, $dn = null, $bn = null, $gn = null, $pn = null, $xn = null, $mn = null) {
		global $LNG, $CONF;
		// $ln, $cn, etc holds the filters for the notifications
		// Type 0: Just check for and show the new notification alert
		// Type 1: Return the last X notifications from each category. (Drop Down Notifications)
		// Type 2: Return the latest X notifications (read and unread) (Notifications Page)

		// For 0: Returns the Global Notifications
		// For 1: Return results for the Chat Messages Notifications (Drop Down)
		// For 2: Return Chat Messages results for the Notifications Page
		// For 3: Return the Friend Requsts Notifications (Drop Down)

		// Start checking for new notifications
		if(!$type) {
            $mc = $lc = $pc = $cc = $sc = $gc = $xc = $rc = $fc = $dc = 0;

			// Check for new likes events
			if($mn) {
				$checkMentions = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '11' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				$mc = $checkMentions->num_rows;
			}

			if($ln) {
				$checkLikes = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '2' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				$lc = $checkLikes->num_rows;
			}

			// Check for new pokes events
			if($pn) {
				$checkPokes = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '8' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				$pc = $checkPokes->num_rows;
			}

			// Check for new comments events
			if($cn) {
				$checkComments = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '1' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				// If any, return 1 (show notification)
				$cc = $checkComments->num_rows;
			}

			// Check for new messages events (shared messages)
			if($sn) {
				$checkShares = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '3' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				// If any, return 1 (show notification)
				$sc = $checkShares->num_rows;
			}

			// Check for groups invitations
			if($gn) {
				$checkGroups = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '6' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				$gc = $checkGroups->num_rows;
			}

			// Check for page invitations
			if($xn) {
				$checkPages = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '9' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				$xc = $checkPages->num_rows;
			}

			$checkFriends = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '4' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

			// If any, return 1 (show notification)
			$rc = $checkFriends->num_rows;

			// Check for new friend additions
			if($fn) {
				$confirmedFriends = $this->db->query(sprintf("SELECT `id` FROM `notifications` WHERE `to` = '%s' AND `from` <> '%s' AND `type` = '5' AND `read` = '0'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

				// If any, return 1 (show notification)
				$fc = $confirmedFriends->num_rows;
			}

			if($for) {
				if($dn) {
					$checkChats = $this->db->query(sprintf("SELECT `read` FROM `conversations` WHERE `to` = '%s' AND `read` = '0'", $this->db->real_escape_string($this->id)));
					// If any, return 1 (show notification)
					$dc = $checkChats->num_rows;
				}
			}

			$output = array('response' => array('global' => $lc + $cc + $sc + $fc + $gc + $pc + $xc + $mc, 'messages' => $dc, 'friends' => $rc));
			return json_encode($output);
		} else {
			// Define the arrays that holds the values (prevents the array_merge to fail, when one or more options are disabled)
			$mentions = array();
			$likes = array();
			$comments = array();
			$shares = array();
			$friends = array();
			$chats = array();
			$birthdays = array();
			$pages = array();
			$groups = array();
			$pokes = array();

			if($type) {
				// Get the events and display all unread messages [applies only to the drop down widgets]
				if($for == 2 && $type !== 2 || !$for && $type !== 2) {
					if($mn) {
						// Check for new likes events
						$checkMentions = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '11' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkMentions->fetch_assoc()) {
							$mentions[] = $row;
						}
					}

					if($ln) {
						// Check for new likes events
						$checkLikes = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '2' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkLikes->fetch_assoc()) {
							$likes[] = $row;
						}
					}

					if($pn) {
						// Check for new likes events
						$checkPokes = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '8' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkPokes->fetch_assoc()) {
							$pokes[] = $row;
						}
					}

					if($cn) {
						// Check for new comments events
						$checkComments = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '1' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkComments->fetch_assoc()) {
							$comments[] = $row;
						}
					}

					if($sn) {
						// Check for new shared events
						$checkShares = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '3' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkShares->fetch_assoc()) {
							$shares[] = $row;
						}
					}

					if($fn) {
						// Check for new friendship events
						$checkFriends = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' AND `notifications`.`from` <> '%s' AND `notifications`.`type` = '5' AND `notifications`.`read` = '0' ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkFriends->fetch_assoc()) {
							$friends[] = $row;
						}
					}

					if($xn) {
						// Check for new group invitations
						$checkPages = $this->db->query(sprintf("SELECT `notifications`.`id`, `notifications`.`from`, `notifications`.`to`, `notifications`.`parent`, `notifications`.`type`, `notifications`.`read`,  `notifications`.`time`, `users`.`username`, `users`.`first_name`, `users`.`last_name`, `users`.`image`, `pages`.`title`, `pages`.`name` FROM `notifications`,`users`,`pages` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '9' AND `notifications`.`read` = '0' AND `pages`.`id` = `notifications`.`parent` ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkPages->fetch_assoc()) {
							$pages[] = $row;
						}
					}

					if($gn) {
						// Check for new group invitations
						$checkGroups = $this->db->query(sprintf("SELECT `notifications`.`id`, `notifications`.`from`, `notifications`.`to`, `notifications`.`parent`, `notifications`.`type`, `notifications`.`read`,  `notifications`.`time`, `users`.`username`, `users`.`first_name`, `users`.`last_name`, `users`.`image`, `groups`.`title`, `groups`.`name` FROM `notifications`,`users`,`groups` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '6' AND `notifications`.`read` = '0' AND `groups`.`id` = `notifications`.`parent` ORDER BY `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

						while($row = $checkGroups->fetch_assoc()) {
							$groups[] = $row;
						}
					}
				}
				// Return the unread messages for drop-down notification (excludes $for 2 and $type 2)
				elseif($type !== 2 && $for == 1) {
					if($dn) {
						$checkChats = $this->db->query(sprintf("SELECT * FROM `conversations`, `chat`, `users` WHERE `conversations`.`to` = '%s' AND `conversations`.`read` = '0' AND `conversations`.`cid` = `chat`.`id` AND `conversations`.`from` = `users`.`idu` ORDER BY `cid` DESC", $this->db->real_escape_string($this->id)));

						// If there are no unread chat messages, select the latest messages*/
						if($checkChats->num_rows < 1) {
							$checkChats = $this->db->query(sprintf("SELECT * FROM `conversations`, `chat`, `users` WHERE `conversations`.`to` = '%s' AND `conversations`.`cid` = `chat`.`id` AND `conversations`.`from` = `users`.`idu` ORDER BY `cid` DESC LIMIT %s", $this->db->real_escape_string($this->id), $limit));
						}

						while($row = $checkChats->fetch_assoc()) {
							$chats[] = $row;
						}
					}
				}
				// Return the unread requests for the drop-down notifications (excludes $for 4 and $type 2)
				elseif($type !== 2 && $for == 3) {
					$checkFriends = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '4' ORDER BY `notifications`.`read` ASC, `notifications`.`id` DESC", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id)));

					while($row = $checkFriends->fetch_assoc()) {
						$friends[] = $row;
					}
				}

				// If there are no new (unread) notifications (for the drop-down widgets), get the lastest notifications
				if(!$for) {
					// Verify for the drop-down notifications
					if(empty($mentions) && empty($likes) && empty($comments) && empty($shares) && empty($friends) && empty($chats) && empty($pages) && empty($groups) && empty($pokes) || $type == 2) {
						$all = 1;
					}
				}
				// For the Notifications Page
				elseif($for == 2 && $type == 2) {
					// Verify for the notifications page
					$all = 1;
				}
				elseif($for == 3 && $type == 1) {
					// Verify for the drop-down notifications
					if(empty($friends) || $type == 2) {
						$all = 1;
					}
				} elseif($for == 1 && $type == 1) {
					// Verify for the drop-down notifications
					if(empty($chats) || $type == 1) {
						$all = 1;
					}
				}

				if(isset($all)) {
					// If the user is on the dedicated notification page
					if($type == 2 && $for == 2) {
						// Notifications page style flag
						$ns = 1;

						// If the user is on a dedicated notification page
						if((isset($ln) && isset($cn) && isset($sn) && isset($fn) && isset($dn) && isset($bn) && isset($gn) && isset($pn) && isset($xn)) == false) {
							if($start) {
								$start = sprintf("AND `notifications`.`id` < '%s'", $this->db->real_escape_string($start));
							}
							$per_page = $limit;
							$limit = $limit + 1;
						}
					}
					// If the request is made for the Chat Messages, prevent loading the rest of the notifications
					if($for != 1) {
						if($mn) {
							$checkMentions = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '11' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkMentions->fetch_assoc()) {
								$mentions[] = $row;
							}
						}

						if($ln) {
							$checkLikes = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '2' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkLikes->fetch_assoc()) {
								$likes[] = $row;
							}
						}

						if($pn) {
							$checkPokes = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '8' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkPokes->fetch_assoc()) {
								$pokes[] = $row;
							}
						}

						if($cn) {
							$checkComments = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '1' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkComments->fetch_assoc()) {
								$comments[] = $row;
							}
						}

						if($sn) {
							$checkShares = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '3' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkShares->fetch_assoc()) {
								$shares[] = $row;
							}
						}

						if($xn) {
							$checkPages = $this->db->query(sprintf("SELECT `notifications`.`id`, `notifications`.`from`, `notifications`.`to`, `notifications`.`parent`, `notifications`.`type`, `notifications`.`read`, `notifications`.`time`, `users`.`username`, `users`.`first_name`, `users`.`last_name`, `users`.`image`, `pages`.`title`, `pages`.`name` FROM `notifications`,`users`,`pages` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' AND `notifications`.`from` <> '%s' AND `notifications`.`type` = '9' AND `pages`.`id` = `notifications`.`parent` %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkPages->fetch_assoc()) {
								$pages[] = $row;
							}
						}

						if($gn) {
							$checkGroups = $this->db->query(sprintf("SELECT `notifications`.`id`, `notifications`.`from`, `notifications`.`to`, `notifications`.`parent`, `notifications`.`type`, `notifications`.`read`, `notifications`.`time`, `users`.`username`, `users`.`first_name`, `users`.`last_name`, `users`.`image`, `groups`.`title`, `groups`.`name` FROM `notifications`,`users`,`groups` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '6' AND `groups`.`id` = `notifications`.`parent` %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkGroups->fetch_assoc()) {
								$groups[] = $row;
							}
						}
					}
					// On the notifications center show the confirmed friendships
					if($for == 2) {
						if($fn) {
							$checkFriends = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '5' %s ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $start, $limit));

							while($row = $checkFriends->fetch_assoc()) {
								$friends[] = $row;
							}
						}
						if($bn) {
							$friendslist = $this->getFriendsList(1);
							if(!empty($friendslist)) {
								$checkBirthdays = $this->db->query(sprintf("SELECT `idu` AS `id`, `username`, `first_name`, `last_name`, `image`, `born` FROM `users` WHERE EXTRACT(MONTH FROM `born`) = '%s' AND EXTRACT(DAY FROM `born`) = '%s' AND `idu` IN (%s) %s ORDER BY `idu` ASC LIMIT %s", date('m'), date('d'), $friendslist, str_replace('`notifications`.`id` <', '`users`.`idu` >', $start), $limit));
								while($row = $checkBirthdays->fetch_assoc()) {
									$birthdays[] = $row;
								}
							}
						}
					}
					// On the notifications widget show the unconfirmed friendships
					else {
						// Make the request only if is for the global notifications widget (avoids showing up in the friends requests widget)
						if(!$for) {
							if($fn) {
								$checkFriends = $this->db->query(sprintf("SELECT * FROM `notifications`, `users` WHERE `notifications`.`from` = `users`.`idu` AND `notifications`.`to` = '%s' and `notifications`.`from` <> '%s' AND `notifications`.`type` = '5' ORDER BY `notifications`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $limit));

								while($row = $checkFriends->fetch_assoc()) {
									$friends[] = $row;
								}
							}
						}
					}

					if($for == 2) {
						if($dn) {
							$checkChats = $this->db->query(sprintf("SELECT * FROM `conversations`, `chat`, `users` WHERE `conversations`.`to` = '%s' AND `conversations`.`cid` = `chat`.`id` AND `conversations`.`from` = `users`.`idu` %s ORDER BY `cid` DESC LIMIT %s", $this->db->real_escape_string($this->id), str_replace('`notifications`.`id` <', '`conversations`.`cid` <', $start), $limit));

							while($row = $checkChats->fetch_assoc()) {
								$chats[] = $row;
							}
						}
					}

					// If there are no latest notifications
					if($for == 2) {
						// Verify for the notifications page
						if(empty($mentions) && empty($likes) && empty($comments) && empty($shares) && empty($friends) && empty($chats) && empty($birthdays) && empty($pages) && empty($groups) && empty($pokes)) {
							return '<div class="message-content"><div class="message-header">'.$LNG['no_notifications'].'</div><div class="message-inner"><a href="'.permalink($this->url.'/index.php?a=settings&b=notifications').'" rel="loadpage">'.$LNG['notifications_settings'].'</a></div></div>';
						}
					} else {
						// Verify for the drop-down notifications
						if($for == 3) {
							$mentions = array(); $likes = array(); $comments = array(); $shares = array(); $chats = array(); $pages = array(); $groups = array(); $pokes = array();
						}
						if(empty($mentions) && empty($likes) && empty($comments) && empty($shares) && empty($friends) && empty($chats) && empty($pages) && empty($groups) && empty($pokes)) {
							return '<div class="notification-row"><div class="notification-padding">'.$LNG['no_notifications'].'</a></div></div>';
						}
					}
				}
			}

			// Add the types into the recursive array results
			$x = 0;
			foreach($likes as $like) {
				$likes[$x]['event'] = 'like';
				$x++;
			}
			$y = 0;
			foreach($comments as $comment) {
				$comments[$y]['event'] = 'comment';
				$y++;
			}
			$z = 0;
			foreach($shares as $share) {
				$shares[$z]['event'] = 'shared';
				$z++;
			}
			$a = 0;
			foreach($friends as $friend) {
				$friends[$a]['event'] = 'friend';
				$a++;
			}
			$b = 0;
			foreach($chats as $chat) {
				$chats[$b]['event'] = 'chat';
				$b++;
			}
			$c = 0;
			foreach($birthdays as $birthday) {
				$birthdays[$c]['event'] = 'birthday';
				$c++;
			}
			$d = 0;
			foreach($groups as $group) {
				$groups[$d]['event'] = 'group';
				$d++;
			}
			$e = 0;
			foreach($pokes as $poke) {
				$pokes[$e]['event'] = 'poke';
				$e++;
			}
			$f = 0;
			foreach($pages as $page) {
				$pages[$f]['event'] = 'page';
				$f++;
			}
			$g = 0;
			foreach($mentions as $mention) {
				$mentions[$g]['event'] = 'mention';
				$g++;
			}

			$array = array_merge($likes, $comments, $shares, $friends, $chats, $birthdays, $pages, $groups, $pokes, $mentions);

			// Sort the array based on publish date, except when on dedicated notifications pages
			if(isset($per_page) == false) {
				usort($array, 'sortDateAsc');
			}

			if(isset($ns)) {
				// Define the $loadmore variable
				$loadmore = '';

				// If there are more results available than the limit, then show the Load More
				if(isset($per_page) && array_key_exists($per_page, $array)) {
					$loadmore = 1;

					// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
					array_pop($array);
				}
			}

			$i = 0;
			$currentTime = time();
			$events = '';
			foreach($array as $value) {
				$time = $value['time']; $b = '';
				if($this->time == '0') {
					$time = date("c", strtotime($value['time']));
				} elseif($this->time == '2') {
					$time = $this->ago(strtotime($value['time']));
				} elseif($this->time == '3') {
					$date = strtotime($value['time']);
					$time = date('Y-m-d', $date);
					$b = '-standard';
				}

				if($type == 2 && $for == 2) {
					$events .= '<div class="users-container"><div class="message-content'.(($value['read'] == 0 && $value['event'] == 'chat') ? ' notification-unread' : '').'" id="notification'.$value['id'].'"><div class="message-inner">';
					$notification_image = 'message-avatar';
					$notification_text = 'message-top';
					$notification_icon = ' class="message-icon"';
					if($value['event'] == 'chat') {
                        $chat_snippet = ': <span class="chat-snippet">'.$this->parseMessage(substr($value['message'], 0, 45)).'...</span>';
                    }
				} else {
					$events .= '<div class="notification-row'.((($value['read'] == 0 && $value['event'] == 'chat') || ($value['read'] == 0 && $value['event'] == 'friend' && $for == 3)) ? ' notification-unread' : '').'" id="notification'.$value['id'].'"><div class="notification-padding">';
					$notification_image = 'notification-image';
					$notification_text = 'notification-text';
					$notification_icon = '';
                    if($value['event'] == 'chat') {
                        $chat_snippet = '<br><span class="chat-snippet">'.$this->parseMessage(substr($value['message'], 0, 45)).'...</span>';
                    }
				}
				if($value['event'] == 'like') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf(($value['child'] ? $LNG['new_like_c_notification'] : $LNG['new_like_notification']), permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), ($value['child'] ? permalink($this->url.'/index.php?a=post&m='.$value['parent'].'#comment'.$value['child']) : permalink($this->url.'/index.php?a=post&m='.$value['parent']))).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_like.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'comment') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_comment_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=post&m='.$value['parent'].'#comment'.$value['child'])).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_comment.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'poke') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_poke_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name'])).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_poke.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'shared') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_shared_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=post&m='.$value['child'])).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_shared.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'page') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_page_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=page&name='.$value['name']), $value['title']).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_page.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'group') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_group_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=group&name='.$value['name']), $value['title']).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_group.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'friend') {
					if($for == 2 || !$for) {
						$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_friend_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=post&m='.$value['child'])).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_friendship.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
					} else {
						$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="notification-text notification-friendships"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage">'.realName($value['username'], $value['first_name'], $value['last_name']).'</a><br><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div><div class="notification-buttons" id="notification-buttons'.$value['id'].'"><div class="notification-button button-normal"><a onclick="friend(\''.$value['idu'].'\', \'3\', \''.$value['id'].'\')">'.$LNG['decline'].'</a></div><div class="notification-button button-active"><a onclick="friend(\''.$value['idu'].'\', \'2\', \''.$value['id'].'\')">'.$LNG['confirm'].'</a></div></div>';
					}
				} elseif($value['event'] == 'chat') {
					if(($currentTime - $value['online']) > $this->online_time) {
						$icon = 'offline';
					} else {
						$icon = 'online';
					}
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_chat_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), 'openChatWindow(\''.$value['idu'].'\', \''.$value['username'].'\', \''.addslashes(realName($value['username'], $value['first_name'], $value['last_name'])).'\', \''.$this->url.'\', \''.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png\')', permalink($this->url.'/index.php?a=messages&u='.$value['username'].'&id='.$value['idu'])).$chat_snippet.'<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_chat.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				} elseif($value['event'] == 'birthday') {
					// Explode the born value [[0]=>Y,[1]=>M,[2]=>D];
					$born = explode('-', $value['born']);

					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf($LNG['new_birthday_notification'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name'])).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_birthday.png" width="16" height="16"'.$notification_icon.'><span class="timeago">'.sprintf($LNG['years_old'], (date('Y')-$born[0])).'</span></div>';
				} elseif($value['event'] == 'mention') {
					$events .= '<div class="'.$notification_image.'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="'.$notification_text.'">'.sprintf(($value['child'] ? $LNG['new_like_c_mention'] : $LNG['new_like_mention']), permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), ($value['child'] ? permalink($this->url.'/index.php?a=post&m='.$value['parent'].'#comment'.$value['child']) : permalink($this->url.'/index.php?a=post&m='.$value['parent']))).'.<br><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_mention.png" width="16" height="16"'.$notification_icon.'><span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
				}

				if(isset($ns)) {
					$events .= '</div></div></div>';
				} else {
					$events .= '</div></div>';
				}
				$i++;
			}

			if(!$for) {
				// Mark global notifications as read
				$this->db->query("UPDATE `notifications` SET `read` = '1', `time` = `time` WHERE `to` = '{$this->id}' AND `read` = '0' AND `type` <> '4'");
			}
			elseif($for == 3) {
				// Mark friend notifications as read
				$this->db->query("UPDATE `notifications` SET `read` = '1', `time` = `time` WHERE `to` = '{$this->id}' AND `read` = '0' AND `type` = '4'");
			}
			// Update when the $for is set, and it's not viewed from the Notifications Page
			elseif($type !== 2) {
				// Mark chat messages notifications as read
				$this->db->query("UPDATE `chat` SET `read` = '1', `time` = `time` WHERE `to` = '{$this->id}' AND `read` = '0'");
				$this->db->query("UPDATE `conversations` SET `read` = '1' WHERE `to` = '{$this->id}' AND `read` = '0'");
			}

			// Show the pagination button if on notifications page
			if(isset($ns) && $loadmore) {
				$events .= sprintf('<div class="load_more" id="more_users"><a onclick="loadNotifications(\'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\', \'%s\')" id="load-more">%s</a></div>', $value['id'], $ln, $cn, $sn, $fn, $dn, $bn, $gn, $pn, $xn, $mn, $LNG['view_more_messages']);
			}
			// return the result
			return $events;
		}
		// If no notification was returned, return 0
	}

	function chatButton($id, $username, $z = null) {
		// Profile: Returns the current row username
		// Z: A switcher for the sublist CSS class
		global $LNG;
		if($z == 1) {
			$style = ' subslist_message';
		}
		if(!empty($this->username) && $this->username !== $username) {
			return '<a href="'.permalink($this->url.'/index.php?a=messages&u='.$username.'&id='.$id).'" title="'.$LNG['send_message'].'" rel="loadpage"><div class="message_btn'.$style.'"></div></a>';
		}
	}

	function friendship($type = null, $list = null, $z = null) {
		global $LNG;
		// Type 0: Show the button
		// Type 1: Go trough the add friend query
		// List: Array (for the dedicated profile page list)
		// $z 1: A switcher for the sublist CSS class
		// $z 2: Request from the notifications widget to confirm the friendship
		// $z 3: Request from the notifications widget to decline the friendship

		// Return if the user is not logged in
		if(!$this->id) {
			return false;
		}
		if($list) {
			$profile = $list;
		} else {
			$profile = $this->profile_data;
		}
		// If the user is not a confirmed one
		if(isset($profile['suspended']) && $profile['suspended'] == 2) {
			return false;
		}
		$style = '';
		// Verify if the username is logged in, and it's not the same with the viewed profile
		if(!empty($this->username) && $this->username !== $profile['username']) {
			if($z == 1) {
				$style = ' subslist';
			}

			if($type) {
				$friendship = $this->verifyFriendship($this->id, $this->db->real_escape_string($profile['idu']));
				// If the friendship status is confirmed OR if the friendship status is pending and the sender is the owner OR the request is to delete the friendship request then cancel the friendship
				if($friendship['status'] == '1' || ($friendship['status'] == '0' && $friendship['from'] == $this->id) || ($friendship['to'] == $this->id && $type == 3)) {
					$result = $this->db->query(sprintf("DELETE FROM `friendships` WHERE (`user1` = '%s' AND `user2` = '%s') OR (`user1` = '%s' AND `user2` = '%s')", $this->db->real_escape_string($this->id), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($this->id)));

					$deleteNotification = $this->db->query(sprintf("DELETE FROM `notifications` WHERE ((`from` = '%s' AND `to` = '%s') OR (`from` = '%s' AND `to` = '%s')) AND `type` IN (4,5)", $this->db->real_escape_string($this->id), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($this->id)));

					// If the decline was done from the notifications widget
					if($type == 3) {
						return '<div class="notification-button button-normal"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$profile['username']).'" target="_blank">'.$LNG['declined'].'</a></div>';
					}
				}
				// If there is a pending invitation
				elseif($friendship['status'] == '0' && $friendship['to'] == $this->id && ($type == 1 || $type == 2)) {
					// Verify the current amount of friends
					$currFriends = $this->countFriends($this->id, 1);
					$targetFriends = $this->countFriends($profile['idu'], 1);

					// Show the maximum limit exceeded when on the notifications widget
					if($currFriends >= $this->friends_limit || $targetFriends >= $this->friends_limit) {
						if($type == 2) {
							if($currFriends >= $this->friends_limit) {
								return sprintf($LNG['friends_limit']);
							} else {
								return sprintf($LNG['user_friends_limit']);
							}
						}
					} else {
						$result = $this->db->query(sprintf("UPDATE `friendships` SET `status` = '1' WHERE (`user1` = '%s' AND `user2` = '%s') OR (`user1` = '%s' AND `user2` = '%s')", $this->db->real_escape_string($this->id), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($this->id)));

						// If user has emails on new friendships enabled
						if($profile['email_new_friend']) {
							// Send e-mail
							sendMail($profile['email'], sprintf($LNG['ttl_friendship_confirmed_email'], $this->username), sprintf($LNG['friendship_confirmed_email'], realName($profile['username'], $profile['first_name'], $profile['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, $this->title, $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->email);
						}

						$updateNotification = $this->db->query(sprintf("UPDATE `notifications` SET `type` = '5', `read` = '0', `to` = '%s', `from` = '%s' WHERE `from` = '%s' AND `to` = '%s' AND `type` = 4", $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($this->id), $this->db->real_escape_string($profile['idu']), $this->db->real_escape_string($this->id)));

						// If the approve was done from the notifications widget
						if($type == 2) {
							return '<div class="notification-button button-normal"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$profile['username']).'" target="_blank">'.$LNG['confirmed'].'</a></div>';
						}
					}
				}
				// If there are no friendship relations
				else {
					$currFriends = $this->countFriends($this->id, 1);
					$targetFriends = $this->countFriends($profile['idu'], 1);

					// If the user & the target has less than the maximum amount
					if($currFriends < $this->friends_limit || $targetFriends < $this->friends_limit) {
						// If the user is not blocked
						if(!$this->getBlocked($profile['idu'], 2)) {
							$result = $this->db->query(sprintf("INSERT INTO `friendships` (`user1`, `user2`, `time`) VALUES ('%s', '%s', CURRENT_TIMESTAMP)", $this->db->real_escape_string($this->id), $this->db->real_escape_string($profile['idu'])));

							$insertNotification = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `type`, `read`) VALUES ('%s', '%s', '4', '0')", $this->db->real_escape_string($this->id), $profile['idu']));

							if($this->email_new_friend) {
								// If user has emails on new friendships enabled
								if($profile['email_new_friend']) {
									// Send e-mail
									sendMail($profile['email'], sprintf($LNG['ttl_new_friend_email'], $this->username), sprintf($LNG['new_friend_email'], realName($profile['username'], $profile['first_name'], $profile['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, $this->title, $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->email);
								}
							}
						}
					}
				}
			}
		} else {
			return false;
		}

		$friendship = $this->verifyFriendship($this->id, $this->db->real_escape_string($profile['idu']));

		if($friendship['status'] == '1') {
			return '<div class="friend-button friend-remove'.$style.'" title="'.$LNG['remove_friend'].'" onclick="friend('.$profile['idu'].', 1'.(($z == 1) ? ', 1' : '').')"></div>';
		} elseif($friendship['status'] == '0') {
			return '<div class="friend-button friend-pending'.$style.'" title="'.(($this->id == $friendship['from']) ? $LNG['friend_request_sent'] : $LNG['friend_request_accept']).'" onclick="friend('.$profile['idu'].', 1'.(($z == 1) ? ', 1' : '').')"></div>';
		} else {
			return '<div class="friend-button'.$style.'" title="'.$LNG['add_friend'].'" onclick="friend('.$profile['idu'].', 1'.(($z == 1) ? ', 1' : '').')"></div>';
		}
	}

	function showError($error) {
		global $LNG;
		$message = '<div class="message-container"><div class="message-content"><div class="message-header">'.$LNG[$error.'_ttl'].'</div><div class="message-inner">'.$LNG["$error"].'</div></div></div>';

		return array($message, 1);
	}
	

	function showWelcome($message = null) {
		global $LNG;
		$output = '<div class="message-container"><div class="wf-img"></div><div class="wf-title">'.$LNG['welcome_feed_ttl'].'</div><div class="wf-text">'.$LNG['welcome_feed'].'</div></div>';
		return $output;
	}

	function verifyFriendship($user_id, $profile_id) {
		if($user_id == $profile_id) {
			$result = array();
			$result['status'] = 'owner';
			$result['user1'] = null;
			$result['user2'] = null;
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `friendships` WHERE ((`user1` = '%s' AND `user2` = '%s') OR (`user1` = '%s' AND `user2` = '%s'))", $this->db->real_escape_string($user_id), $this->db->real_escape_string($profile_id), $this->db->real_escape_string($profile_id), $this->db->real_escape_string($user_id)));

			$result = $query->fetch_assoc();
		}
		// Returns the friendship status
		// Status: 	0 Pending
		//			1 Confirmed

		return array(	'status'	=> $result['status'] ?? null,
						'from'		=> $result['user1'] ?? null,
						'to'		=> $result['user2'] ?? null);
	}

	function getMessage($id) {
		$query = $this->db->query(sprintf("SELECT `idu`,`username`,`private`,`public` FROM `messages`, `users` WHERE `messages`.`id` = '%s' AND `messages`.`uid` = `users`.`idu`", $this->db->real_escape_string($id)));
		$result = $query->fetch_assoc();

		// If the current user is not the owner of the message
		if($result['idu'] !== $this->id) {
			$friendship = $this->verifyFriendship($this->id, $result['idu']);

			// Verify if the message
			if(!$result['public']) {
				$private = 1;
			} elseif($result['public'] == 2 && $friendship['status'] !== '1') {
				$private = 1;
			}
		}

		if($this->is_admin) {
			$private = 0;
		}

		if(isset($private) && $private) {
			return $this->showError(($result['public'] == 2) ? 'message_semi_private' : 'message_private');
		} else {
			// Get the message for Messages Page
			$query = sprintf("SELECT * FROM messages, users WHERE messages.id = '%s' AND messages.uid = users.idu", $this->db->real_escape_string($id));

			return $this->getMessages($query, null, null);
		}
	}

	function getLastMessage() {
		$query = sprintf("SELECT * FROM `messages`, `users` WHERE `uid` = '%s' AND `messages`.`uid` = `users`.`idu` ORDER BY `id` DESC LIMIT 0, 1", $this->db->real_escape_string($this->id));

		$message = $this->getMessages($query, null, null);
		return $message[0];
	}

	function getComments($id, $cid, $start, $owner = null) {
		// The query to select the subscribed users

		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND comments.id < \''.$this->db->real_escape_string($cid).'\'';
		}
		$query = sprintf("SELECT * FROM comments, users WHERE comments.mid = '%s' AND comments.uid = users.idu %s ORDER BY comments.id DESC LIMIT %s", $this->db->real_escape_string($id), $start, ($this->c_per_page + 1));

		return $this->comments($query, array('id' => $id, 'start' => $start, 'owner' => $owner));
	}

	function getLastComment() {
		$query = sprintf("SELECT * FROM `comments`, `users` WHERE `uid` = '%s' AND `comments`.`uid` = `users`.`idu` ORDER BY `id` DESC LIMIT 0, 1", $this->db->real_escape_string($this->id));

		return $this->comments($query);
	}

	function getMessageOwner($id) {
		$query = $this->db->query(sprintf("SELECT `uid` FROM `messages` WHERE `id` = '%s'", $this->db->real_escape_string($id)));

		return $query->fetch_assoc();
	}

	function getCommentActions($id, $time = null, $likes = null, $update = null) {
		global $LNG;

		$b = '';
		if($this->time == '0') {
			$time = date("c", strtotime($time));
		} elseif($this->time == '2') {
			$time = $this->ago(strtotime($time));
		} elseif($this->time == '3') {
			$date = strtotime($time);
			$time = date('Y-m-d', $date);
			$b = '-standard';
		}

		if($update) {
			$stats = $this->getCachedStats($id, 1);
			$likes = $stats['likes'];
		}

		// Verify the Like state
		$verify = $this->verifyLike($id, 1);

		if($verify) {
			$state = $LNG['dislike'];
			$y = 2;
		} else {
			$state = $LNG['like'];
			$y = 1;
		}

		// If the current user is not empty
		if(empty($this->id)) {
			// Output variable
			$likeUrl = '<a href="'.$this->url.'/" rel="loadpage" title="'.$LNG['login_to_lcs'].'">'.$state.'</a>';
		} else {
			$likeUrl = '<a onclick="doLike('.$id.', 1)" id="doLikeC'.$id.'">'.$state.'</a>';
		}

		// Output variable
		$actions = '<div class="message-time"><span class="like-comment">'.$likeUrl.' -&nbsp;</span>
		<span id="time-c-'.$id.'"><div class="timeago'.$b.'" title="'.$time.'">
			'.$time.'
		</div></span>';

		if($likes > 0) {
			$actions .= '<a onclick="likesModal('.$id.', 1)" title="'.$LNG['view_who_liked'].'" id="acl'.$id.'"><div class="actions_btn like_btn"> '.$likes.'</div></a>';
		}

		$actions .= '<div class="actions_btn loader" id="action-c-loader'.$id.'"></div></div>';

		return $actions;
	}

	function comments($query, $values = null) {
		global $LNG, $CONF;
		// check if the query was executed

		if($result = $this->db->query($query)) {
			// Set the result into an array
			$rows = array();
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}
			$rows = array_reverse($rows);

			// Define the $comments variable;
			$comments = $load = $loadmore = '';

			// If there are more results available than the limit, then show the Load More Comments
			if(array_key_exists($this->c_per_page, $rows)) {
				$loadmore = 1;

				// Unset the first array element because it's not needed, it's used only to predict if the Load More Comments should be displayed
				unset($rows[0]);
			}

			foreach($rows as $comment) {
				$menu = $item = $commentMedia = $verified = '';
				// If the user is logged-in
				if($this->username) {
					$menu = '<div class="message-menu comment-menu" onclick="messageMenu('.$comment['id'].', 4)"></div>';

					// If it's the comment author
					if($this->username == $comment['username']) {
						$item .= '<div class="message-menu-row" onclick="edit_comment('.$comment['id'].', 0, '.$comment['mid'].')" id="edit_text_c'.$comment['id'].'">'.$LNG['edit'].'</div>';
					}
					// If it's the comment author, or if is the Page owner
					if($this->username == $comment['username'] || $values['owner']) {
						$item .= '<div class="message-menu-row" onclick="deleteModal('.$comment['id'].', 0, '.$comment['mid'].')">'.$LNG['delete'].'</div>';
					}
					// If the current username is not the same as the author
					if(!empty($this->username) && $this->username !== $comment['username']) {
						$item .= '<div class="message-menu-row" onclick="report_the('.$comment['id'].', 0)">'.$LNG['report'].'</div>';
					}

					$menu .= '<div id="comment-menu'.$comment['id'].'" class="message-menu-container">'.$item.'</div>';
				}

				$name = realName($comment['username'], $comment['first_name'], $comment['last_name']);
				if(isset($this->page_data['by']) && $this->page_data['by'] == $comment['idu']) {
					$name = $this->page_data['title'];
					$comment['image'] = $this->page_data['image'];
                    if ($this->page_data['verified']) {
                        $verified = '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_page'].'"></span>';
                    }
				} else {
                    if ($comment['verified']) {
                        $verified = '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>';
                    }
                }

				if($comment['type'] == 'picture') {
					$commentMedia = '<div class="comment-image-thumbnail"><a onclick="gallery(\''.$comment['value'].'\', '.$comment['id'].', \'media\', 2)" id="'.$comment['value'].'"><img src="'.permalink($this->url.'/image.php?t=m&w=200&h=200&src='.$comment['value']).'"></a></div>';
				}

				// Variable which contains the result
				$comments .= '
				<div class="message-reply-container" id="comment'.$comment['id'].'">
					'.$menu.'
					<div class="message-reply-avatar" id="avatar-c-'.$comment['id'].'">
						<a href="'.permalink($this->url.'/index.php?a=profile&u='.$comment['username']).'" rel="loadpage"><img onmouseover="profileCard('.$comment['idu'].', '.$comment['id'].', 1, 0, 0)" onmouseout="profileCard(0, 0, 1, 1, 0);" onclick="profileCard(0, 0, 1, 1, 0);" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$comment['image']).'"></a>
					</div>
					<div class="message-reply-message">
						<span class="message-reply-author" id="author-c-'.$comment['id'].'"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$comment['username']).'" rel="loadpage">'.$name.'</a></span>'.$verified.': <span id="comment_text'.$comment['id'].'">'.$this->parseMessage($comment['message']).'</span>
						'.$commentMedia.'
					</div>
					<div class="message-reply-footer" id="comment-action'.$comment['id'].'">
						'.$this->getCommentActions($comment['id'], $comment['time'], $comment['likes']).'
					</div>
					<div class="delete_preloader" id="del_comment_'.$comment['id'].'"></div>
				</div>';
				$message_id = $comment['mid'];
			}

			if($loadmore && $this->c_per_page) {
				$load = '<div class="load-more-comments" id="more_comments_'.htmlentities($values['id'], ENT_QUOTES).'"><a onclick="loadComments('.$message_id.', '.$rows[1]['id'].', '.((int)$values['start'] + $this->c_per_page).')">'.$LNG['view_more_comments'].'</a></div>';
			}

			// Close the query
			$result->close();

			// Return the comments variable
			return $load.$comments;
		} else {
			return false;
		}
	}

	function like($id, $type, $action = null) {
		global $LNG;
		// Type 0: Like Message
		// Type 1: Like Comment

		if($type == 1) {
			$special = ', `comments`';
			$table = 'comments';
			$extra = 'WHERE `comments`.`mid` = `messages`.`id` AND';
		} else {
			$special = '';
			$table = 'messages';
			$extra = 'WHERE ';
		}

		// Select the comment's likes (if the comments exists)
		$query = $this->db->query(sprintf("SELECT * FROM `users`, `messages` %s %s `%s`.`id` = '%s' AND `%s`.`uid` = `users`.`idu`", $special, $extra, $table, $this->db->real_escape_string($id), $table));
		$post = $query->fetch_assoc();

		// If the comment does not exists
		if(empty($post['id'])) {
			return false;
		}

		// Select the likes, if any
		$query = $this->db->query(sprintf("SELECT * FROM `likes`, `users` WHERE `likes`.`post` = '%s' AND `likes`.`type` = '%s' AND `likes`.`by` = '%s' AND `likes`.`by` = `users`.`idu`", $this->db->real_escape_string($id),  $this->db->real_escape_string($type), $this->db->real_escape_string($this->id)));

		// If a like already exists, dislike
		if($query->num_rows > 0) {
			$this->db->query(sprintf("DELETE FROM `likes` WHERE `type` = '%s' AND `post` = '%s' AND `by` = '%s'", $this->db->real_escape_string($type), $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$this->db->query(sprintf("UPDATE `%s` SET `likes` = `likes` -1, `time` = `time` WHERE id = '%s'", $table, $this->db->real_escape_string($id)));
			$value = $LNG['like'];
			$action = 0;
		} else {
			$this->db->query(sprintf("INSERT INTO `likes` (`post`, `by`, `type`) VALUES ('%s', '%s', '%s')", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id), $this->db->real_escape_string($type)));
			$this->db->query(sprintf("UPDATE `%s` SET `likes` = `likes` + 1, `time` = `time` WHERE id = '%s'", $table, $this->db->real_escape_string($id)));
			$value = $LNG['dislike'];
			$action = 1;
		}

		if($type == 1) {
			$parent = $post['mid'];
			$child = $post['id'];
			$email_url = $this->url.'/index.php?a=post&m='.$parent.'#comment'.$child;
			$email_content = $LNG['like_c_email'];
			$email_title = $LNG['ttl_like_c_email'];
			$actions = $this->getCommentActions($id, $post['time'], null, true);
		} else {
			$parent = $post['id'];
			$child = 0;
			$email_url = $this->url.'/index.php?a=post&m='.$parent;
			$email_content = $LNG['like_email'];
			$email_title = $LNG['ttl_like_email'];
			$actions = $this->getActions($id, null, null, null, true);
		}

		// If the action is "Like" and the post is not being made for a page
		if($action > 0 && empty($post['page'])) {
			$this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '%s', '2', '0')", $this->db->real_escape_string($this->id), $post['uid'], $parent, $child));
			// If email on likes is enabled in admin settings
			if($this->email_like) {
				// If user has emails on like enabled and it\'s not liking his own post
				if($post['email_like'] && ($this->id !== $post['idu'])) {
					// Send e-mail
					sendMail($post['email'], sprintf($email_title, $this->username), sprintf($email_content, realName($post['username'], $post['first_name'], $post['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, $email_url, $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->email);
				}
			}
		} else {
			$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` = '%s' AND `child` = '%s' AND `type` = '2' AND `from` = '%s'", $parent, $child, $this->db->real_escape_string($this->id)));
		}

		// Return the output
		return json_encode(array('value' => $value, 'type' => $action, 'actions' => $actions));
	}

	function getCachedStats($id, $type) {
		if($type) {
			$table = 'comments';
		} else {
			$table = 'messages';
		}
		$query = $this->db->query(sprintf("SELECT * FROM `%s` WHERE `id` = '%s'", $table, $this->db->real_escape_string($id)));
		return $query->fetch_assoc();
	}

	function parseMessage($message) {
		global $LNG, $CONF;

		// Parse links
		$parseUrl = preg_replace_callback('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', "parseCallback", $message);

		// Parse @mentions and #hashtags
		$parsedMessage = preg_replace(array('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', '/(^|[^a-z0-9_\/])!([a-z0-9_]+)/i', '/(^|[^a-z0-9_\/])#(\w+)/u'), array('$1<a href="'.permalink($this->url.'/index.php?a=profile&u=$2').'" rel="loadpage">@$2</a>', '$1<a href="'.permalink($this->url.'/index.php?a=group&name=$2').'" rel="loadpage">!$2</a>', '$1<a href="'.permalink($this->url.'/index.php?a=search&tag=$2').'" rel="loadpage">#$2</a>'), $parseUrl);

		// Define the censored words
		$censored = explode(',', str_replace(', ', ',', $this->censor));

		// Strip any html tags except anchors, and replace any bad words?
		$parsedMessage = str_replace($censored, $LNG['censored'], $parsedMessage);

		// Define smiles
		$smiles = smiles();

		if($this->smiles) {
			foreach($smiles as $smile => $emoji) {
				$parsedMessage = str_replace($smile, $emoji, $parsedMessage);
			}
		}

		return $parsedMessage;
	}

	function getChatType($type, $value, $id) {
		global $LNG, $CONF;

		$po = '';
		foreach($this->plugins as $plugin) {
			if(array_intersect(array("1"), str_split($plugin['type']))) {
				$po .= plugin($plugin['name'], array('id' => $id, 'type' => $type, 'value' => $value, 'user_id' => $this->id), 1);
			}
		}

		if($po) {
			return $po;
		}

		switch($type) {
			case "picture":
			return '<div class="chat-image-thumbnail"><a onclick="gallery(\''.$value.'\', '.$id.', \'media\', 3)" id="'.$value.'"><img src="'.permalink($this->url.'/image.php?t=m&w=100&h=100&src='.$value).'"></a></div>';
		}
	}

	function getMessageType($type, $value, $id) {
		global $LNG, $CONF;

		$po = '';
		foreach($this->plugins as $plugin) {
			if(array_intersect(array("1"), str_split($plugin['type']))) {
				$po .= plugin($plugin['name'], array('id' => $id, 'type' => $type, 'value' => $value, 'user_id' => $this->id), 1);
			}
		}

		if($po) {
			return $po;
		}

		// Switch the case
		switch($type) {
			// If it's a map
			case "map":
                return '<div class="message-type-general event-place"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/map.svg">'.sprintf($LNG['map'], $value).'</div>
				<div class="message-divider"></div>';
				break;

			// If it's a ate action
			case "food":
				return '<div class="message-type-general event-food"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/food.svg">'.sprintf($LNG['food'], $value).'</div>
				<div class="message-divider"></div>';
				break;

			// If it's a game action
			case "game":
				return '<div class="message-type-general event-game"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/game.svg">'.sprintf($LNG['played'], $value).'</div>
				<div class="message-divider"></div>';
				break;

			// If it's a music/song action
			case "music":
                return '<div class="message-type-general event-music"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/audio.svg">'.sprintf($LNG['listened'], $value).'</div><div class="message-divider"></div>';
				break;

			// If it's a picture
			case "picture":
				$images = explode(',', $value);
				$result = '';
				if(count($images) == 1) {
					$result .= '<div class="message-type-image event-picture"><a onclick="gallery(\''.$images[0].'\', '.$id.', \'media\', 1)" id="'.$images[0].'"><img src="'.permalink($this->url.'/image.php?t=m&w=650&h=300&src='.$images[0]).'"></a>';
				} elseif(count($images) == 2) {
					$result .= '<div class="message-type-image event-picture"><div class="image-container-padding">';
					foreach($images as $image) {
						$result .= '<a onclick="gallery(\''.$image.'\', '.$id.', \'media\', 1)" id="'.$image.'"><div class="image-thumbnail-container-half"><div class="image-thumbnail"><img src="'.permalink($this->url.'/image.php?t=m&w=300&h=300&src='.$image).'"></div></div></a>';
					}
					$result .= '</div>';
				} elseif(count($images) == 3) {
					$result .= '<div class="message-type-image event-picture"><div class="image-container-padding">';
					foreach($images as $image) {
						$result .= '<a onclick="gallery(\''.$image.'\', '.$id.', \'media\', 1)" id="'.$image.'"><div class="image-thumbnail-container"><div class="image-thumbnail"><img src="'.permalink($this->url.'/image.php?t=m&w=200&h=200&src='.$image).'"></div></div></a>';
					}
					$result .= '</div>';
				} elseif(count($images) == 4 || count($images) > 4) {
					$result .= '<div class="message-type-image event-picture"><div class="image-container-padding">';
					$i = 1;
					foreach($images as $image) {
						if($i == 1) {
							$result .= '<a onclick="gallery(\''.$image.'\', '.$id.', \'media\', 1)" id="'.$image.'"><div class="fake"><div class="image-thumbnail"><img src="'.permalink($this->url.'/image.php?t=m&w=650&h=300&src='.$image).'"></div></div></a>';
						} else {
							// If there are more than 4 images, hide them
							if($i > 4) {
								$result .= '<a onclick="gallery(\''.$image.'\', '.$id.', \'media\', 1)" id="'.$image.'" style="display: none;"></a>';
							} else {
								$result .= '<a onclick="gallery(\''.$image.'\', '.$id.', \'media\', 1)" id="'.$image.'"><div class="image-thumbnail-container"><div class="image-thumbnail">'.($i == 4 && count($images) > 4 ? '<span class="image-thumbnail-text">+ '.(count($images)-4).'</span>' : '').'<img src="'.permalink($this->url.'/image.php?t=m&w=200&h=200&src='.$image).'"></div></div></a>';
							}
						}
						$i++;
					}
					$result .= '</div>';
				}
				return $result.'</div><div class="message-divider"></div>';
				break;

			// If it's a video
			case "video":
				return '<div class="message-type-general event-video"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/video.svg">'.sprintf($LNG['watched'], $value).'</div><div class="message-divider"></div>';
				break;
			// If it's empty
			case "":
				return false;
				break;
		}
	}

	function deleteCommentsImages($id) {
		$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `comments` WHERE `uid` = '%s' AND `type` = 'picture'", $this->db->real_escape_string($id)));

		$output = '';
		while($row = $query->fetch_assoc()) {
			$output .= $row['value'].',';
		}

		deletePhotos('picture', $output);
	}

	function deleteChatImages($id) {
		$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `chat` WHERE `from` = '%s' OR `to` = '%s' AND `type` = 'picture'", $this->db->real_escape_string($id), $this->db->real_escape_string($id)));

		$output = '';
		while($row = $query->fetch_assoc()) {
			$output .= $row['value'].',';
		}

		deletePhotos('picture', $output);
	}

	function deleteMessagesImages($id, $group = null, $page = null) {
		if($group) {
			$user = '';
			if($id) {
				$user = sprintf(" AND `uid` = '%s'", $this->db->real_escape_string($id));
			}
			$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `messages` WHERE `group` = '%s' AND `type` = 'picture'%s", $this->db->real_escape_string($group), $user));
		} elseif($page) {
			$user = '';
			if($id) {
				$user = sprintf(" AND `uid` = '%s'", $this->db->real_escape_string($id));
			}
			$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `messages` WHERE `page` = '%s' AND `type` = 'picture'%s", $this->db->real_escape_string($page), $user));
		} else {
			$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `messages` WHERE `uid` = '%s' AND `type` = 'picture'", $this->db->real_escape_string($id)));
		}

		$output = '';
		while($row = $query->fetch_assoc()) {
			$output .= $row['value'].',';
		}

		deletePhotos('picture', $output);
	}

	function getMessagesIds($id = null, $group = null, $extra = null, $share = null, $page = null) {
		// Extra: get all the ids posted in a group/page
		if($extra) {
			if($page) {
				$query = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `page` = '%s'%s ORDER BY `id` ASC", $this->db->real_escape_string($extra), $share));
			} else {
				$query = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `group` = '%s'%s ORDER BY `id` ASC", $this->db->real_escape_string($extra), $share));
			}
		} elseif($share) {
			$query = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `type` = 'shared' AND `value` IN (%s) ORDER BY `id` ASC", $this->db->real_escape_string($share)));
		} else {
		    $x = '';
			if($group) {
				$x = " AND `group` = '".$group."'";
			} elseif($page) {
				$x = " AND `page` = '".$page."'";
			}
			$query = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `uid` = '%s'%s ORDER BY `id` ASC", ($id ? $this->db->real_escape_string($id) : $this->db->real_escape_string($this->id)), $x));
		}
		$output = [];
		while($row = $query->fetch_assoc()) {
			$output[] = $row['id'];
		}

		return implode(',', $output);
	}

	function delete($id, $type) {
		// Type 0: Delete Comment
		// Type 1: Delete Message
		// Type 2: Delete Chat Message

		// Prepare the statement
		if($type == 0) {
			// Check if the user is the owner of the message
			$ownership = $this->db->query(sprintf("SELECT `comments`.`uid` as `cuid`, `messages`.`uid` as `muid`, `comments`.`mid` as `mid`, `comments`.`type` as `type`, `comments`.`value` as `value` FROM `comments`, `messages` WHERE `comments`.`id` = '%s' AND `comments`.`mid` = `messages`.`id`", $this->db->real_escape_string($id)));
			$message = $ownership->fetch_assoc();

			// If the logged-in user is the message owner
			if($this->id == $message['muid']) {
				// Take the ownership of the comment
				$this->id = $message['cuid'];
			}

			$stmt = $this->db->prepare("DELETE FROM `comments` WHERE `id` = '{$this->db->real_escape_string($id)}' AND `uid` = '{$this->db->real_escape_string($this->id)}'");

			$x = 0;
		} elseif($type == 1) {
			// Get the current type (for images deletion)
			$query = $this->db->query(sprintf("SELECT `id`, `type`, `value` FROM `messages` WHERE `id` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$message = $query->fetch_assoc();

			$stmt = $this->db->prepare("DELETE FROM `messages` WHERE `id` = '{$this->db->real_escape_string($id)}' AND `uid` = '{$this->db->real_escape_string($this->id)}'");

			$x = 1;
		} elseif($type == 2) {
			// Get the current type (for images deletion)
			$query = $this->db->query(sprintf("SELECT `id`, `type`, `value` FROM `chat` WHERE `id` = '%s' AND `from` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$message = $query->fetch_assoc();

			// Check if there's any other unread messages
			$query_cid = $this->db->query(sprintf("SELECT `id`, `to`, `from` FROM `chat` WHERE `id` != '%s' AND `to` = (SELECT `to` FROM `chat` WHERE `id` = '%s') AND `from` = '%s' AND `read` = 0 ORDER BY `id` DESC LIMIT 1", $this->db->real_escape_string($id), $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$result_cid = $query_cid->fetch_assoc();

			$stmt = $this->db->prepare("DELETE FROM `chat` WHERE `id` = '{$this->db->real_escape_string($id)}' AND `from` = '{$this->db->real_escape_string($this->id)}'");

			$x = 2;
		}

		// Execute the statement
		$stmt->execute();

		// Save the affected rows
		$affected = $stmt->affected_rows;

		// Close the statement
		$stmt->close();

		// If the messages/comments table was affected
		if($affected) {
			// Deletes the Comments/Likes/Reports if the Message was deleted
			if($x == 1) {
				$sids = $this->getMessagesIds(null, null, null, $id);

				// If there are any messages shared
				if($sids) {
					$this->deleteShared($sids);
				}

				// Delete all images from comments
				$query = $this->db->query(sprintf("SELECT `type`, `value` FROM `comments` WHERE `mid` = '%s' AND `type` = 'picture'", $this->db->real_escape_string($id)));

				$output = '';
				while($row = $query->fetch_assoc()) {
					$output .= $row['value'].',';
				}

				deletePhotos('picture', $output);

				$this->db->query(sprintf("DELETE FROM `comments` WHERE `mid` = '%s'", $this->db->real_escape_string($id)));
				$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` = '%s' AND `type` = 0", $this->db->real_escape_string($id)));
				$this->db->query(sprintf("DELETE FROM `reports` WHERE `post` = '%s' AND `parent` = '0'", $this->db->real_escape_string($id)));
				$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` = '%s'", $this->db->real_escape_string($id)));

				// If the message was a shared one, delete it from notifications as well
				if($message['type'] == 'shared') {
					$this->db->query("DELETE FROM `notifications` WHERE `child` = '{$this->db->real_escape_string($id)}' AND `parent` = '{$message['value']}' AND `type` = 3");

					// Update the main post shares counter
					$this->db->query(sprintf("UPDATE `messages` SET `shares` = `shares` - 1, `time` = `time` WHERE `id` = '%s'", $this->db->real_escape_string($message['value'])));
				} else {
					$this->db->query("DELETE FROM `messages` WHERE `type` = 'shared' AND `value` = '{$this->db->real_escape_string($id)}'");
				}
			} elseif($x == 0) {
				$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` = '%s' AND `type` = 1", $this->db->real_escape_string($id)));
				$this->db->query("DELETE FROM `reports` WHERE `post` = '{$this->db->real_escape_string($id)}' AND `parent` <> '0'");
				$this->db->query("DELETE FROM `notifications` WHERE `child` = '{$this->db->real_escape_string($id)}' AND `type` = '2'");
				$this->db->query("DELETE FROM `notifications` WHERE `child` = '{$this->db->real_escape_string($id)}' AND `type` = '1'");
				$this->db->query("DELETE FROM `notifications` WHERE `child` = '{$this->db->real_escape_string($id)}' AND `type` = '11'");
				$this->db->query(sprintf("UPDATE `messages` SET `comments` = `comments` - 1, `time` = `time` WHERE `id` = '%s'", $this->db->real_escape_string($message['mid'])));
			} elseif($x == 2) {
				// If there's another chat message available to be made as last id notification
				if($result_cid['id']) {
					$this->db->query(sprintf("UPDATE `conversations` SET `cid` = '%s' WHERE `from` = '%s' AND `to` = '%s'", $result_cid['id'], $this->db->real_escape_string($this->id), $this->db->real_escape_string($result_cid['to'])));
				} else {
					$this->db->query(sprintf("DELETE FROM `conversations` WHERE `cid` = '%s'", $this->db->real_escape_string($id)));
				}
			}

			// Execute the deletePhotos function
			deletePhotos($message['type'], $message['value']);

			if($x == 2 || $x == 1) {
				foreach($this->plugins as $plugin) {
					if(array_intersect(array("d"), str_split($plugin['type']))) {
						plugin($plugin['name'], array('id' => $message['id'], 'type' => $message['type'], 'value' => $message['value']), 4);
					}
				}
			}
		}

		return ($affected) ? 1 : 0;
	}

	function deleteShared($id) {
		$this->db->query(sprintf("DELETE FROM `comments` WHERE `mid` IN (%s)", $id));
		$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` IN (%s) AND `type` = 0", $id));
		$this->db->query(sprintf("DELETE FROM `reports` WHERE `post` IN (%s) AND `parent` = '0'", $id));
		$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` IN (%s)", $id));
	}

	function report($id, $type) {
		global $LNG;
		// Check if the Message exists
		if($type == 1) {
			$result = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `id` = '%s'", $this->db->real_escape_string($id)));
		} else {
			$result = $this->db->query(sprintf("SELECT `id`,`mid` FROM `comments` WHERE `id` = '%s'", $this->db->real_escape_string($id)));
			$parent = $result->fetch_array(MYSQLI_ASSOC);
		}
		// If the Message/Comment exists
		if($result->num_rows) {
			$result->close();

			// Get the report status, 0 = already exists * 1 = is safe
			$query = sprintf("SELECT `state` FROM `reports` WHERE `post` = '%s' AND `type` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($type));
			$result = $this->db->query($query);
			$state = $result->fetch_assoc();

			//  If the report already exists
			if($result->num_rows) {
				// If the comment state is 0, then already exists
				if($state['state'] == 0) {
					return $LNG["{$type}_already_reported"];
				} elseif($state['state'] == 1) {
					return $LNG["{$type}_is_safe"];
				} else {
					return $LNG["{$type}_is_deleted"];
				}
			} else {
				$stmt = $this->db->prepare(sprintf("INSERT INTO `reports` (`post`, `parent`, `by`, `type`) VALUES ('%s', '%s', '%s', '%s')", $this->db->real_escape_string($id), (isset($parent['mid'])) ? $parent['mid'] : 0, $this->db->real_escape_string($this->id), $this->db->real_escape_string($type)));

				// Execute the statement
				$stmt->execute();

				// Save the affected rows
				$affected = $stmt->affected_rows;

				// Close the statement
				$stmt->close();

				// If the comment was added, return 1
				return ($affected) ? $LNG["{$type}_report_added"] : $LNG["{$type}_report_error"];
			}
		} else {
			return $LNG["{$type}_not_exists"];
		}
	}

	function commentError($message) {
	    global $LNG;
		$rand = rand();
		return '<div class="message-reply-container" id="post_comment_'.$rand.'"><div class="message-reported">'.$message.' <a onclick="deleteNotification(1, '.$rand.')" title="'.$LNG['close'].'"><div class="delete_btn"></div></a></div></div>';
	}

	function addComment($id, $comment, $type = null, $value = null) {
		global $LNG;
		if(strlen($comment) > $this->message_length) {
			return array(0, $this->commentError(sprintf($LNG['comment_too_long'], $this->message_length)));
		}

		$query = sprintf("SELECT * FROM `messages`,`users` WHERE `id` = '%s' AND `messages`.`uid` = `users`.`idu`", $this->db->real_escape_string($id));
		$result = $this->db->query($query);

		$row = $result->fetch_assoc();

		// If the message is from a page, get the page data
		if($row['page']) {
			$this->page_data = $this->pageData(null, $row['page']);
		}

		// If the message is shared to friends only
		if($row['public'] == 2) {
			// If the user is also the owner
			if($this->id == $row['uid']) {
				$row['public'] = 1;
			} else {
				// Check if there is any friendship relation
				$friendship = $this->verifyFriendship($this->id, $row['uid']);

				// Set the message to appear as public
				if($friendship['status'] == 1) {
					$row['public'] = 1;
				}
			}
		}

		// If the POST is public
		if($row['public'] == 1 && !$this->getBlocked($row['uid'], 2)) {
			if($type == 'picture' && (!empty($_FILES['value']['size']) || !empty($message))) {
				// Define the array which holds the value names
				$allowedExt = explode(',', $this->image_format);
				$ext = pathinfo($_FILES['value']['name'], PATHINFO_EXTENSION);
				if(!empty($_FILES['value']['size']) && $_FILES['value']['size'] > $this->max_size) {
					return array(0, $this->commentError(sprintf($LNG['file_too_big'], $_FILES['value']['name'], fsize($this->max_size))));
				} elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
					return array(0, $this->commentError(sprintf($LNG['format_not_exist'], $_FILES['value']['name'], $this->image_format)));
				} else {
					if(isset($_FILES['value']['name']) && $_FILES['value']['name'] !== '' && $_FILES['value']['size'] > 0) {
						$tmp_name = $_FILES['value']['tmp_name'];
						$name = pathinfo($_FILES['value']['name'], PATHINFO_FILENAME);
						$fullname = $_FILES['value']['name'];
						$ext = pathinfo($_FILES['value']['name'], PATHINFO_EXTENSION);
						$finalName = uniqid(null, true).'.'.$this->db->real_escape_string($ext);

						// Define the type for picture
						$type = 'picture';

						// Store the values into arrays
						$value = $finalName;

						// Fix the image orientation if possible
						imageOrientation($tmp_name);

						move_uploaded_file($tmp_name, __DIR__ . '/../uploads/media/'.$finalName);
					}
				}
			} else {
				$type = '';
				$value = '';

				// If the comment is empty
				if(empty($comment)) {
					return array(0);
				}
			}

			// Add the insert message
			$stmt = $this->db->prepare("INSERT INTO `comments` (`uid`, `mid`, `message`, `type`, `value`) VALUES (?, ?, ?, ?, ?)");

            $comment = htmlspecialchars($comment);

			$stmt->bind_param('iisss', $this->id, $id, $comment, $type, $value);

			// Execute the statement
			$stmt->execute();

			// Save the affected rows
			$affected = $stmt->affected_rows;

			// Close the statement
			$stmt->close();

            // Select the last inserted message
            $getId = $this->db->query(sprintf("SELECT `id`,`uid`,`mid`,`message` FROM `comments` WHERE `uid` = '%s' AND `mid` = '%s' ORDER BY `id` DESC", $this->db->real_escape_string($this->id), $row['id']));
            $lastComment = $getId->fetch_assoc();

			// If the comment is not being posted on a page
			if(empty($row['page'])) {
                // Do the INSERT notification
                $insertNotification = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '%s', '1', '0')", $this->db->real_escape_string($this->id), $row['uid'], $row['id'], $lastComment['id']));

                if($affected) {
                    // If email on comments is enabled in admin settings
                    if($this->email_comment) {
                        // If user has emails on like enabled and it\'s not liking his own post
                        if($row['email_comment'] && ($this->id !== $row['idu'])) {
                            // Send e-mail
                            sendMail($row['email'], sprintf($LNG['ttl_comment_email'], $this->username), sprintf($LNG['comment_email'], realName($row['username'], $row['first_name'], $row['last_name']), permalink($this->url . '/index.php?a=profile&u=' . $this->username), $this->username, permalink($this->url . '/index.php?a=post&m=' . $id), $this->title, permalink($this->url . '/index.php?a=settings&b=notifications')), $this->email);
                        }
                    }
                }
            }

            if($affected) {
                // Update the comments counter
                $this->db->query(sprintf("UPDATE `messages` SET `comments` = `comments` + 1, `time` = `time` WHERE `id` = '%s'", $this->db->real_escape_string($row['id'])));

                preg_match_all('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', $lastComment['message'], $matchedMentions);

                $i = 0;
                $prevent = array();
                foreach($matchedMentions[2] as $mention) {
                    if($i == 30) break;

                    if(!in_array($mention, $prevent)) {
                        // Validate the user
                        $getUser = $this->db->query(sprintf("SELECT `idu`, `username`, `first_name`, `last_name`, `email`, `email_mention` FROM `users` WHERE `username` = '%s'", $this->db->real_escape_string($mention)));
                        $mUser = $getUser->fetch_assoc();

                        $getBlocked = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE `by` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($mUser['idu']), $this->db->real_escape_string($this->id)));

                        // If the user exists and is not the message owner
                        if($getUser->num_rows > 0 && $getBlocked->num_rows == 0 && $mUser['username'] != $this->username) {
                            // If the user has email on mention enabled and the email is enabled in the Admin Panel
                            if($mUser['email_mention'] == 1 && $this->email_mention == 1) {
                                sendMail($mUser['email'], sprintf($LNG['ttl_mention_c_email'], $mUser['username']), sprintf($LNG['mention_c_email'], realName($mUser['username'], $mUser['first_name'], $mUser['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, permalink($this->url.'/index.php?a=post&m='.$lastComment['mid'].'#comment'.$lastComment['id']), $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->site_email);
                            }

                            $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '%s', 11, 0)", $this->id, $mUser['idu'], $lastComment['mid'], $lastComment['id']));
                        }
                    }
                    $prevent[] = $mention;
                    $i++;
                }
            }

			// If the comment was added, return 1
			return ($affected) ? array(1) : array(0, $this->commentError($LNG['comment_error']));
		} else {
			return array(0, $this->commentError($LNG['comment_error']));
		}
	}

	function changePrivacy($id, $value) {
		global $LNG;
		$stmt = $this->db->prepare("UPDATE `messages` SET `public` = '{$this->db->real_escape_string($value)}', `time` = `time`  WHERE `id` = '{$this->db->real_escape_string($id)}' AND `uid` = '{$this->db->real_escape_string($this->id)}' AND `group` = 0");

		// Execute the statement
		$stmt->execute();

		// Save the affected rows
		$affected = $stmt->affected_rows;

		// Close the statement
		$stmt->close();

		if($value == 1) {
			$public = '<div class="privacy-icons public-icon" title="'.$LNG['public'].'"></div>';
		} elseif($value == 2) {
			$public = '<div class="privacy-icons friends-icon" title="'.$LNG['friends'].'"></div>';
		} else {
			$public = '<div class="privacy-icons private-icon" title="'.$LNG['private'].'"></div>';
		}
		return $public;
	}

	function ago($i) {
		global $LNG;
		$duration = time() - $i;
		$o = $LNG['just_now'];
		$t = array(
			'year' => 31556926,
			'month' => 2629744,
			'week' => 604800,
			'day' => 86400,
			'hour' => 3600,
			'minute' => 60,
			'second' => 1
		);

		foreach($t as $string => $value) {
			if($value <= $duration) {
				$v = floor($duration/$value);
				if($v > 1) {
					$o = sprintf($LNG['ta_'.$string.'s'], $v).' '.$LNG['ago'];
				} else {
					$o = $LNG['ta_'.$string].' '.$LNG['ago'];
				}
				break;
			}
		}

		return $o;
	}

	function sidebarGender($bold) {
		global $LNG, $CONF;

		// Start the output
		$row = array('male', 'female');
		$link = '<div class="sidebar-container widget-gender"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['filter_gender'].'</div>';
		$class = '';
		if(!in_array($bold, array('m', 'f'))) {
			$class = ' sidebar-link-active';
		}
		$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&q='.htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8').((!empty($_GET['age'])) ? '&age='.htmlspecialchars($_GET['age'], ENT_QUOTES, 'UTF-8') : '')).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/filters/all.svg">'.$LNG["all_genders"].'</a></div>';

		foreach($row as $type) {
			$class = '';
			if(substr($type, 0, 1) == $bold) {
				$class = ' sidebar-link-active';
			}

			// Output the links
			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&q='.htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8').((!empty($_GET['age'])) ? '&age='.htmlspecialchars($_GET['age'], ENT_QUOTES, 'UTF-8') : '').'&filter='.substr($type, 0, 1)).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/filters/'.$type.'.svg">'.$LNG["sidebar_{$type}"].'</a></div>';
		}
		$link .= '</div></div>';
		return $link;
	}

	function sidebarSearch($settings) {
		global $LNG, $CONF;

		// Start the output
		$row = array_filter(array('tag', ($settings['groups'] ? 'groups' : ''), ($settings['pages'] ? 'pages' : '')));

		$link = '<div class="sidebar-container widget-search"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['title_search'].'</div>';
		$class = '';
		if(isset($_GET['q']) && !empty($_GET['q'])) {
			$class = ' sidebar-link-active';
		}
		$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&q='.htmlspecialchars((isset($_GET['q']) ? $_GET['q'] : null).(isset($_GET['tag']) ? $_GET['tag'] : null).(isset($_GET['groups']) ? $_GET['groups'] : null).(isset($_GET['pages']) ? $_GET['pages'] : null), ENT_QUOTES, 'UTF-8')).'" rel="loadpage">'.$LNG["sidebar_people"].'</a></div>';

		foreach($row as $type) {
			$class = '';
			$url = '&'.$type.'='.htmlspecialchars((isset($_GET['q']) ? $_GET['q'] : null).(isset($_GET['tag']) ? $_GET['tag'] : null).(isset($_GET['groups']) ? $_GET['groups'] : null).(isset($_GET['pages']) ? $_GET['pages'] : null), ENT_QUOTES, 'UTF-8');
			if(!empty($_GET[$type])) {
				$class = ' sidebar-link-active';
			}

			// Output the links
			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].$url).'" rel="loadpage">'.$LNG["sidebar_{$type}"].'</a></div>';
		}
		$link .= '</div></div>';
		return $link;
	}

	function sidebarAge($bold) {
		global $LNG, $CONF;

		// Start the output
		$ages = array('22-18', '29-22', '39-29', '49-39', '59-49', '69-59', '99-69');
		$link = '<div class="sidebar-container widget-age"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['filter_age'].'</div>';
		$class = '';
		if(!in_array($bold, $ages)) {
			$class = ' sidebar-link-active';
		}
		$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&q='.htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8').((!empty($_GET['filter'])) ? '&filter='.htmlspecialchars($_GET['filter'], ENT_QUOTES, 'UTF-8') : '')).'" rel="loadpage">'.$LNG["all_ages"].'</a></div>';
		foreach($ages as $age) {
			// Split the ages
			$between = explode('-', $age);

			$class = '';
			if($age == $bold) {
				$class = ' sidebar-link-active';
			}

			// Output the links
			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&q='.htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8').'&age='.$age.((!empty($_GET['filter'])) ? '&filter='.htmlspecialchars($_GET['filter'], ENT_QUOTES, 'UTF-8') : '')).'" rel="loadpage">'.$between[1].' - '.$between[0].'</a></div>';
		}
		$link .= '</div></div>';
		return $link;
	}

	function sidebarNotifications($bold, $settings = null) {
		global $LNG, $CONF;

		// Start the output
		$row = array_filter(array('likes', 'comments', 'shared', 'friendships', 'mentions', ($settings['pages'] ? 'pages' : ''), ($settings['groups'] ? 'groups' : ''), 'chats', 'pokes', 'birthdays'));
		$link = '<div class="sidebar-container widget-notifications"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['title_notifications'].'</div>';
		$class = '';
		if(!in_array($bold, $row)) {
			$class = ' sidebar-link-active';
		}
		$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a']).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/all.svg">'.$LNG["recent"].'</a></div>';

		foreach($row as $type) {
			$class = '';
			if($type == $bold) {
				$class = ' sidebar-link-active';
			}

			// Output the links
			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].'&filter='.$type).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/'.$type.'.svg">'.$LNG["sidebar_{$type}"].'</a></div>';
		}
		$link .= '</div></div>';
		return $link;
	}

	function sidebarTypes($bold) {
		global $LNG, $CONF;
		$row = $this->listTypes;

		// Sort the array elements
		sort($row);

		$profile = ($this->profile) ? '&u='.$this->profile : '';
		// If the result is not empty
		if($row) {
			// Start the output
			$link = '<div class="sidebar-container widget-types"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['events'].'</div>';
            $class = '';
			if(empty($bold) && isset($_GET['r']) == false) {
				$class = ' sidebar-link-active';
			}
			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].$profile).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/all.svg">'.$LNG["all_events"].'</a></div>';

			$i = 1;
			$hidden = '';
			foreach($row as $type) {
                $class = '';
				if($type == $bold) {
					$class = ' sidebar-link-active';
				}

				// Output the links
				$link .= '<div class="sidebar-link sidebar-events'.$class.'"'.$hidden.'><a href="'.permalink($this->url.'/index.php?a='.$_GET['a'].$profile.'&filter='.$type).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/events/'.$type.'.svg">'.$LNG["sidebar_{$type}"].'</a></div>';

				// Display the show more arrow
				if(($i == 5 && count($row) > 5 && empty($bold)) || ($i == 5 && count($row) > 5 && !empty($bold) && !ctype_alpha($bold))) {
					// Output the links
					$link .= '<div class="sidebar-link sidebar-more" id="show-more-btn-1"><a href="javascript:;" onclick="sidebarShow(1)"><div class="message-menu sidebar-arrow"></div></a></div>';
					// Hide the rest of the elements
					$hidden = ' style="display: none;"';
				}
				$i++;
			}
			$link .= '</div></div>';
			return $link;
		}
	}

	function sidebarDates($bold, $location) {
		// Location: Define the page where the widget appears
		global $LNG;

		if($location == 1) {
			$row = $this->listDates('hashtag');
			$url = '&tag='.htmlspecialchars($_GET['tag'], ENT_QUOTES, 'UTF-8');
		} else {
			$row = $this->listDates;
			$url = ($this->profile) ? '&u='.$this->profile : '';
		}

		// If the result is not empty
		if($row) {
			// Start the output
			$link = '<div class="sidebar-container widget-archive"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['archive'].'</div>';
			$class = '';
			if(empty($bold) && isset($_GET['r']) == false) {
				$class = ' sidebar-link-active';
			}

			$link .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a='.htmlspecialchars($_GET['a'], ENT_QUOTES, 'UTF-8').$url).'" rel="loadpage">'.$LNG["all_time"].'</a></div>';
			$i = 1;
			$hidden = '';
			foreach($row as $date) {
				$class = '';
				if($date == $bold) {
					$class = ' sidebar-link-active';
				}

				// Output the links
				$link .= '<div class="sidebar-link sidebar-dates'.$class.'"'.$hidden.'><a href="'.permalink($this->url.'/index.php?a='.htmlspecialchars($_GET['a'], ENT_QUOTES, 'UTF-8').$url.'&filter='.$date).'" rel="loadpage">'.$date.'</a></div>';

				// Display the show more arrow
				if(($i == 5 && count($row) > 5 && empty($bold)) || ($i == 5 && count($row) > 5 && !empty($bold) && !is_numeric($bold))) {
					// Output the links
					$link .= '<div class="sidebar-link sidebar-more" id="show-more-btn-2"><a href="javascript:;" onclick="sidebarShow(2)"><div class="message-menu sidebar-arrow"></div></a></div>';
					// Hide the rest of the elements
					$hidden = ' style="display: none;"';
				}
				$i++;
			}
			$link .= '</div></div>';
			return $link;
		}
	}

	function listTypes($friends = null) {
		// Removed any verification queries for performance purposes
		if($friends == false) {
			return false;
		} elseif($friends == 'profile') {
			$list = array('map', 'music', 'picture', 'shared', 'video');
		} elseif($friends) {
			$list = array('map', 'music', 'picture', 'shared', 'video');
		}
		return $list;
	}

	function listDates($friends = null) {
		if($friends == false) {
			return false;
		} elseif($friends == 'profile') {
			$start_date = ($this->profile_data['date'] ? $this->profile_data['date'] : $this->registration_date);
		} elseif($friends) {
			if($friends == 'hashtag') {
				$query = $this->db->query(sprintf("SELECT extract(YEAR from `messages`.`time`) AS `year` FROM `messages` WHERE `messages`.`tag` LIKE '%s' ORDER BY `messages`.`id` ASC LIMIT 1", '%'.$this->db->real_escape_string($_GET['tag']).'%'));
			} else {
				$query = $this->db->query(sprintf("SELECT extract(YEAR from `users`.`date`) AS `year` FROM `users` WHERE (`users`.`idu` IN (%s) AND `users`.`suspended` = 0) ORDER BY `users`.`date` ASC LIMIT 1", $friends));
			}

			$result = $query->fetch_assoc();

			$start_date = $result['year'].'-01-01';
		}

		$date = date("Y", strtotime($start_date));
		while($date <= date("Y", strtotime(date('Y-m-d')))) {
			$list[] = $date;
			$date++;
		}

		return array_reverse($list);
	}

	function sidebarFriends($type, $for) {
		global $LNG;
		$rows = $this->friendsArray;

		// If the select was made
		if(!empty($rows)) {
			if($for == 0) {
				$i = 0;
				$output = '<div class="sidebar-container widget-friends"><div class="sidebar-content"><div class="sidebar-header"><a href="'.permalink($this->url.'/index.php?a=profile&u='.((!empty($this->profile)) ? $this->profile : $this->username).'&r=friends').'" rel="loadpage">'.$LNG['friends'].' <span class="sidebar-header-light">('.$this->friendsCount.')</span></a></div><div class="sidebar-padding">';
				foreach($rows as $row) {
					$username = realName($row['username'], $row['first_name'], $row['last_name']);
					// Add the elemnts to the array
					$output .= '<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage"><div class="sidebar-subscriptions"><div class="sidebar-title-container"><div class="sidebar-title-name">'.$username.'</div></div><img src="'.permalink($this->url.'/image.php?t=a&w=112&h=112&src='.$row['image']).'"></div></a>';
					$i++;
				}
				$output .= '</div></div></div>';
			} elseif($for == 1) {
				$output = '<a href="'.permalink($this->url.'/index.php?a=profile&u='.((!empty($this->profile)) ? $this->profile : $this->username).'&r=friends').'" rel="loadpage">'.$this->friendsCount.' '.$LNG['people'].'</a>';
			}
			return $output;
		} else {
			return false;
		}
	}

	function onlineUsers($type = null, $value = null, $window = null) {
		global $LNG, $CONF;
		// Type 2: Show the Friends Results for the live search for Chat/Messages
		//		 : If value is set, find friends
		// Type 1: Display the friends for the Chat/Messages page
		//		 : If value is set, find exact username
		// Type 0: Display the friends for Chat Window

		// Get friends list
		if(!$type) {
			$friendslist = $this->getFriendsList();
		} else {
			$friendslist = $this->getFriendsList();
		}
		$currentTime = time();

		if(!empty($friendslist)) {
			if($type == 1) {
				// Display current friends
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `idu` IN (%s) ORDER BY `online` DESC", $this->db->real_escape_string($friendslist)));
			} elseif($type == 2) {
				if($value) {
					// Search in friends
					$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE (`username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s') AND `idu` IN (%s) ORDER BY `online` DESC", '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($friendslist)));
				} else {
					// Display current friends
					// If it's for the chat window, when the search result is empty, display only the online users
					if($window) {
						$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `idu` IN (%s) AND `online` > '%s'-'%s' ORDER BY `username` ASC", $this->db->real_escape_string($friendslist), $currentTime, $this->online_time));
					} else {
						$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `idu` IN (%s) ORDER BY `online` DESC", $this->db->real_escape_string($friendslist)));
					}
				}
			} else {
				// Display the online friends (for the chat window)
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `idu` IN (%s) AND `online` > '%s'-'%s' ORDER BY `username` ASC", $this->db->real_escape_string($friendslist), $currentTime, $this->online_time));
			}

			// Store the array results
			while($row = $query->fetch_assoc()) {
				$rows[] = $row;
			}
		}

		// usort($rows, 'sortOnlineUsers');

		if($type == 1) {
			// Output the users
			$output = '<div class="sidebar-container widget-online-users"><div class="sidebar-content"><div class="sidebar-header"><input type="text" placeholder="'.$LNG['search_in_friends'].'"  id="search-list"></div><div class="sidebar-chat-list">';
			if(!empty($rows)) {
				$i = 0;
				foreach($rows as $row) {
					// Switch the images, depending on the online state
					if(($currentTime - $row['online']) > $this->online_time) {
						$icon = 'offline';
					} else {
						$icon = 'online';
					}
					$output .= ($row['username'] == $_GET['u']) ? '<strong>' : '';
					$output .= '<div class="sidebar-users"><a href="'.permalink($this->url.'/index.php?a=messages&u='.$row['username'].'&id='.$row['idu']).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png" class="sidebar-status-icon"> <img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'" width="25" height="25"> '.realName($row['username'], $row['first_name'], $row['last_name']).'</a></div>';
					$output .= ($row['username'] == $_GET['u']) ? '</strong>' : '';
					$i++;
				}
			} else {
				$output .= '<div class="sidebar-inner">'.$LNG['lonely_here'].'</div>';
			}
			$output .= '</div></div></div>';
		} elseif($type == 2) {
			$output = '';
			if(!empty($rows)) {
				$i = 0;
				foreach($rows as $row) {
					// Switch the images, depending on the online state
					if(($currentTime - $row['online']) > $this->online_time) {
						$icon = 'offline';
					} else {
						$icon = 'online';
					}
					$url = ($window) ? '<a onclick="openChatWindow(\''.$row['idu'].'\', \''.$row['username'].'\', \''.addslashes(realName($row['username'], $row['first_name'], $row['last_name'])).'\', \''.$this->url.'\', \''.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png\')"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png" class="sidebar-status-icon"> <img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'" width="25" height="25"> '.realName($row['username'], $row['first_name'], $row['last_name']).'</a>' : '<a href="'.permalink($this->url.'/index.php?a=messages&u='.$row['username'].'&id='.$row['idu']).'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png" class="sidebar-status-icon"> <img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'" width="25" height="25"> '.realName($row['username'], $row['first_name'], $row['last_name']).'</a>';
					$output .= '<div class="sidebar-users">'.$url.'</div>';

					$i++;
				}
			} else {
				$output .= '<div class="sidebar-inner">'.$LNG['no_results'].'</div>';
			}
		} else {
            $output = '';
			// If the query has content
			if(!empty($rows)) {
				// Output the online users
				$i = 0;
				foreach($rows as $row) {
					// Switch the images, depending on the online state
					if(($currentTime - $row['online']) > $this->online_time) {
						$icon = 'offline';
					} else {
						$icon = 'online';
					}
					$output .= '<div class="sidebar-users"><a onclick="openChatWindow(\''.$row['idu'].'\', \''.$row['username'].'\', \''.addslashes(realName($row['username'], $row['first_name'], $row['last_name'])).'\', \''.$this->url.'\', \''.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png\')"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png" class="sidebar-status-icon"> <img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'" width="25" height="25"> '.realName($row['username'], $row['first_name'], $row['last_name']).'</a></div>';

					$i++;
				}
			}
		}
		if($type) {
			return $output;
		} else {
			return array('friends_chat' => array('friends_count' => ((isset($query) && $query->num_rows > 0) ? $query->num_rows : 0), 'friends_list' => $output));
		}
	}

	function getChat($uid, $user) {
		// If the user is not a confirmed one
		if($user['suspended'] == 2) {
			return $this->getChat(null, null);
		}
		$uid = saniscape($uid);
		global $LNG, $CONF, $settings;

		$po = '';
		foreach($this->plugins as $plugin) {
			if(array_intersect(array("e"), str_split($plugin['type']))) {
				$data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; $data['plugin_chat'] = 1; $data['user'] = $user; unset($data['password']); unset($data['salted']);
				$po .= plugin($plugin['name'], $data, 3);
			}
		}

		$output =	'<div class="message-container">
						<div class="message-content">
							<div class="message-form-header">
								<div class="message-form-user"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/chat.png"></div>
								<span class="chat-username">'.((empty($user['username'])) ? $LNG['conversation'] : realName($user['username'], $user['first_name'], $user['last_name'])).'</span><span class="blocked-link">'.$this->getBlocked($uid).'</span>
								<div class="preloader message-loader" style="display: none" id="m-p-'.$uid.'"></div>
							</div>
							<div class="chat-container scrollable" id="chat-container-'.$uid.'">
								'.((empty($user['username'])) ? $this->chatError($LNG['start_conversation']) : $this->getChatMessages($uid)).'
							</div>
							<div class="message-divider"></div>

							<div class="chat-form-inner"><input id="chat" class="chat-user'.$uid.'" placeholder="'.$LNG['type_message'].'" name="chat" onkeydown="if(event.keyCode == 13) { postChat('.$uid.', 1) }">
							<div class="c-w-icon c-w-icon-smiles" id="chat-smiles-'.$uid.'" onclick="chatPluginContainer('.$uid.', 0, 1)" title="'.$LNG['chat_smiles'].'"></div><label for="chatimage" data-userid="'.$uid.'" class="c-w-icon c-w-icon-picture chat-image-btn" title="'.$LNG['chat_picture'].'"></label><div data-userid="'.$uid.'" class="c-w-icon c-w-icon-camera chat-camera-btn desktop" onclick="cameraModal()" title="'.$LNG['chat_camera'].'"></div>'.$po.'<div class="chat-send" onclick="postChat('.$uid.', 1)">'.$LNG['send'].'</div>
							</div>
						</div>
					</div>';
		return $output;
	}

	function checkChat($uid) {
		global $CONF;
		if(is_array($uid)) {
			$output = array();

			foreach($uid as $fid) {
				$userStatus = $this->db->query(sprintf("SELECT `online` FROM `users` WHERE `idu` = '%s'", $this->db->real_escape_string($fid)));
				$result = $userStatus->fetch_assoc();

				if((time() - $result['online']) > $this->online_time) {
					$icon = 'offline';
				} else {
					$icon = 'online';
				}
				$output[$fid] = array('message' => $this->getChatMessages($fid, null, null, 2), 'status' => '<img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/'.$icon.'.png" class="sidebar-status-icon">');
			}

			return array('friends_messages' => $output);
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `chat` WHERE `from` = '%s' AND `to` = '%s' AND `read` = '0'", $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id)));
			if($query->num_rows) {
				return $this->getChatMessages($uid, null, null, 2);
			}
		}
		return false;
	}

	function getChatMessages($uid = null, $cid = null, $start = null, $type = null, $for = null) {
		// uid = user id (from which user the message was sent)
		// cid = where the pagination will start
		// start = on/off
		// Type 0: Get all the messages from a conversation
		// Type 1: Get the last posted message from a conversation
		// Type 2: Get the latest unread messages from a conversation
		global $LNG;

		// If the $start value is 0, empty the query;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `chat`.`id` < \''.$this->db->real_escape_string($cid).'\'';
		}

		if($type == 1) {
			$query = sprintf("SELECT * FROM `chat`, `users` WHERE (`chat`.`from` = '%s' AND `chat`.`to` = '%s' AND `chat`.`from` = `users`.`idu`) ORDER BY `chat`.`id` DESC LIMIT 1", $this->db->real_escape_string($this->id), $this->db->real_escape_string($uid));
		} elseif($type == 2) {
			$query = sprintf("SELECT * FROM `chat`,`users` WHERE `from` = '%s' AND `to` = '%s' AND `read` = '0' AND `chat`.`from` = `users`.`idu` ORDER BY `chat`.`id` DESC", $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id));
		} else {
			// SELECT * FROM `chat`, `users` WHERE (`chat`.`from` = '%s' AND `chat`.`to` = '%s' AND `chat`.`from` = `users`.`idu`) %s OR (`chat`.`from` = '%s' AND `chat`.`to` = '%s' AND `chat`.`from` = `users`.`idu`) %s ORDER BY `chat`.`id` DESC LIMIT %s
			$query = sprintf("SELECT * FROM `chat` LEFT JOIN `users` ON `users`.`idu` = `chat`.`from` WHERE (`chat`.`from` = '%s' AND `chat`.`to` = '%s') %s OR (`chat`.`from` = '%s' AND `chat`.`to` = '%s') %s ORDER BY `chat`.`id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->db->real_escape_string($uid), $start, $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id), $start, ($this->m_per_page + 1));
		}

		// check if the query was executed
		if($result = $this->db->query($query)) {
			if($type !== 1) {
				// Set the read status to 1 whenever you load messages [IGNORE TYPE: 1]
				$update = $this->db->query(sprintf("UPDATE `chat` SET `read` = '1', `time` = `time` WHERE `from` = '%s' AND `to` = '%s' AND `read` = '0'", $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id)));
				$update =  $this->db->query(sprintf("UPDATE `conversations` SET `read` = '1' WHERE `from` = '%s' AND `to` = '%s' AND `read` = '0'", $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id)));
			}

			// Set the result into an array
			while($row = $result->fetch_assoc()) {
				$rows[] = $row;
			}

			if(!empty($rows)) {
				$rows = array_reverse($rows);

				if($type == 1) {
					// Add the unread notification
					$stmt = $this->db->prepare("UPDATE `conversations` SET `cid` = ?, `read` = '0' WHERE `from` = ? AND `to` = ?");
					$stmt->bind_param('sss', $rows[0]['id'], $this->id, $uid);
					$stmt->execute();
					$affected = $stmt->affected_rows;
					$stmt->close();
					// If a conversation notification does not exist, create one
					if($affected == 0) {
						$this->db->query(sprintf("INSERT INTO `conversations` (`from`, `to`, `read`, `cid`) VALUES ('%s', '%s', '0', '%s')", $this->db->real_escape_string($this->id), $this->db->real_escape_string($uid), $rows[0]['id']));
					}
				}

				// Define the $output variable;
				$output = $last_seen = $load = $loadmore = '';

				// If there are more results available than the limit, then show the Load More Chat Messages
				if(array_key_exists($this->m_per_page, $rows)) {
					$loadmore = 1;

					// Unset the first array element because it's not needed, it's used only to predict if the Load More Chat Messages should be displayed
					unset($rows[0]);
				}

				foreach($rows as $row) {
					$po = '';
					foreach($this->plugins as $plugin) {
						if(array_intersect(array("1"), str_split($plugin['type']))) {
							$po .= plugin($plugin['name'], array('message' => $row['message'], 'id' => $row['id'], 'type' => $row['type'], 'value' => $row['value'], 'user_id' => $this->id, 'plugin_chat' => 1), 1);
						}
					}

					// Define the time selected in the Admin Panel
					$time = $row['time']; $b = '';
					if($this->time == '0') {
						$time = date("c", strtotime($row['time']));
					} elseif($this->time == '2') {
						$time = $this->ago(strtotime($row['time']));
					} elseif($this->time == '3') {
						$date = strtotime($row['time']);
						$time = date('Y-m-d', $date);
						$b = '-standard';
					}

					if($this->username == $row['username']) { // If it's current username is the same with the current author
						$delete = '<a onclick="deleteModal('.$row['id'].', 2)" title="'.$LNG['delete'].'"><div class="delete_btn"></div></a>';
						$class = 'user-one';
					} else {
						$delete = '';
						$class = 'user-two';
					}

					// Variable which contains the result
					$output .= '
					<div class="message-reply-container '.$class.'" data-chat-id="'.$row['id'].'" >
						'.$delete.'
						<div class="message-reply-avatar">
							<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage" title="'.realName($row['username'], $row['first_name'], $row['last_name']).'" id="avatar-m-'.$row['id'].'"><img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'"></a>
						</div>
						<div class="message-reply-message">'.(!empty($row['type']) && !empty($row['value']) ? $this->getChatType($row['type'], $row['value'], $row['id']) : $this->parseMessage($row['message'])).$po.'
							<div class="message-time" id="time-m-'.$row['id'].'">
								<div class="timeago'.$b.'" title="'.$time.'">
									'.$time.'
								</div>
							</div>
						</div>
						<div class="delete_preloader" id="del_chat_'.$row['id'].'"></div>

					</div>';
					$start = $row['id'];
				}

				// Switch the images, depending on the online state
				$currentTime = time();

				$user = $this->profileData(null, $uid);

				if(($currentTime - $user['online']) > $this->online_time && empty($user['offline']) && $type == 0) {
					$time = date('Y-m-d H:i:s', $user['online']); $b = '';
					if($this->time == '0') {
						$time = date("c", $user['online']);
					} elseif($this->time == '2') {
						$time = $this->ago($user['online']);
						$b = '-standard';
					} elseif($this->time == '3') {
						$time = date('Y-m-d', $user['online']);
						$b = '-standard';
					}

					$last_seen = '<div class="last-online" data-last-online="'.$user['idu'].'">'.sprintf($LNG['last_online'], '<div class="timeago'.$b.'" title="'.$time.'">'.$time.'</div>').'</div>';
				}

				$output .= $last_seen;

				if($loadmore) {
					$load = '<div class="load-more-chat" id="'.(($for == 1) ? 'l-m-c-'.$uid : 'l-m-c').'"><a onclick="loadChat('.htmlentities($uid, ENT_QUOTES).', '.$rows[1]['id'].', 1, '.(($for) ? 1 : 0).')">'.$LNG['view_more_conversations'].'</a></div>';
				}

				// Close the query
				$result->close();

				// Return the conversations
				return $load.$output;
			}
		} else {
			return false;
		}
	}

	function postChat($message, $uid, $type = null, $value = null) {
		global $LNG;

		$user = $this->profileData(null, $uid);
		// If the user is not a confirmed one
		if($user['suspended'] == 2) {
			return false;
		}

		if($type == 'picture' && (!empty($_FILES['image']['size']) || !empty($message))) {
			if($message) {
				$value = mt_rand().'_'.mt_rand().'_'.mt_rand().'.png';

				// If the message is a valid base64 encoded image
				$image = base64Image($message, $value);
				if($image['data'] && $image['size'] < $this->max_size) {
					file_put_contents(__DIR__ . '/../uploads/media/'.$value, $image['data']);
					$message = '';
				} else {
					return false;
				}
			} else {
				// Define the array which holds the value names
				$allowedExt = explode(',', $this->image_format);
				$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
				if(!empty($_FILES['image']['size']) && $_FILES['image']['size'] > $this->max_size) {
					return $this->chatError(sprintf($LNG['file_too_big'], $_FILES['image']['name'], fsize($this->max_size)));
				} elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
					return $this->chatError(sprintf($LNG['format_not_exist'], $_FILES['image']['name'], $this->image_format));
				} else {
					if(isset($_FILES['image']['name']) && $_FILES['image']['name'] !== '' && $_FILES['image']['size'] > 0) {
						$tmp_name = $_FILES['image']['tmp_name'];
						$name = pathinfo($_FILES['image']['name'], PATHINFO_FILENAME);
						$fullname = $_FILES['image']['name'];
						$ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
						$finalName = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$this->db->real_escape_string($ext);

						// Define the type for picture
						$type = 'picture';

						// Store the values into arrays
						$value = $finalName;

						// Fix the image orientation if possible
						imageOrientation($tmp_name);

						move_uploaded_file($tmp_name, __DIR__ . '/../uploads/media/'.$finalName);
					}
				}
			}
		} else {
			// Reset the values if the event is not a picture or a plugin
			if($type == 'plugin') {
				$type = $po = '';
				foreach($this->plugins as $plugin) {
					if(array_intersect(array("1"), str_split($plugin['type']))) {
						$poerr = plugin($plugin['name'], array('message' => $message, 'type' => $type, 'value' => $value, 'plugin_chat' => 1), 0);
						// If the plugin output is not an array (error)
						if(!is_array($poerr)) {
							// Store the result
							$po .= $poerr;
						} else {
							// Return the plugin error message
							return $this->chatError($poerr[0]);
						}
					}
				}

				// If there's any plugin output
				if($po) {
					$value = $po;
				}
			} else {
				$type = '';
				$value = '';

				if(strlen($message) > $this->chat_length) {
					return $this->chatError(sprintf($LNG['chat_too_long'], $this->chat_length));
				} elseif($uid == $this->id) {
					return $this->chatError(sprintf($LNG['chat_self']));
				} elseif(!$user['username']) {
					return $this->chatError(sprintf($LNG['chat_no_user']));
				}
			}
		}

		$query = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE `by` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($this->id), $this->db->real_escape_string($uid)));

		if($query->num_rows) {
			return $this->chatError(sprintf($LNG['blocked_user'], realName($user['username'], $user['first_name'], $user['last_name'])));
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE `by` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($uid), $this->db->real_escape_string($this->id)));

			if($query->num_rows) {
				return $this->chatError(sprintf($LNG['blocked_by'], realName($user['username'], $user['first_name'], $user['last_name'])));
			}
		}

		// Prepare the insertion
		$stmt = $this->db->prepare(sprintf("INSERT INTO `chat` (`from`, `to`, `message`, `type`, `value`, `read`, `time`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP)", $this->db->real_escape_string($this->id), $this->db->real_escape_string($uid), $this->db->real_escape_string(htmlspecialchars($message)), $this->db->real_escape_string($type), $this->db->real_escape_string(strip_tags($value)), 0));

		// Execute the statement
		$stmt->execute();

		// Save the affected rows
		$affected = $stmt->affected_rows;

		// Close the statement
		$stmt->close();
		if($affected) {
			return $this->getChatMessages($uid, null, null, 1);
		}
	}

	function updateStatus($offline = null) {
		if(!$offline) {
			$this->db->query(sprintf("UPDATE `users` SET `online` = '%s' WHERE `idu` = '%s'", time(), $this->db->real_escape_string($this->id)));
		}
	}

	function chatError($value) {
		return '<div class="chat-error">'.$value.'</div>';
	}

	function sidebarPages($visible = null) {
		global $CONF, $LNG;

		// Select the pages
		$query = $this->db->query(sprintf("SELECT * FROM `pages` WHERE `pages`.`by` = '%s' ORDER BY `id` DESC LIMIT %s", $this->db->real_escape_string($this->id), $this->pages_limit));
		$row = array();

		while($rows = $query->fetch_assoc()) {
			$row[] = $rows;
		}

		$output = '<div class="sidebar-container widget-pages"><div class="sidebar-content"><div class="sidebar-header"><a href="'.permalink($this->url.'/index.php?a=page').'" rel="loadpage">'.$LNG['pages'].'</a></span></div>';

		if(!$visible) {
			$output .= '<div class="sidebar-link"><a href="'.permalink($this->url.'/index.php?a=page').'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/plus.svg" width="24" height="24">'.$LNG['create_page'].'</a></div>';
		}

		if($row) {
			$i = 1;

			$hidden = '';
			foreach($row as $page) {
				$menulist = '
				<a href="'.permalink($this->url.'/index.php?a=page&name='.$page['name'].'&r=edit').'" rel="loadpage"><div class="message-menu-row">'.$LNG['edit'].'</div></a>
				<a href="'.permalink($this->url.'/index.php?a=page&name='.$page['name'].'&r=delete').'" rel="loadpage"><div class="message-menu-row">'.$LNG['delete'].'</div></a>';

				$menu = '
				<div id="page-menu'.$page['id'].'" class="message-menu-container sidebar-menu-container">
					'.$menulist.'
				</div>';

				$notifications = $this->pageActivity(0, $page);

				$output .= '<div class="sidebar-link sidebar-page" id="page-'.$page['id'].'"'.$hidden.'><a href="'.permalink($this->url.'/index.php?a=page&name='.$page['name']).'" rel="loadpage"><img src="'.permalink($this->url.'/image.php?t=a&w=48&h=48&src='.$page['image']).'" width="24" height="24">'.($notifications ? '<span class="admin-notifications-number sidebar-notifications-number">'.$notifications.'</span>' : '').''.$page['title'].'</a><div class="sidebar-settings-container" onclick="messageMenu('.$page['id'].', 3)"><div class="settings_btn sidebar-settings'.($visible ? '' : ' s-settings-hidden').'"></div></div></div>';

				// Add the context menu
				$output .= $menu;

				// Display the show more arrow
				if($i == 5 && count($row) > 5 && !$visible) {
					// Output the links
					$output .= '<div class="sidebar-link sidebar-more" id="show-more-btn-4"><a href="javascript:;" onclick="sidebarShow(4)"><div class="message-menu sidebar-arrow"></div></a></div>';

					// Hide the rest of the elements
					$hidden = ' style="display: none;"';
				}

				$i++;
			}
		}
		$output .= '</div></div>';
		return $output;
	}

	function sidebarGroups($visible = null) {
		// Visibile: If the user is on the Group page
		global $CONF, $LNG;

		// Select the groups and group by the ones owned, group by name
		$query = $this->db->query(sprintf("SELECT * FROM `groups_users`, `groups` WHERE `groups_users`.`user` = '%s' AND `groups_users`.`status` = 1 AND `groups_users`.`group` = `groups`.`id` ORDER BY `permissions` DESC, `groups`.`title` ASC LIMIT %s", $this->db->real_escape_string($this->id), $this->groups_limit));
		$row = array();

		while($rows = $query->fetch_assoc()) {
			$row[] = $rows;
		}

		$output = '<div class="sidebar-container widget-groups"><div class="sidebar-content"><div class="sidebar-header"><a href="'.permalink($this->url.'/index.php?a=group').'" rel="loadpage">'.$LNG['groups'].'</a></span></div>';

		if(!$visible) {
			$output .= '<div class="sidebar-link"><a href="'.permalink($this->url.'/index.php?a=group').'" rel="loadpage"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/plus.svg" width="24" height="24">'.$LNG['create_group'].'</a></div>';
		}

		if($row) {
			$i = 1;
			$hidden = '';
			foreach($row as $group) {
				if($group['permissions'] == 2) {
					$menulist = '
					<a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name'].'&r=edit').'" rel="loadpage"><div class="message-menu-row">'.$LNG['edit'].'</div></a>
					<a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name'].'&r=delete').'" rel="loadpage"><div class="message-menu-row">'.$LNG['delete'].'</div></a>';
				} else {
					$menulist = '<a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name']).'" rel="loadpage"><div class="message-menu-row">'.$LNG['leave_group'].'</div></a>';
				}
				$menu = '
				<div id="group-menu'.$group['id'].'" class="message-menu-container sidebar-menu-container">
					'.$menulist.'
				</div>';

				// When on group page, highlight groups that user creeated
				$class = '';
				if($visible && $group['permissions'] == 2) {
					$class = ' sidebar-link-active';
				}

				$notifications = $this->groupActivity(2, 0, $group['id']);

				$output .= '<div class="sidebar-link sidebar-group'.$class.'" id="group-'.$group['id'].'"'.$hidden.'><a href="'.permalink($this->url.'/index.php?a=group&name='.$group['name']).'" rel="loadpage"><img src="'.permalink($this->url.'/image.php?t=c&w=48&h=48&src='.$group['cover']).'" width="24" height="24">'.($notifications ? '<span class="admin-notifications-number sidebar-notifications-number">'.$notifications.'</span>' : '').''.$group['title'].'</a><div class="sidebar-settings-container" onclick="messageMenu('.$group['id'].', 2)"><div class="settings_btn sidebar-settings'.($visible ? '' : ' s-settings-hidden').'"></div></div></div>';

				// Add the context menu
				$output .= $menu;

				// Display the show more arrow
				if($i == 5 && count($row) > 5 && !$visible) {
					// Output the links
					$output .= '<div class="sidebar-link sidebar-more" id="show-more-btn-3"><a href="javascript:;" onclick="sidebarShow(3)"><div class="message-menu sidebar-arrow"></div></a></div>';

					// Hide the rest of the elements
					$hidden = ' style="display: none;"';
				}

				$i++;
			}
		}
		$output .= '</div></div>';
		return $output;
	}

	function sidebarInput($type) {
		global $LNG;
		if($type == 2) {
			$name = 'page';
		} else {
			$name = 'group';
		}
		if($type == 1) {
			$class = 'search-group';
			$title = $LNG['search_this_group'];
			$url = permalink($this->url.'/index.php?a=group&name='.$this->group_data['name'].'&search=');
			$placeholder = $LNG['search_this_group'];
			$value = (!empty($_GET['search']) ? $_GET['search'] : '');
		} else {
			$class = 'invite-'.$name;
			$title = $LNG['invite_friends'];
			$url = permalink($this->url.'/index.php?a='.$name.'&name='.($type == 2 ? $this->page_data['name'] : $this->group_data['name']).'&friends=');
			$placeholder = $LNG['search_in_friends'];
			$value = (!empty($_GET['friends']) ? $_GET['friends'] : '');
		}

		$output = '<div class="sidebar-container widget-'.$class.'"><div class="sidebar-content"><div class="sidebar-header">'.$title.'</div><div class="sidebar-inner"><input type="text" name="search-sidebar" id="'.$class.'" class="search-sidebar" onkeydown="if(event.keyCode==13){searchFriends(\''.$url.'\', '.$type.')}" placeholder="'.$placeholder.'" value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"><div id="search-sidebar-btn" onclick="searchFriends(\''.$url.'\', '.$type.');"></div></div></div></div>';

		return $output;
	}

	function inviteGroup($type, $user) {
		// Type 0: Check if the user can be invited to join a group
		// Type 1: Send the invitation

		// Check if the invited user is a friend
		$friendsList = $this->getFriendsList(0);
		$friendsList = explode(',', $friendsList);
		if(!in_array($user, $friendsList)) {
			return false;
		}

		if($type) {
			// Get the current group/invitation status
			$status = $this->inviteGroup(0, $user);

			// If the user is not notified or is not in the group
			if(!$status) {
				$query = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '0', '6', '0')", $this->id, $this->db->real_escape_string($user), $this->group_data['id']));

				// If email on likes is enabled in admin settings
				if($this->email_group_invite) {

					// Select the tageted user information
					$query = $this->db->query(sprintf("SELECT `email_group_invite`, `username`, `first_name`, `last_name`, `email` FROM `users` WHERE `idu` = '%s'", $this->db->real_escape_string($user)));
					

					$row = $query->fetch_assoc();
				
					// If user has emails on group invitations enabled
					if($row['email_group_invite'] && ($this->id !== $row['idu'])) {
						global $LNG;

						// Send e-mail
						sendMail($row['email'], sprintf($LNG['ttl_group_invite'], $this->username), sprintf($LNG['group_invite'], realName($row['username'], $row['first_name'], $row['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, permalink($this->url.'/index.php?a=group&name='.$this->group_data['name']), $this->group_data['title'], $this->group_data['invitecode'],$this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->email);
					}
				}
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `notifications` WHERE `from` = '%s' AND `to` = '%s' AND `parent` = '%s' AND `type` = 6", $this->id, $this->db->real_escape_string($user), $this->group_data['id']));

			if($query->num_rows > 0) {
				return 1;
			} else {
				// Check if the user is already in the group
				$query = $this->db->query(sprintf("SELECT * FROM `groups_users` WHERE `user` = '%s' AND `group` = '%s'", $this->db->real_escape_string($user), $this->group_data['id']));

				if($query->num_rows > 0) {
					return 2;
				}
				return false;
			}
		}
	}

	function invitePage($type, $user) {
		// Type 0: Check if the user can be invited to join a page
		// Type 1: Send the invitation

		// Check if the invited user is a friend
		$friendsList = $this->getFriendsList(0);
		$friendsList = explode(',', $friendsList);
		if(!in_array($user, $friendsList)) {
			return false;
		}

		if($type) {
			// Get the current page/invitation status
			$status = $this->invitePage(0, $user);

			// If the user is not notified or didn't liked the page
			if(!$status) {
				$query = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '0', '9', '0')", $this->id, $this->db->real_escape_string($user), $this->page_data['id']));

				// If email on likes is enabled in admin settings
				if($this->email_page_invite) {

					// Select the tageted user information
					$query = $this->db->query(sprintf("SELECT `email_page_invite`, `username`, `first_name`, `last_name`, `email` FROM `users` WHERE `idu` = '%s'", $this->db->real_escape_string($user)));

					$row = $query->fetch_assoc();

					// If user has emails on page invitations enabled
					if($row['email_page_invite'] && ($this->id !== $row['idu'])) {
						global $LNG;

						// Send e-mail
						sendMail($row['email'], sprintf($LNG['ttl_page_invite'], $this->username), sprintf($LNG['page_invite'], realName($row['username'], $row['first_name'], $row['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, permalink($this->url.'/index.php?a=page&name='.$this->page_data['name']), $this->page_data['title'], $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->email);
					}
				}
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `notifications` WHERE `from` = '%s' AND `to` = '%s' AND `parent` = '%s' AND `type` = 9", $this->id, $this->db->real_escape_string($user), $this->page_data['id']));

			if($query->num_rows > 0) {
				return 1;
			} else {
				// Check if the user has already liked the page
				$query = $this->db->query(sprintf("SELECT * FROM `likes` WHERE `by` = '%s' AND `post` = '%s' AND `type` = 2", $this->db->real_escape_string($user), $this->page_data['id']));

				if($query->num_rows > 0) {
					return 2;
				}
				return false;
			}
		}
	}

	function searchFriends($value, $type = null) {
		// Type 0: Groups
		// Type 1: Pages
		global $LNG, $CONF;

		$friendsList = $this->getFriendsList(0);
		if(!$friendsList) {
			return false;
		}
		$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE (`users`.`username` LIKE '%s' OR concat_ws(' ', `users`.`first_name`, `users`.`last_name`) LIKE '%s') AND `users`.`suspended` = 0 AND `users`.`idu` IN (%s) ORDER BY `users`.`verified` DESC, `users`.`idu` DESC LIMIT 0, 50", '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $friendsList));

		// Sometimes the query might fail due to the fact that utf8 characters are being passed and the `username` sql field does not allow special chars
		if(!$query) {
			$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE concat_ws(' ', `users`.`first_name`, `users`.`last_name`) LIKE '%s' AND `users`.`suspended` = 0 AND `users`.`idu` IN (%s) ORDER BY `users`.`verified` DESC, `users`.`idu` DESC LIMIT 0, 50", '%'.$this->db->real_escape_string($value).'%', $friendsList));
		}

		$rows = [];
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

		$output = '';
		foreach($rows as $row) {
			if($type) {
				$status = $this->invitePage(0, $row['idu']);
			} else {
				$status = $this->inviteGroup(0, $row['idu']);
			}
			$class = 'button-normal';
			$action = '';
			if($status == 1) {
				$buttons = $LNG['invited'];
			} elseif($status == 2) {
				$buttons = ($type ? $LNG['liked'] : $LNG['member']);
			} else {
				$buttons = $LNG['invite'];
				$class = 'button-active';
				$action = ($type ? 'page(0, '.$row['idu'].', '.$this->page_data['id'].', \'\')' : 'group(7, '.$row['idu'].', '.$this->group_data['id'].')');
			}
			$output .= '<div class="message-container" id="'.($type ? 'page' : 'group').'-invite-'.$row['idu'].'">
							<div class="message-content">
								<div class="message-inner">
									<div class="users-button '.$class.'">
										<a onclick="'.$action.'">'.$buttons.'</a>
									</div>
									<div class="message-avatar" id="avatar'.$row['idu'].'">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author" id="author'.$row['idu'].'">
											<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
										</div>
										<div class="message-time">
											'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
										</div>
									</div>
								</div>
							</div>
						</div>';
		}
		return $output;
	}

	function getMentions($target, $string) {
		$query = $this->db->query(sprintf("SELECT `username`, `first_name`, `last_name`, `image` FROM (SELECT * FROM (SELECT `user2` as `friends` FROM `friendships` WHERE `user1` = '%s' AND `status` = '1' UNION ALL SELECT `user1` as `friends` FROM `friendships` WHERE `user2` = '%s' AND `status` = '1' ORDER BY `friends` ASC) as f LEFT JOIN `users` ON `f`.`friends` = `users`.`idu` LIMIT 0, 2000) as r WHERE `username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s' LIMIT 10", $this->id, $this->id, '%'.$this->db->real_escape_string($string).'%', '%'.$this->db->real_escape_string($string).'%'));

		if($query->num_rows > 0) {
			$output = '<div id="mentions-container">';
			while($row = $query->fetch_assoc()) {
				$output .= '<div class="mention-user" onclick="doMention(\''.htmlspecialchars($target, ENT_QUOTES, 'UTF-8').'\', \''.htmlspecialchars($string, ENT_QUOTES, 'UTF-8').'\', \''.$row['username'].'\')"><img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'" width="16" height="16"> <strong>@'.$row['username'].'</strong> '.realName(null, $row['first_name'], $row['last_name']).'</div>';
			}
			$output .= '</div>';
		} else {
			return false;
		}

		return $output;
	}

	function listGroupMembers($type = null, $start = null) {
		// Type 0: Load group members
		// Type 1: Load group admins
		// Type 2: Load group requests
		// Type 3: Load group blocks
		// Type 4: Search group members
		global $LNG, $CONF;

		$start = $this->db->real_escape_string($start);

		if($type == 1) {
			$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 1 AND `groups_users`.`permissions` IN (1, 2) AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` ORDER BY `permissions` DESC LIMIT %s, %s", $this->group_data['id'], $start, ($this->s_per_page + 1)));
		} elseif($type == 2) {
			$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 0 AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` LIMIT %s, %s", $this->group_data['id'], $start, ($this->s_per_page + 1)));
		} elseif($type == 3) {
			$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 2 AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` ORDER BY `groups_users`.`time` DESC LIMIT %s, %s", $this->group_data['id'], $start, ($this->s_per_page + 1)));
		} elseif($type == 4) {
			$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 1 AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` AND (`users`.`username` LIKE '%s' OR concat_ws(' ', `users`.`first_name`, `users`.`last_name`) LIKE '%s') ORDER BY `groups_users`.`time` DESC LIMIT 0, 50", $this->group_data['id'], '%'.$this->db->real_escape_string($start).'%', '%'.$this->db->real_escape_string($start).'%'));

			// Sometimes the query might fail due to the fact that utf8 characters are being passed and the `username` sql field does not allow special chars
			if(!$query) {
				$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 1 AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` AND concat_ws(' ', `users`.`first_name`, `users`.`last_name`) LIKE '%s' ORDER BY `groups_users`.`time` DESC LIMIT 0, 50", $this->group_data['id'], '%'.$this->db->real_escape_string($start).'%'));
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `groups_users`,`users` WHERE `groups_users`.`status` = 1 AND `groups_users`.`group` = '%s' AND `groups_users`.`user` = `users`.`idu` ORDER BY `groups_users`.`permissions` DESC, `groups_users`.`id` DESC LIMIT %s, %s", $this->group_data['id'], $start, ($this->s_per_page + 1)));
		}

		$rows = [];
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

        $output = $loadmore = '';

		// Disable the loadmore button if on the Group Search page
		if($type == 4) {
		    $loadmore = 0;
        } else {
            if(array_key_exists($this->s_per_page, $rows)) {
                $loadmore = 1;

                // Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
                array_pop($rows);
            }
        }

		foreach($rows as $row) {
			/*
			// Array Map
			// array => { url, name, dynamic load, class type}
			*/
			$array = array();
			$buttons = '';
			// If the logged-in user has admin permissions and the $row['user'] is not the same with the logged-in user
			if(isset($this->group_member_data['permissions']) && in_array($this->group_member_data['permissions'], array(1, 2)) && $row['user'] !== $this->id) {
				// If the user has Admin privileges
				if($row['permissions'] == '1') {
					$x = 1;
				} else {
					$x = 0;
				}
				// If the logged-in user is the group owner
				if($this->group_member_data['permissions'] == '2') {
					$y = 1;
				} else {
					$y = 0;
				}
				// If the logged-in user is a group Admin
				if($this->group_member_data['permissions'] == '1') {
					$z = 1;
				} else {
					$z = 0;
				}
				// If the user is not the Group owner
				if($row['permissions'] !== '2') {
					if($type == 1) {
						if($y) {
							$array = array($LNG['remove'] => array(0, 'remove'), $LNG['block'] => array(2, 'block'), $LNG['remove_admin'] => array(5, 'remove-admin'));
						}
					} elseif($type == 2) {
						$array = array($LNG['decline'] => array(0, 'remove'), $LNG['block'] => array(2, 'block'), $LNG['approve'] => array(1, 'approve'));
					} elseif($type == 3) {
						$array = array($LNG['remove'] => array(0, 'remove'), $LNG['unblock'] => array(3, 'approve'));
					} else {
						if($z && !$x || $y) {
							$array = array($LNG['remove'] => array(0, 'remove'), $LNG['block'] => array(2, 'block'));
						}
						if($y) {
							$array[($x ? $LNG['remove_admin'] : $LNG['make_admin'])] = ($x ? array(5, 'remove-admin') : array(4, 'make-admin'));
						}
					}
				}
				// Output the buttons
				$buttons = '<div class="sidebar-gr-btn-container">';
				foreach($array as $button => $value) {
					$buttons .= '<a onclick="group(0, '.$value[0].', '.$row['group'].', '.$row['user'].', '.$row['id'].')" title="'.$button.'"><div class="group-button '.$value[1].'-button"></div></a>';
				}
				$buttons .= '</div>';
			}

			$output .= '<div class="message-container" id="group-request-'.$row['id'].'">
							<div class="message-content">
								<div class="message-inner">
								'.$buttons.'
									<div class="message-avatar" id="avatar'.$row['idu'].'">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author" id="author'.$row['idu'].'">
											<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
										</div>
										<div class="message-time">
											'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
										</div>
									</div>
								</div>
							</div>
						</div>';
		}
		if($loadmore) {
            $output .= '<div class="load_more" id="more_messages"><a onclick="group(1, '.$type.', '.$this->group_data['id'].', \''.$this->db->real_escape_string($start + $this->s_per_page).'\', \'\')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}
		return $output;
	}

	function sidebarBirthdays() {
		global $CONF, $LNG;
		$friendslist = $this->friends;
		// If there are no friends, return false
		if(empty($friendslist)) {
			return false;
		}

		// Count the birthdays today
		$qCount = $this->db->query(sprintf("SELECT COUNT(*) AS `value` FROM `users` WHERE EXTRACT(MONTH FROM `born`) = '%s' AND EXTRACT(DAY FROM `born`) = '%s' AND `idu` IN (%s)", date('m'), date('d'), $friendslist));

		$count = $qCount->fetch_assoc();

		if($count['value']) {
			$qResult = $this->db->query(sprintf("SELECT `username`, `first_name`, `last_name` FROM `users` WHERE EXTRACT(MONTH FROM `born`) = '%s' AND EXTRACT(DAY FROM `born`) = '%s' AND `idu` IN (%s) LIMIT 1", date('m'), date('d'), $friendslist));

			$result = $qResult->fetch_assoc();

			return '<div class="sidebar-container widget-birthdays"><div class="sidebar-content"><div class="sidebar-header"><a href="'.permalink($this->url.'/index.php?a=notifications&filter=birthdays').'" rel="loadpage">'.$LNG['friends_birthdays'].'</a></div><div class="sidebar-inner"><div class="sidebar-birthdays"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/n_birthday.png" width="16" height="16">'.(($count['value'] == 1) ? sprintf($LNG['new_birthday_notification'], permalink($this->url.'/index.php?a=notifications&filter=birthdays'), realName($result['username'], $result['first_name'], $result['last_name'])) : sprintf($LNG['x_and_x_others'], permalink($this->url.'/index.php?a=notifications&filter=birthdays'), realName($result['username'], $result['first_name'], $result['last_name']), permalink($this->url.'/index.php?a=notifications&filter=birthdays'), ($count['value']-1))).'</div></div></div></div>';
		}
	}

	function sidebarFriendsActivity($limit, $type = null) {
		global $LNG, $CONF;

		$friendslist = $this->friends;
		// If there are no friends, return false
		if(empty($friendslist)) {
			return false;
		}

		// Define the arrays that holds the values (prevents the array_merge to fail, when one or more options are disabled)
		$likes = array();
		$comments = array();

		$checkLikes = $this->db->query(sprintf("SELECT * FROM  `messages`, `likes`, `users` WHERE `likes`.`by` = `users`.`idu` AND `likes`.`post` = `messages`.`id` AND `messages`.`page` = 0 AND `likes`.`type` = '0' AND `likes`.`by` IN (%s) AND `users`.`suspended` = 0 ORDER BY `likes`.`id` DESC LIMIT %s", $friendslist, 25));
		while($row = $checkLikes->fetch_assoc()) {
			$likes[] = $row;
		}

		$checkComments = $this->db->query(sprintf("SELECT * FROM  `messages`, `comments`, `users` WHERE `comments`.`uid` = `users`.`idu` AND `comments`.`mid` = `messages`.`id` AND `messages`.`page` = 0 AND `comments`.`uid` IN (%s) AND `users`.`suspended` = 0 ORDER BY `comments`.`id` DESC LIMIT %s", $friendslist, 25));

		while($row = $checkComments->fetch_assoc()) {
			$comments[] = $row;
		}

		// If there are no latest notifications
		if(empty($likes) && empty($comments)) {
			return false;
		}

		// Add the types into the recursive array results
		$x = 0;
		foreach($likes as $like) {
			$likes[$x]['event'] = 'like';
			$x++;
		}
		$y = 0;
		foreach($comments as $comment) {
			$comments[$y]['event'] = 'comment';
			$y++;
		}

		$array = array_merge($likes, $comments);

		// Sort the array
		usort($array, 'sortDateAsc');

		$activity = '<div class="sidebar-container widget-friends-activity"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['sidebar_friends_activity'].'</div><div class="sidebar-fa-content scrollable">';
		$i = 0;
		foreach($array as $value) {
			if($i == $limit) break;
			$time = $value['time']; $b = '';
			if($this->time == '0') {
				$time = date("c", strtotime($value['time']));
			} elseif($this->time == '2') {
				$time = $this->ago(strtotime($value['time']));
			} elseif($this->time == '3') {
				$date = strtotime($value['time']);
				$time = date('Y-m-d', $date);
				$b = '-standard';
			}
			$activity .= '<div class="notification-row"><div class="notification-padding">';
			if($value['event'] == 'like') {
				$activity .= '<div class="sidebar-fa-image"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="sidebar-fa-text">'.sprintf($LNG['new_like_fa'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=post&m='.$value['post'])).'. <span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
			} elseif($value['event'] == 'comment') {
				$activity .= '<div class="sidebar-fa-image"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$value['username']).'" rel="loadpage"><img class="notifications" src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$value['image']).'"></a></div><div class="sidebar-fa-text">'.sprintf($LNG['new_comment_fa'], permalink($this->url.'/index.php?a=profile&u='.$value['username']), realName($value['username'], $value['first_name'], $value['last_name']), permalink($this->url.'/index.php?a=post&m='.$value['mid'])).'. <span class="timeago'.$b.'" title="'.$time.'">'.$time.'</span></div>';
			}
			$activity .= '</div></div>';
			$i++;
		}
		$activity .= '</div></div></div>';

		return $activity;
	}

	function sidebarSuggestions($interests) {
		global $LNG;

		$friendslist = $this->getFriendsList(1);

		// If there are friends available, exclude them
		if($friendslist) {
			$friendslist = $this->id.','.$friendslist;
		} else {
			$friendslist = $this->id;
		}

        if ($interests == 0) {
            $interestsFilter = '';
        } else {
            $interestsFilter = sprintf("AND `gender` = '%s'", $this->db->real_escape_string($interests));
        }

		$query = $this->db->query(sprintf("SELECT `idu`, `username`, `first_name`, `last_name`, `location`, `image`  FROM `users` WHERE `idu` NOT IN (%s) %s AND `suspended` = 0 ORDER BY `idu` DESC LIMIT 6", $friendslist, $interestsFilter));

		// Store the array results
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

		// If suggestions are available
		if(!empty($rows)) {
			$i = 0;

			$output = '<div class="sidebar-container widget-suggestions"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['sidebar_suggestions'].'</div><div class="sidebar-padding">';
			foreach($rows as $row) {
				if($i == 6) break; // Display only the last 6 suggestions

				// Add the elemnts to the array
				$output .= '<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage"><div class="sidebar-subscriptions"><div class="sidebar-title-container"><div class="sidebar-title-name">'.realName($row['username'], $row['first_name'], $row['last_name']).'</div></div><img src="'.permalink($this->url.'/image.php?t=a&w=112&h=112&src='.$row['image']).'"></div></a>';
				$i++;
			}
			$output .= '</div></div></div>';
			return $output;
		} else {
			return false;
		}
	}

	function sidebarTrending($bold, $per_page) {
		global $LNG;

		// Select all the messages that has #hashtags today
		$query = $this->db->query(sprintf("SELECT * FROM `messages`, `users` WHERE `messages`.`uid` = `users`.`idu` AND `messages`.`time` >= '%s 00:00:00' AND `messages`.`time` <= '%s 23:59:59' AND `messages`.`tag` != '' AND `messages`.`group` = 0 AND `messages`.`public` = 1 AND `users`.`suspended` = 0 LIMIT 5000", date('Y-m-d', strtotime('-1 day')), date('Y-m-d')));

		$hashtags = '';
		// Store the hashtags into a string
		while($row = $query->fetch_assoc()) {
			$hashtags .= $row['tag'].',';
		}

		// If there are trends available
		if(!empty($hashtags)) {
			$i = 0;
			// Count the array values and filter out the blank spaces (also lowercase all array elements to prevent case-insensitive showing up, e.g: Test, test, TEST)
			$hashtags = explode(',', $hashtags);
			$count = array_count_values(array_map('mb_strtolower', array_filter($hashtags)));

			// Sort them by trend
			arsort($count);
			$output = '<div class="sidebar-container widget-trending"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['sidebar_trending'].'</div>';
			foreach($count as $row => $value) {
				if($i == $per_page) break; // Display and break when the trends hits the limit

				$class = '';
				if($row == mb_strtolower($bold)) {
					$class = ' sidebar-link-active';
				}
				$output .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($this->url.'/index.php?a=search&tag='.$row).'" rel="loadpage">#'.$row.'</a></div>';

				$i++;
			}
			$output .= '</div></div>';
			return $output;
		} else {
			return false;
		}
	}

	function getPictures() {
		// Type 0: Return the pictures count
		$query = $this->db->query(sprintf("SELECT count(`id`) FROM `messages` USE INDEX(`uid`, `type`) WHERE `type` = 'picture' AND `group` = 0 AND `uid` = '%s'", $this->profile_data['idu']));

		$result = $query->fetch_array();
		return $result[0];
	}

	function countGroups() {
		// Type 0: Return the pictures count
		$query = $this->db->query(sprintf("SELECT count(`id`) FROM `groups_users` WHERE `user` = '%s' AND `status` = '1'", $this->profile_data['idu']));

		$result = $query->fetch_array();
		return $result[0];
	}

	function getShares($start = null, $id) {
		global $LNG, $CONF;
		if($start == 0) {
			$start = '';
		} else {
			// Else, build up the query
			$start = 'AND `messages`.`id` < \''.$this->db->real_escape_string($start).'\'';
		}

		$query = $this->db->query(sprintf("SELECT * FROM `messages`, `users` WHERE `messages`.`value` = '%s' AND `messages`.`type` = 'shared' AND `messages`.`uid` = `users`.`idu` %s ORDER BY `messages`.`id` DESC LIMIT %s", $this->db->real_escape_string($id), $start, ($this->per_page + 1)));

		// Declare the rows array
		$rows = array();
		while($row = $query->fetch_assoc()) {
			// Store the result into the array
			$rows[] = $row;
		}

        $output = $loadmore = '';

		// Decide whether the load more will be shown or not
		if(array_key_exists($this->per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		foreach($rows as $row) {
			$output .= '<div class="modal-listing">
							<div class="modal-listing-inner">
								<div class="users-button button-normal">
									<a href="'.permalink($this->url.'/index.php?a=post&m='.$row['id']).'" rel="loadpage">'.$LNG['view'].'</a>
								</div>
								<div class="message-avatar" id="avatar'.$row['idu'].'">
									<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
										<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
									</a>
								</div>
								<div class="message-top">
									<div class="message-author" id="author'.$row['idu'].'" rel="loadpage">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
									</div>
									<div class="message-time">
										'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
									</div>
								</div>
							</div>
						</div>';
			$start = $row['id'];
		}

		if($loadmore) {
			$output .= '<div id="more-shares" class="modal-listing-load-more">
							<a onclick="loadShares('.$start.', \''.$id.'\')">'.$LNG['view_more_messages'].'</a>
						</div>';
		}
		return $output;
	}

	function getLikes($start = null, $type = null, $value = null, $for = null) {
		global $LNG;
		// Type 0: Return the likes count
		// Type 1: Return the likes for messages & comments
		// For 	0: Messages
		// For	1: Commments
		// For 	2: Pages
		if($type == 1) {
			global $CONF;

			if($start == 0) {
				$start = '';
			} else {
				// Else, build up the query
				$start = 'AND `likes`.`id` < \''.$this->db->real_escape_string($start).'\'';
			}

			$query = $this->db->query(sprintf("SELECT * FROM `likes`, `users` WHERE `likes`.`post` = '%s' AND `likes`.`type` = '%s' AND `likes`.`by` = `users`.`idu` %s ORDER BY `likes`.`id` DESC LIMIT %s", $this->db->real_escape_string($value), $this->db->real_escape_string($for), $start, ($this->per_page + 1)));

			// Declare the rows array
			$rows = array();
			while($row = $query->fetch_assoc()) {
				// Store the result into the array
				$rows[] = $row;
			}

			$output = $loadmore = '';

			// Decide whether the load more will be shown or not
			if(array_key_exists($this->per_page, $rows)) {
				$loadmore = 1;

				// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
				array_pop($rows);
			}

			foreach($rows as $row) {
				$output .= '<div class="modal-listing">
								<div class="modal-listing-inner">
									<div id="friend'.$row['idu'].'">'.$this->friendship(0, array('idu' => $row['idu'], 'username' => $row['username'], 'private' => $row['private']), 1).'</div>'.$this->chatButton($row['idu'], $row['username'], 1).'
									<div class="message-avatar" id="avatar'.$row['idu'].'">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author" id="author'.$row['idu'].'" rel="loadpage">
											<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
										</div>
										<div class="message-time">
											'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
										</div>
									</div>
								</div>
							</div>';
				$start = $row['id'];
			}

			if($loadmore) {
				$output .= '<div id="more-likes" class="modal-listing-load-more">
								<a onclick="loadLikes('.$start.', \''.$value.'\', \''.$type.'\', '.$for.')">'.$LNG['view_more_messages'].'</a>
							</div>';
			}
			return $output;
		} elseif($type == 2) {
			if($start) {
				$query = $this->db->query(sprintf("SELECT * FROM `likes`, `users` WHERE `post` = '%s' AND `type` = '2' AND `likes`.`by` = `users`.`idu` ORDER BY `likes`.`id` DESC LIMIT 0, %s", $this->page_data['id'], $start));
				$rows = '<div class="message-divider"></div><div class="page-inner">';

				while($row = $query->fetch_assoc()) {
					$rows .= '<div class="likes-users"><a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" title="'.realName($row['username'], $row['first_name'], $row['last_name']).'" rel="loadpage"><img src="'.permalink($this->url.'/image.php?t=a&w=112&h=112&src='.$row['image']).'"></a></div>';
				}
				$rows .= '</div>';
				return $rows;
			} else {
                if (isset($this->page_data)) {
                    $query = $this->db->query(sprintf("SELECT
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2') as total,
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2' AND `time` >= '%s') as today,
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2' AND `time` >= '%s' AND `time` < '%s') as yesterday,
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2' AND `time` >= '%s' AND `time` <= '%s') as this_week,
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2' AND `time` >= '%s' AND `time` < '%s') as this_month,
                        (SELECT COUNT(*) FROM `likes` WHERE `post` = '%s' AND `type` = '2' AND `time` >= '%s' AND `time` < '%s') as this_year",
                        $this->page_data['id'],
                        $this->page_data['id'], date('Y-m-d'),
                        $this->page_data['id'], date('Y-m-d', strtotime('-1 day')), date('Y-m-d'),
                        $this->page_data['id'], date('Y-m-d', strtotime('last Monday')), date('Y-m-d', strtotime('next Sunday')),
                        $this->page_data['id'], date('Y-m-01'), date('Y-m-01', strtotime('+1 month')),
                        $this->page_data['id'], date('Y-01-01'), date('Y-01-01', strtotime('+1 year'))));

                    while($row = $query->fetch_assoc()) {
                        $rows[] = $row;
                    }

                    return $rows[0];
                }
			}
		} else {
			$query = $this->db->query(sprintf("SELECT count(`id`) FROM `likes` WHERE `likes`.`by` = '%s' AND `likes`.`type` = 2", $this->profile_data['idu']));

			// Store the array results
			$result = $query->fetch_array();

			// Return the likes value
			return $result[0];
		}
	}

	function getHashtags($start, $per_page, $value, $type = null, $filter = null) {
		global $LNG;
		// TYPE 0: Return the messages for the queried hashtag
		// TYPE 1: Return the queries hashtags list
		if($type) {
			if($type) {
				$query = $this->db->query(sprintf("SELECT `messages`.`tag` FROM `messages`, `users` WHERE `messages`.`uid` = `users`.`idu` AND `messages`.`tag` LIKE '%s' AND `messages`.`group` = 0 AND `messages`.`public` = 1 AND `users`.`suspended` = 0 LIMIT 10", '%'.$this->db->real_escape_string($value).'%'));
			}

			// Store the hashtags into a string
            $hashtags = '';
			while($row = $query->fetch_assoc()) {
				$hashtags .= $row['tag'];
			}

			$output = '<div class="search-content"><div class="search-results"><div class="notification-inner"><a onclick="manageResults(2)"><strong>'.$LNG['view_all_results'].'</strong></a> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div>';
			// If there are no results
			if(empty($hashtags)) {
				$output .= '<div class="message-inner">'.$LNG['no_results'].'</div>';
			} else {
				// Explore each hashtag string into an array
				$explode = explode(',', $hashtags);

				// Merge all matched arrays into a string
				$rows = array_unique(array_map('mb_strtolower', $explode));

				foreach($rows as $row) {
					if(stripos($row, $value) !== false) {
						$output .= '<div class="hashtag">
										<a href="'.permalink($this->url.'/index.php?a=search&tag='.$row).'" rel="loadpage">
											<div class="hashtag-inner">
												#'.$row.'
											</div>
										</a>
									</div>';
					}
				}
			}
			$output .= '</div></div>';
		} else {
			// If the $start value is 0, empty the query;
			if($start == 0) {
				$start = '';
			} else {
				// Else, build up the query
				$start = 'AND messages.id < \''.$this->db->real_escape_string($start).'\'';
			}

			if(!empty($filter)) {
				// Set the filter to be passed to the getMessages() function
				$this->hashtags = saniscape($filter);

				$filter = sprintf("AND `time` >= '%s' AND `time` < '%s'", $this->db->real_escape_string($filter).'-01-01 00:00:00', ($this->db->real_escape_string($filter)+1).'-01-01 00:00:00');
			}

			$query = sprintf("SELECT * FROM `messages`, `users` WHERE `messages`.`tag` REGEXP '[[:<:]]%s[[:>:]]' AND `messages`.`uid` = `users`.`idu` %s %s AND `messages`.`group` = 0 AND `messages`.`public` = 1 AND `users`.`suspended` = 0 ORDER BY `messages`.`id` DESC LIMIT %s", $this->db->real_escape_string($value), $filter, $start, ($this->per_page + 1));

			return $this->getMessages($query, 'loadHashtags', '\''.saniscape($value).'\'');
		}
		return $output;
	}

	function getSearch($start, $per_page, $value, $filter = null, $age = null, $type = null) {
		// $type - switches the type for live search or static one [search page]
		global $LNG, $CONF;

		// Define the query type
		// Query Type 0: Normal search username, first and last name
		// Query Type 1: Live Search
		if(filter_var($value, FILTER_VALIDATE_EMAIL)) {
			$qt = 1;
		} else {
			$qt = 0;
		}

		// If the gender filter is set, and the age is also set
		if(($filter == 'm' || $filter == 'f') && preg_match('/^[0-9]+-[0-9]+$/i', $age)) {
			if($filter == 'm') {
				$gender = 1;
			} else {
				$gender = 2;
			}

			// Build the current date
			$year = date('Y'); $month = date('m'); $day = date('d');
			$date = explode('-', $age);

			// Between age
			$x = ($year-$date[0]).'-'.$month.'-'.$day;
			// To age
			$y = ($year-$date[1]).'-'.$month.'-'.$day;

			if($qt == 1) {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `gender` = '%s' AND `born` BETWEEN '%s' AND '%s' AND `email` = '%s' LIMIT 1", $gender,  $this->db->real_escape_string($x), $this->db->real_escape_string($y), $this->db->real_escape_string($value)));
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `gender` = '%s' AND `born` BETWEEN '%s' AND '%s' AND (`username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s') AND `suspended` = 0 ORDER BY `verified` DESC, `idu` DESC LIMIT %s, %s", $gender, $this->db->real_escape_string($x), $this->db->real_escape_string($y), '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($per_page + 1)));
			}
		}
		// If the filter is male / female (alpha type)
		elseif($filter == 'm' || $filter == 'f') {
			if($filter == 'm') {
				$gender = 1;
			} else {
				$gender = 2;
			}
			if($qt == 1) {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `gender` = '%s' AND `email` = '%s' LIMIT 1", $gender, $this->db->real_escape_string($value)));
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `gender` = '%s' AND (`username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s') AND `suspended` = 0 ORDER BY `verified` DESC, `idu` DESC LIMIT %s, %s", $gender, '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($per_page + 1)));
			}
		}
		// If the filter is a date range (digit type)
		elseif(preg_match('/^[0-9]+-[0-9]+$/i', $age)) {
			// Build the current date
			$year = date('Y'); $month = date('m'); $day = date('d');
			$date = explode('-', $age);

			// Between age
			$x = ($year-$date[0]).'-'.$month.'-'.$day;
			// To age
			$y = ($year-$date[1]).'-'.$month.'-'.$day;

			if($qt == 1) {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `born` BETWEEN '%s' AND '%s' AND `email` = '%s' LIMIT 1", $this->db->real_escape_string($x), $this->db->real_escape_string($y), $this->db->real_escape_string($value)));
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `born` BETWEEN '%s' AND '%s' AND (`username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s')  AND `suspended` = 0 ORDER BY `verified` DESC, `idu` DESC LIMIT %s, %s", $this->db->real_escape_string($x), $this->db->real_escape_string($y), '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($per_page + 1)));
			}
		} else {
			if($qt == 1) {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE `email` = '%s' LIMIT 1", $this->db->real_escape_string($value)));
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `users` WHERE (`username` LIKE '%s' OR concat_ws(' ', `first_name`, `last_name`) LIKE '%s') AND `suspended` = 0 ORDER BY `verified` DESC, `idu` DESC LIMIT %s, %s", '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($per_page + 1)));
			}
		}

		$output = $loadmore = '';
		$rows = [];
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

		// If the query type is live, hide the load more button
		if(array_key_exists($per_page, $rows)) {
			$loadmore = 1;
			if($type) {
				$loadmore = 0;
			}

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		// If the query type is live show the proper style
		if($type) {
			$output = '<div class="search-content"><div class="search-results"><div class="notification-inner"><a onclick="manageResults(1)"><strong>'.$LNG['view_all_results'].'</strong></a> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div>';
			// If there are no results
			if(empty($rows)) {
				$output .= '<div class="message-inner">'.$LNG['no_results'].'</div>';
			} else {
				foreach($rows as $row) {
					$output .= '<div class="message-inner">
									<div id="friend'.$row['idu'].'">'.$this->friendship(0, array('idu' => $row['idu'], 'username' => $row['username'], 'private' => $row['private']), 1).'</div>'.$this->chatButton($row['idu'], $row['username'], 1).'
									<div class="message-avatar" id="avatar'.$row['idu'].'">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author" id="author'.$row['idu'].'">
											<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
										</div>
										<div class="message-time">
											'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
										</div>
									</div>
								</div>';
				}
			}
			$output .= '</div></div>';

		} else {
			// If there are no results
			if(empty($rows)) {
				$output .= '<div class="message-container"><div class="message-content"><div class="message-header">'.$LNG['search_title'].'</div><div class="message-inner">'.$LNG['no_results'].'</div></div></div>';
			} else {
				foreach($rows as $row) {
					$output .= '<div class="message-container">
									<div class="message-content">
										<div class="message-inner">
										<div id="friend'.$row['idu'].'">'.$this->friendship(0, array('idu' => $row['idu'], 'username' => $row['username'], 'private' => $row['private']), 1).'</div>'.$this->chatButton($row['idu'], $row['username'], 1).'
											<div class="message-avatar" id="avatar'.$row['idu'].'">
												<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
													<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
												</a>
											</div>
											<div class="message-top">
												<div class="message-author" id="author'.$row['idu'].'">
													<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
												</div>
												<div class="message-time">
													'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
												</div>
											</div>
										</div>
									</div>
								</div>';
				}
			}
		}
		if($loadmore) {
            $output .= '<div class="load_more" id="more_messages"><a onclick="loadPeople('.($start + $per_page).', \''.saniscape($value).'\', \''.saniscape($filter).'\', \''.saniscape($age).'\')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}

		return $output;
	}

	function getGroups($start = null, $value = null, $live = null) {
		global $LNG, $CONF;

		$start = $this->db->real_escape_string($start);

		if(empty($value)) {
			// If the value is empty, return the values for the Admin Panel query
			if(empty($live)) {
				$query = $this->db->query(sprintf("SELECT * FROM `groups` ORDER BY `id` DESC LIMIT %s, %s", $this->db->real_escape_string($start), ($this->per_page + 1)));
				$class = 'users-container';
				$button = $LNG['edit'];
				$link = $this->url.'/index.php?a=admin&b=manage_groups&c=';
				$admin = true;
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `groups_users`, `groups` WHERE `user` = '%s' AND `status` = 1 AND `groups_users`.`group` = `groups`.`id` ORDER BY `permissions` DESC LIMIT %s, %s", $this->profile_data['idu'], $this->db->real_escape_string($start), ($this->per_page + 1)));
				$class = 'message-container';
				$button = $LNG['view'];
				$link = permalink($this->url.'/index.php?a=group&name=');
				$profile = ", '".$this->profile_data['idu']."'";
				$live = null;
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `groups` WHERE `name` = '%s' OR (`name` LIKE '%s' OR `title` LIKE '%s') LIMIT %s, %s", $this->db->real_escape_string($value), '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($this->per_page + 1)));

			// Sometimes the query might fail due to the fact that utf8 characters are being passed and the `name` sql field does not allow special chars
			if(!$query) {
				$query = $this->db->query(sprintf("SELECT * FROM `groups` WHERE `title` = '%s' OR `title` LIKE '%s' LIMIT %s, %s", $this->db->real_escape_string($value), '%'.$this->db->real_escape_string($value).'%', ($this->per_page + 1)));
			}
			$class = 'message-container';
			$button = $LNG['view'];
			$link = permalink($this->url.'/index.php?a=group&name=');
		}

		$rows = [];
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

		if($live) {
			$output = '<div class="search-content"><div class="search-results"><div class="notification-inner"><a onclick="manageResults(3)"><strong>'.$LNG['view_all_results'].'</strong></a> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div>';
			// If there are no results
			if(empty($rows)) {
				$output .= '<div class="message-inner">'.$LNG['no_results'].'</div>';
			} else {
				foreach($rows as $row) {
					$output .= '<div class="message-inner" id="group'.$row['id'].'">
									<div class="users-button button-normal">
										<a href="'.permalink($this->url.'/index.php?a=group&name='.$row['name']).'" rel="loadpage">'.$LNG['view'].'</a>
									</div>
									<div class="message-avatar">
										<a href="'.permalink($this->url.'/index.php?a=group&name='.$row['name']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=c&w=48&h=48&src='.$row['cover']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author">
											<a href="'.permalink($this->url.'/index.php?a=group&name='.$row['name']).'" rel="loadpage">'.$row['title'].'</a>
										</div>
										<div class="message-time">
										'.($row['privacy'] ? $LNG['private_group'] : $LNG['public_group']).' ('.sprintf($LNG['x_members'], $row['members']).')
										</div>
									</div>
								</div>';
				}
			}
			$output .= '</div></div>';
		} else {
		    $output = $loadmore = '';

			if($value) {
				// If there are no results
				if(empty($rows)) {
					$output .= '<div class="message-container"><div class="message-content"><div class="message-header">'.$LNG['search_title'].'</div><div class="message-inner">'.$LNG['no_results'].'</div></div></div>';
				}
			}
			if(array_key_exists($this->per_page, $rows)) {
				$loadmore = 1;

				// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
				array_pop($rows);
			}

			foreach($rows as $row) {
				$output .= '<div class="'.$class.'" id="group'.$row['id'].'">
								<div class="message-content">
									<div class="message-inner">
										<div class="users-button button-normal">
											<a href="'.$link.$row['name'].'" rel="loadpage">'.$button.'</a>
										</div>
										<div class="message-avatar">
											<a href="'.permalink($this->url.'/index.php?a=group&name='.$row['name']).'" rel="loadpage">
												<img src="'.(isset($admin) ? $this->url.'/image.php?t=c&w=48&h=48&src='.$row['cover'] : permalink($this->url.'/image.php?t=c&w=48&h=48&src='.$row['cover'])).'">
											</a>
										</div>
										<div class="message-top">
											<div class="message-author">
												<a href="'.permalink($this->url.'/index.php?a=group&name='.$row['name']).'" rel="loadpage">'.(empty($value) ? $row['name'] : $row['title']).'</a>
											</div>
											<div class="message-time">
											'.($row['privacy'] ? $LNG['private_group'] : $LNG['public_group']).' ('.sprintf($LNG['x_members'], $row['members']).')
											</div>
										</div>
									</div>
								</div>
							</div>';
			}
			if($loadmore) {
                $output .= '<div class="load_more" id="'.(empty($value) && isset($this->profile_data['idu']) == false ? 'more_users' : 'more_messages').'"><a onclick="group('.(empty($value) ? 5 : 3).', \''.htmlspecialchars($value).'\', \''.$this->db->real_escape_string($start + $this->per_page).'\', \''.($this->profile_data['idu'] ?? null).'\')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
			}
		}
		return $output;
	}

	function getPages($start = null, $value = null, $live = null) {
		global $LNG, $CONF;

		if(empty($value)) {
			// If the value is empty, return the values for the Admin Panel query
			if(empty($live)) {
				$query = $this->db->query(sprintf("SELECT * FROM `pages` ORDER BY `id` DESC LIMIT %s, %s", $this->db->real_escape_string($start), ($this->per_page + 1)));
				$class = 'users-container';
				$button = $LNG['edit'];
				$link = $this->url.'/index.php?a=admin&b=manage_pages&c=';
				$admin = true;
			} else {
				$query = $this->db->query(sprintf("SELECT * FROM `likes`, `pages` WHERE `likes`.`by` = '%s' AND `likes`.`type` = 2 AND `likes`.`post` = `pages`.`id` ORDER BY `likes`.`id` DESC LIMIT %s, %s", $this->profile_data['idu'], $this->db->real_escape_string($start), ($this->per_page + 1)));
				$class = 'message-container';
				$button = $LNG['view'];
				$link = permalink($this->url.'/index.php?a=page&name=');
				$live = 0;
			}
		} else {
			$query = $this->db->query(sprintf("SELECT * FROM `pages` WHERE `name` = '%s' OR (`name` LIKE '%s' OR `title` LIKE '%s') ORDER BY `verified` DESC, `id` ASC LIMIT %s, %s", $this->db->real_escape_string($value), '%'.$this->db->real_escape_string($value).'%', '%'.$this->db->real_escape_string($value).'%', $this->db->real_escape_string($start), ($this->per_page + 1)));
			// Sometimes the query might fail due to the fact that utf8 characters are being passed and the `name` sql field does not allow special chars
			if(!$query) {
				$query = $this->db->query(sprintf("SELECT * FROM `pages` WHERE `title` = '%s' OR `title` LIKE '%s' ORDER BY `verified` DESC, `id` ASC LIMIT %s, %s", $this->db->real_escape_string($value), '%'.$this->db->real_escape_string($value).'%', ($this->per_page + 1)));
			}
			$class = 'message-container';
			$button = $LNG['view'];
			$link = $this->url.'/index.php?a=page&name=';
		}

		$rows = [];
		while($row = $query->fetch_assoc()) {
			$rows[] = $row;
		}

		if($live) {
			$output = '<div class="search-content"><div class="search-results"><div class="notification-inner"><a onclick="manageResults(4)"><strong>'.$LNG['view_all_results'].'</strong></a> <a onclick="manageResults(0)" title="'.$LNG['close_results'].'"><div class="delete_btn"></div></a></div>';
			// If there are no results
			if(empty($rows)) {
				$output .= '<div class="message-inner">'.$LNG['no_results'].'</div>';
			} else {
				foreach($rows as $row) {
					$output .= '<div class="message-inner" id="page'.$row['id'].'">
									<div class="users-button button-normal">
										<a href="'.permalink($this->url.'/index.php?a=page&name='.$row['name']).'" rel="loadpage">'.$LNG['view'].'</a>
									</div>
									<div class="message-avatar">
										<a href="'.permalink($this->url.'/index.php?a=page&name='.$row['name']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=48&h=48&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author">
											<a href="'.permalink($this->url.'/index.php?a=page&name='.$row['name']).'" rel="loadpage">'.$row['title'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_page'].'"></span>' : '').'
										</div>
										<div class="message-time">'.$LNG['page_'.$row['category']].' - '.$row['likes'].' '.$LNG['likes'].'</div>
									</div>
								</div>';
				}
			}
			$output .= '</div></div>';
		} else {
		    $output = $loadmore = '';

			if($value) {
				// If there are no results
				if(empty($rows)) {
					$output .= '<div class="message-container"><div class="message-content"><div class="message-header">'.$LNG['search_title'].'</div><div class="message-inner">'.$LNG['no_results'].'</div></div></div>';
				}
			}
			if(array_key_exists($this->per_page, $rows)) {
				$loadmore = 1;

				// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
				array_pop($rows);
			}

			foreach($rows as $row) {
				$output .= '<div class="'.$class.'" id="page'.$row['id'].'">
								<div class="message-content">
									<div class="message-inner">
										<div class="users-button button-normal">
											<a href="'.permalink($link.$row['name']).'" rel="loadpage">'.$button.'</a>
										</div>
										<div class="message-avatar">
											<a href="'.permalink($this->url.'/index.php?a=page&name='.$row['name']).'" rel="loadpage">
												<img src="'.(isset($admin) ? $this->url.'/image.php?t=a&w=48&h=48&src='.$row['image'] : permalink($this->url.'/image.php?t=a&w=48&h=48&src='.$row['image'])).'">
											</a>
										</div>
										<div class="message-top">
											<div class="message-author">
												<a href="'.permalink($this->url.'/index.php?a=page&name='.$row['name']).'" rel="loadpage">'.((empty($value) && is_null($live)) ? $row['name'] : $row['title']).'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_page'].'"></span>' : '').'
											</div>
											<div class="message-time">'.$LNG['page_'.$row['category']].' - '.$row['likes'].' '.$LNG['likes'].'</div>
										</div>
									</div>
								</div>
							</div>';
			}
			if($loadmore) {
                $output .= '<div class="load_more" id="'.(empty($value) && isset($this->profile_data['idu']) == false ? 'more_users' : 'more_messages').'"><a onclick="page('.(empty($value) ? 2 : 1).', \''.htmlspecialchars($value).'\', \''.$this->db->real_escape_string($start + $this->per_page).'\', \''.($this->profile_data['idu'] ?? null).'\')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
			}
		}
		return $output;
	}

	function listFriends($type = null) {
		global $LNG, $CONF;
		$rows = $this->listFriends;

        $output = $loadmore = '';

		if(array_key_exists($this->s_per_page, $rows)) {
			$loadmore = 1;

			// Unset the last array element because it's not needed, it's used only to predict if the Load More Messages should be displayed
			array_pop($rows);
		}

		foreach($rows as $row) {
			$output .= '<div class="message-container">
							<div class="message-content">
								<div class="message-inner">
								<div id="friend'.$row['idu'].'">'.$this->friendship(0, array('idu' => $row['idu'], 'username' => $row['username'], 'private' => $row['private']), 1).'</div>'.$this->chatButton($row['idu'], $row['username'], 1).'
									<div class="message-avatar" id="avatar'.$row['idu'].'">
										<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">
											<img src="'.permalink($this->url.'/image.php?t=a&w=50&h=50&src='.$row['image']).'">
										</a>
									</div>
									<div class="message-top">
										<div class="message-author" id="author'.$row['idu'].'">
											<a href="'.permalink($this->url.'/index.php?a=profile&u='.$row['username']).'" rel="loadpage">'.$row['username'].'</a>'.((!empty($row['verified'])) ? '<span class="verified-small"><img src="'.$this->url.'/'.$CONF['theme_url'].'/images/icons/verified.png" title="'.$LNG['verified_profile'].'"></span>' : '').'
										</div>
										<div class="message-time">
											'.realName(null, $row['first_name'], $row['last_name']).''.((!empty($row['location']) && $row['private'] == 0) ? ' ('.$row['location'].')' : '&nbsp;').'
										</div>
									</div>
								</div>
							</div>
						</div>';
			$last = $row['id'];
		}
		if($loadmore) {
				$output .= '<div class="load_more" id="more_messages"><a onclick="loadSubs('.$last.', '.$type.', '.$this->profile_data['idu'].')" id="load-more">'.$LNG['view_more_messages'].'</a></div>';
		}
		return $output;
	}

	function countFriends($id, $status) {
		if(isset($status)) {
			if($status == 1) {
				$status = 'AND `status` = 1';
			} else {
				$status = 'AND `status` = 0';
			}
		} else {
			$status = '';
		}
		$query = $this->db->query(sprintf("SELECT (SELECT COUNT(*) as number FROM `friendships` WHERE `user1` = '%s' %s) as `user1`, (SELECT COUNT(*) as number FROM `friendships` WHERE `user2` = '%s' %s) as `user2`", $this->db->real_escape_string($id), $status, $this->db->real_escape_string($id), $status));

		$result = $query->fetch_assoc();

		return ($result['user1']+$result['user2']);
	}

	function getFriends($id, $start = null) {
		if(is_numeric($start)) {
			if($start == 0) {
				$start = '';
			} else {
				$start = 'AND `friendships`.`id` < \''.$this->db->real_escape_string($start).'\'';
			}
		}
		$limit = 'LIMIT '.($this->s_per_page + 1);

		// Select all the friendships (the queries are separated for performance purposes)
		$user1 = sprintf("SELECT * FROM `friendships`, `users` WHERE `user1` = '%s' AND `user2` = `users`.`idu` AND `friendships`.`status` = '1' $start ORDER BY `id` DESC %s;", $this->db->real_escape_string($id), $limit);
		$user2 = sprintf("SELECT * FROM `friendships`, `users` WHERE `user2` = '%s' AND `user1` = `users`.`idu` AND `friendships`.`status` = '1' $start ORDER BY `id` DESC %s;", $this->db->real_escape_string($id), $limit);

		$result1 = $this->db->query($user1);
		$result2 = $this->db->query($user2);

		$array = [];
		while($row = $result1->fetch_assoc()) {
			$array[$row['id']] = $row;
		}
		while($row = $result2->fetch_assoc()) {
			$array[$row['id']] = $row;
		}

		// Sort the array by the unique ID of the friendship entry
		krsort($array);

		// Remove all the unnecessary array elements
		$array = array_slice($array, 0, $this->s_per_page + 1);

		return $array;
	}

	function getActions($id, $likes = null, $comments = null, $shares = null, $update = null) {
		global $LNG;

		if($update) {
			$stats = $this->getCachedStats($id, 0);
			$shares = $stats['shares'];
			$likes = $stats['likes'];
			$comments = $stats['comments'];
		}

		// Verify the Like state
		$verify = $this->verifyLike($id);

		if($verify) {
			$state = $LNG['dislike'];
			$y = 2;
		} else {
			$state = $LNG['like'];
			$y = 1;
		}

		// If the current user is not empty
		if(empty($this->id)) {
			$actions = '<a href="'.$this->url.'/" rel="loadpage" title="'.$LNG['login_to_lcs'].'">'.$LNG['like'].'</a> - <a href="'.$this->url.'/" rel="loadpage" title="'.$LNG['login_to_lcs'].'">'.$LNG['comment'].'</a> - <a href="'.$this->url.'/" rel="loadpage" title="'.$LNG['login_to_lcs'].'">'.$LNG['share'].'</a>';
		} else {
			$actions = '<a onclick="doLike('.$id.', 0)" id="doLike'.$id.'">'.$state.'</a> - <a onclick="focus_form('.$id.')">'.$LNG['comment'].'</a> - <a onclick="share('.$id.')">'.$LNG['share'].'</a>';
		}

		if($shares > 0) {
			$actions .= '<a onclick="sharesModal('.$id.', 0)" title="'.$LNG['view_who_shared'].'" id="as'.$id.'"><div class="actions_btn shares_btn"> '.$shares.'</div></a>';
		}
		if($comments > 0) {
			$actions .= '<div class="actions_btn comments_btn" id="ac'.$id.'"> '.$comments.'</div>';
		}
		if($likes > 0) {
			$actions .= '<a onclick="likesModal('.$id.', 0)" title="'.$LNG['view_who_liked'].'" id="al'.$id.'"><div class="actions_btn like_btn"> '.$likes.'</div></a>';
		}

		$actions .= '<div class="actions_btn loader" id="action-loader'.$id.'"></div>';

		return $actions;
	}

	function verifyLike($id, $type = null) {
		if($type == 1) {
			$result = $this->db->query(sprintf("SELECT * FROM `likes` WHERE `post` = '%s' AND `by` = '%s' AND `type` = 1", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
		} else {
			$result = $this->db->query(sprintf("SELECT * FROM `likes` WHERE `post` = '%s' AND `by` = '%s' AND `type` = 0", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
		}
		return ($result->num_rows) ? 1 : 0;
	}

	function getBlocked($id, $type = null, $extra = null) {
		// Type 0: Output the button state
		// Type 1: Block/Unblock a user
		// Type 2: Returns 1 if blocked
		// Extra: Returns output for the profile [...] menu

		$profile = $this->profileData(null, $id);

		// If the user is not a confirmed one
		if($profile['suspended'] == 2) {
			return false;
		}

		// If the username does not exist, return nothing
		if(empty($profile)) {
			return false;
		} else {
			// Verify if there is any block issued for this username
			if($type == 2) {
				$checkBlocked = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE ((`uid` = '%s' AND `by` = '%s') OR (`uid` = '%s' AND `by` = '%s'))", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id), $this->db->real_escape_string($this->id), $this->db->real_escape_string($id)));
			} else {
				$checkBlocked = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE `uid` = '%s' AND `by` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			}

			// If the Message/Comment exists
			$state = $checkBlocked->num_rows;

			if($type == 2) {
				return $state;
			}

			// If type 1: Add/Remove
			if($type) {
				// If there is a block issued, remove the block
				if($state) {
					// Remove the block
					$this->db->query(sprintf("DELETE FROM `blocked` WHERE `uid` = '%s' AND `by` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

					// Block variable
					$y = 0;
				} else {
					// Insert the block
					$this->db->query(sprintf("INSERT INTO `blocked` (`uid`, `by`) VALUES ('%s', '%s')", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

					// Delete any friendships
					$this->db->query(sprintf("DELETE FROM `friendships` WHERE (`user1` = '%s' AND `user2` = '%s') OR (`user1` = '%s' AND `user2` = '%s')", $this->db->real_escape_string($this->id), $this->db->real_escape_string($id), $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

					$this->db->query(sprintf("DELETE FROM `notifications` WHERE ((`from` = '%s' AND `to` = '%s') OR (`from` = '%s' AND `to` = '%s')) AND `type` IN (4,5)", $this->db->real_escape_string($this->id), $this->db->real_escape_string($id), $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

					// Unblock variable
					$y = 1;
				}
				return $this->outputBlocked($id, $profile, $y, $extra);
			} else {
				return $this->outputBlocked($id, $profile, $state, $extra);
			}
		}
	}

	function outputBlocked($id, $profile, $state, $extra) {
		global $LNG;

		if($extra) {
			$x = '<div class="message-menu-row" onclick="doBlock('.$id.', 1)" id="block'.$id.'">'.($state ? $LNG['unblock'] : $LNG['block']).'</div>';
		} else {
			$x = '<span class="unblock-link"><a onclick="doBlock('.$id.', 1)">'.($state ? $LNG['unblock'] : $LNG['block']).'</a></span>';
		}

		return $x;
	}

	function commentEdit($message, $id) {
		global $LNG;

		if(strlen($message) > $this->message_length || empty($message)) {
			return false;
		} else {
			// Update the message
			$result = $this->db->query(sprintf("UPDATE `comments` SET `message` = '%s', `time` = `time` WHERE `id` = '%s' AND `uid` = '%s'", $this->db->real_escape_string(htmlspecialchars($message)), $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

			$select = $this->db->query(sprintf("SELECT `uid`, `message` FROM `comments` WHERE `id` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$result = $select->fetch_assoc();

			// Verify if is the message owner (prevents obtaining message's content from private posts for example)
			if($result['uid'] == $this->id) {
				return trim(nl2br($this->parseMessage($result['message'])));
			}
		}
	}

	function postEdit($message, $id) {
		global $LNG;

		list($error, $content) = $this->validateMessage($message, null, null, null, 0, null, null);

		if($error) {
			return false;
		} else {
			// Escape thge message and trim it to remove any extra white spaces or consecutive new lines
			$message = $this->db->real_escape_string(htmlspecialchars(trim(nl2clean($message))));

			// Match the hashtags
			preg_match_all('/(#\w+)/u', str_replace(array('\r', '\n'), ' ', $message), $matchedHashtags);

			// For each hashtag, strip the '#' tag and add a comma after it
            $hashtag = '';
			if(!empty($matchedHashtags[0])) {
				foreach($matchedHashtags[0] as $match) {
					$hashtag .= str_replace('#', '', $match).',';
				}
			}

			// Update the message
			$result = $this->db->query(sprintf("UPDATE `messages` SET `message` = '%s', `tag` = '%s', `time` = `time` WHERE `id` = '%s' AND `uid` = '%s'", $message, $hashtag, $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

			$select = $this->db->query(sprintf("SELECT `uid`, `message` FROM `messages` WHERE `id` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));
			$result = $select->fetch_assoc();

			// Verify if is the message owner (prevents obtaining message's content from private posts for example)
			if($result['uid'] == $this->id) {
				return trim(nl2br($this->parseMessage($result['message'])));
			}
		}
	}

	function validateMessage($message, $image, $type, $value, $privacy, $group, $page) {
		// If message is longer than admitted
		if(strlen($message) > $this->message_length) {
			$error = array('message_too_long', $this->message_length);
		}
		// Define the switch variable
		$x = 0;
		if(isset($image['name'][0]) && $image['name'][0]) {
			// Set the variable value to 1 if at least one image name exists
			$x = 1;
		}
		if($x == 1) {
			// If the user selects more images than allowed
			if(count($image['name']) > $this->max_images) {
				$error = array('too_many_images', count($image['name']), $this->max_images);
			} else {
				// Define the array which holds the value names
				$value = array();
				$tmp_value = array();
				foreach($image['error'] as $key => $err) {
					$allowedExt = explode(',', $this->image_format);
					$ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
					if(!empty($image['size'][$key]) && $image['size'][$key] > $this->max_size) {
						$error = array('file_too_big', fsize($this->max_size), $image['name'][$key]); // Error Code #004
						break;
					} elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
						$error = array('format_not_exist', $this->image_format, $image['name'][$key]); // Error Code #005
						break;
					} else {
						if(isset($image['name'][$key]) && $image['name'][$key] !== '' && $image['size'][$key] > 0) {
							$tmp_name = $image['tmp_name'][$key];
							$name = pathinfo($image['name'][$key], PATHINFO_FILENAME);
							$fullname = $image['name'][$key];
							$size = $image['size'][$key];
							$ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
							$finalName = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$this->db->real_escape_string($ext);

							// Define the type for picture
							$type = 'picture';

							// Store the values into arrays
							$tmp_value[] = $tmp_name;
							$value[] = $finalName;

							// Fix the image orientation if possible
							imageOrientation($tmp_name);
						}
					}
				}
				if(empty($error)) {
					foreach($value as $key => $finalName) {
						move_uploaded_file($tmp_value[$key], '../uploads/media/'.$finalName);
					}
				}
				// Implode the values
				$value = implode(',', $value);
			}
		} else {
			// Allowed types of evenets
			$allowedType = array('map',  'video', 'music', 'plugin');

			// If the user doesn't select any event, at all.
			if(empty($type)) {
				// Empty the type & value
				$type = '';
				$value = '';
			} else {
				// Verify if the event exist
				if(in_array($type, $allowedType)) {
					// Empty the plugin type
					if($type == 'plugin') {
						$type = '';
					}
				} else {
					$error = array('event_not_exist'); // Error Code #002
				}
			}
		}

		// If the group is set, force the post to be public
		if($group) {
			// Verify if the user has access to the group
			$privacy = 1;
			$page = '0';
		} elseif($page) {
			// Verify if the user has access to the group
			$privacy = 1;
			$group = '0';
		}

		// Allowed types of privacy
		$allowedPrivacy = array(0, 1, 2);

		if(!in_array($privacy, $allowedPrivacy)) {
			$error = array('privacy_no_exist'); // Error Code #003
		}

		# #001 - The message is empty
		# #002 - The event does not exist
		# #003 - The privacy value is not valid
		# #004 - The selected file is too big
		# #005 - The selected file's format is invalid

		if(isset($error)) {
			// Return an error
			return array('1', $error);
		} else {
		    $po = '';
			foreach($this->plugins as $plugin) {
				if(array_intersect(array("1"), str_split($plugin['type']))) {
					$poerr = plugin($plugin['name'], array('message' => $message, 'type' => $type, 'value' => $value), 0);
					// If the plugin output is not an array (error)
					if(!is_array($poerr)) {
						// Store the result
						$po .= $poerr;

                        // If the plugin returns a type:value output, stop executing the rest of the plugins
                        if(!empty($po)) {
                            break;
                        }
					} else {
						// Return the plugin error message
						return array('2', $poerr[0]);
					}
				}
			}

			// If there's any plugin output
			if($po) {
				$value = $po;
			}

			// Escape thge message and trim it to remove any extra white spaces or consecutive new lines
			$message = $this->db->real_escape_string(htmlspecialchars(trim(nl2clean($message))));

			// Match the hashtags
			preg_match_all('/(#\w+)/u', str_replace(array('\r', '\n'), ' ', $message), $matchedHashtags);

			// For each hashtag, strip the '#' tag and add a comma after it
            $hashtag = '';
			if(!empty($matchedHashtags[0])) {
				foreach($matchedHashtags[0] as $match) {
					$hashtag .= str_replace('#', '', $match).',';
				}
			}

			$value = substr($value, 0, 2048);

			// Create the query
			// Add the insert message
			$query = sprintf("INSERT INTO `messages` (`uid`, `message`, `tag`, `type`, `value`, `group`, `page`, `time`, `public`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '%s', CURRENT_TIMESTAMP, '%s')", $this->db->real_escape_string($this->id), $message, $hashtag, $this->db->real_escape_string($type), $this->db->real_escape_string(strip_tags($value)), $this->db->real_escape_string($group), $this->db->real_escape_string($page), $this->db->real_escape_string($privacy));
			return array('0', $query);
		}
	}

	function postMessage($message, $image, $type, $value, $privacy, $group, $page) {
		global $LNG;
		list($error, $content) = $this->validateMessage($message, $image, $type, $value, $privacy, $group, $page);
		if($error) {
			// Randomize a number for the js function
			$rand = rand();
			if($error == 2) {
				$switch = $content;
			} else {
				$switch = (isset($content[2])) ? sprintf($LNG["{$content[0]}"], $content[2], $content[1]) : sprintf($LNG["{$content[0]}"], $content[1]);
			}
			return $this->db->real_escape_string('<div class="message-container" id="notification'.$rand.'"><div class="message-content"><div class="message-inner">'.$switch.'<div class="delete_btn" title="'.$LNG['close'].'" onclick="deleteNotification(0, \''.$rand.'\')"></div></div></div></div>');
		} else {
			// Add the insert message
			$stmt = $this->db->prepare("$content");

			// Execute the statement
			$stmt->execute();

			if($stmt->affected_rows) {
				preg_match_all('/(^|[^a-z0-9_\/])@([a-z0-9_]+)/i', $message, $matchedMentions);

				$lastMessage = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `uid` = '%s' ORDER BY `id` DESC", $this->id));
				$mid = $lastMessage->fetch_assoc();

				$i = 0;
				$prevent = array();
				foreach($matchedMentions[2] as $mention) {
					if($i == 30) break;
					// Validate the user
					if(!in_array($mention, $prevent)) {
						$getUser = $this->db->query(sprintf("SELECT `idu`, `username`, `first_name`, `last_name`, `email`, `email_mention` FROM `users` WHERE `username` = '%s'", $this->db->real_escape_string($mention)));
						$mUser = $getUser->fetch_assoc();

						$getBlocked = $this->db->query(sprintf("SELECT * FROM `blocked` WHERE `by` = '%s' AND `uid` = '%s'", $this->db->real_escape_string($mUser['idu']), $this->db->real_escape_string($this->id)));

						// If the user exists, and didn't blocked the message owner, and is not the message owner
						if($getUser->num_rows > 0 && $getBlocked->num_rows == 0 && $mUser['username'] != $this->username) {
							// If the user has email on mention enabled and the email is enabled in the Admin Panel
							if($mUser['email_mention'] == 1 && $this->email_mention == 1) {
								sendMail($mUser['email'], sprintf($LNG['ttl_mention_email'], $mUser['username']), sprintf($LNG['mention_email'], realName($mUser['username'], $mUser['first_name'], $mUser['last_name']), permalink($this->url.'/index.php?a=profile&u='.$this->username), $this->username, permalink($this->url.'/index.php?a=post&m='.$mid['id']), $this->title, permalink($this->url.'/index.php?a=settings&b=notifications')), $this->site_email);
							}

							$this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', 0, 11, 0)", $this->id, $mUser['idu'], $mid['id']));
						}
					}
					$prevent[] = $mention;
					$i++;
				}
			}

			// Close the statement
			$stmt->close();

			// If the message was added, return 1
			return $this->db->real_escape_string($this->getLastMessage());
		}
	}

	function postShared($id) {
		global $LNG;
		// Check if the post ID exists and it's public
		$query = $this->db->query(sprintf("SELECT * FROM `messages`,`users` WHERE `messages`.`id` = '%s' AND `messages`.`public` IN (1, 2) AND `messages`.`uid` = `users`.`idu`", $this->db->real_escape_string($id)));
		$result = $query->fetch_assoc();

		// If a message is found
		if($result) {
			// Insert the shared message

			// Check if the message was already shared [avoid mirror in mirror effect]
			if($result['type'] == 'shared') {
				$insert = $this->db->query(sprintf("INSERT INTO `messages` (`uid`, `message`, `type`, `value`, `tag`, `time`, `public`) VALUES ('%s', '', 'shared', '%s', '', CURRENT_TIMESTAMP, '1');", $this->db->real_escape_string($this->id), $this->db->real_escape_string($result['value'])));
				$mid = $result['value'];
			} else {
				$insert = $this->db->query(sprintf("INSERT INTO `messages` (`uid`, `message`, `type`, `value`, `tag`, `time`, `public`) VALUES ('%s', '', 'shared', '%s', '', CURRENT_TIMESTAMP, '1');", $this->db->real_escape_string($this->id), $this->db->real_escape_string($result['id'])));
				$mid = $result['id'];
			}

			// Do the INSERT notification
			$selectShared = $this->db->query(sprintf("SELECT * FROM `messages`,`users` WHERE `messages`.`uid` = '%s' AND `messages`.`type` = 'shared' AND `messages`.`uid` = `users`.`idu` ORDER BY `messages`.`id` DESC", $this->db->real_escape_string($this->id)));
			$resultShared = $selectShared->fetch_assoc();

			$getOriginal = $this->db->query(sprintf("SELECT * FROM `messages`, `users` WHERE `messages`.`id` = '%s' AND `messages`.`uid` = `users`.`idu`", $result['value']));
			$shared = $getOriginal->fetch_assoc();

			// If the message is not from a page
			if(empty($shared['page'])) {
				if(isset($shared['uid']) == false || $this->id !== $shared['uid']) {
					$insertNotification = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `to`, `parent`, `child`, `type`, `read`) VALUES ('%s', '%s', '%s', '%s', '3', '0')", $this->db->real_escape_string($this->id), $result['uid'], ($result['type'] == 'shared' ? $result['value'] : $result['id']), $resultShared['id']));
				}
			}

			// Update the shares counter
			$this->db->query(sprintf("UPDATE `messages` SET `shares` = `shares` + 1, `time` = `time` WHERE `id` = '%s'", $this->db->real_escape_string($mid)));

			return sprintf($LNG['shared_success'], permalink($this->url.'/index.php?a=feed'));
		} else {
			return $LNG['no_shared'];
		}
	}

	function pageActivity($type, $page) {
		// Type 1: Update the notifications with the new value
		// Type 0: Check for new notifications
		$curr_count = $page['likes'];
		if($type) {
			// Update the notifications with the latest value
			$this->db->query(sprintf("UPDATE `notifications` SET `child` = '%s' WHERE `parent` = '%s' AND `from` = '%s' AND `type` = 10", $curr_count, $page['id'], $this->id));
		} else {
			// Get the old value
			$getOld = $this->db->query(sprintf("SELECT `child` as `count` FROM `notifications` WHERE `parent` = '%s' AND `from` = '%s' AND `type` = 10", $page['id'], $this->id));
			$old = $getOld->fetch_assoc();

			// If there's no notification entry found, add one
			if($old === null) {
				$insertNotification = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `parent`, `child`, `type`) VALUES ('%s', '%s', '%s', '10');", $this->id, $page['id'], $curr_count));
			}

			// If there are new activities
			if($curr_count > $old['count']) {
				return ($curr_count - $old['count']);
			}
		}
	}

	function groupActivity($type, $message = null, $group = null, $user_id = null) {
		// Type 0: Get the latest viewed message from the group
		// Type 1: Add or update the notifications with the last viewed message
		// Type 2: Get the new messages count since last group visit
		// Type 3: Select the last posted message from the group

		if($type == 3) {
			// Select the last group message
			$query = $this->db->query(sprintf("SELECT `id` FROM `messages` WHERE `group` = '%s' ORDER BY `id` DESC LIMIT 1", $group));
			$result = $query->fetch_assoc();

			// Insert into notifications
			$this->db->query(sprintf("INSERT INTO `notifications` (`from`, `parent`, `child`, `type`) VALUES ('%s', '%s', '%s', '7')", $this->db->real_escape_string(($user_id ? $user_id : $this->id)), $group, $result['id']));
		} elseif($type == 2) {
			// Check if there is a last message in the notifications
			$last = $this->groupActivity(0, 0, $group);

			// If there is any last message
			$query = $this->db->query(sprintf("SELECT count(`id`) FROM `messages` WHERE `group` = '%s' AND `id` > '%s'", $group, $last));

			$result = $query->fetch_array();
			return $result[0];
		} elseif($type == 1) {
			// Check if there is a last message in the notifications
			$last = $this->groupActivity(0);

			// If the user has no `notifications`.`type` 7 (pre 2.0.9 release) add one
			if($last === NULL) {
				$this->groupActivity(3, $message, $this->group_data['id']);
				return false;
			}

			// Check if the last message is higher than the current loaded one (prevents adding lower values when Loading Page on groups)
			if($message > $last) {
				// Update the last record with the new one
				$query = $this->db->query(sprintf("UPDATE `notifications` SET `child` = '%s' WHERE `from` = '%s' AND `parent` = '%s' AND `type` = '7'", $message, $this->id, $this->group_data['id']));
			}
		} else {
			$query = $this->db->query(sprintf("SELECT `child` FROM `notifications` WHERE `from` = '%s' AND `parent` = '%s' AND `type` = '7'", $this->id, ($group ? $group : $this->group_data['id'])));

			$result = $query->fetch_assoc();
			return $result['child'] ?? null;
		}
	}

	function groupMember($type, $user) {
		// Type 2: Block the member
		// Type 1: Accept the user
		// Type 0: Decline the user

		// Get the user group status
		$currQuery = $this->db->query(sprintf("SELECT * FROM `groups_users` WHERE `user` = '%s' AND `group` = '%s'", $this->db->real_escape_string($user), $this->group_data['id']));

		$old = $currQuery->fetch_assoc();

		if($type == 1) {
			// Approve the user
			$this->db->query(sprintf("UPDATE `groups_users` SET `status` = '1', `permissions` = 0 WHERE `user` = '%s' AND `group` = '%s' AND `permissions` != '2' AND `user` != '%s'", $this->db->real_escape_string($user), $this->group_data['id'], $this->id));
			$this->groupActivity(3, null, $this->group_data['id'], $user);
		} elseif($type == 2) {
			// Block the member and remove any permissions
			$this->db->query(sprintf("UPDATE `groups_users` SET `status` = '2', `permissions` = 0 WHERE `user` = '%s' AND `group` = '%s' AND `permissions` != '2' AND `user` != '%s'", $this->db->real_escape_string($user), $this->group_data['id'], $this->id));
		} elseif($type == 3) {
			// Unblock the member and remove any permissions
			$this->db->query(sprintf("UPDATE `groups_users` SET `status` = '1', `permissions` = 0 WHERE `user` = '%s' AND `group` = '%s' AND `permissions` != '2' AND `user` != '%s'", $this->db->real_escape_string($user), $this->group_data['id'], $this->id));
		} elseif($type == 4) {
			// Promote a group member to Admin status
			$this->db->query(sprintf("UPDATE `groups_users` SET `status` = '1', `permissions` = 1, `time` = `time` WHERE `user` = '%s' AND `group` = '%s' AND `permissions` != '2' AND `user` != '%s'", $this->db->real_escape_string($user), $this->group_data['id'], $this->id));
		} elseif($type == 5) {
			// Remove the Admin status of a member
			$this->db->query(sprintf("UPDATE `groups_users` SET `permissions` = 0, `time` = `time` WHERE `user` = '%s' AND `group` = '%s' AND `permissions` != '2' AND `user` != '%s'", $this->db->real_escape_string($user), $this->group_data['id'], $this->id));
		} else {
			// Delete a group member
			$stmt = $this->db->prepare("DELETE FROM `groups_users` WHERE `user` = ? AND `group` = ? AND `permissions` != '2' AND `user` != ?");

			$stmt->bind_param('sss', $user, $this->group_data['id'], $this->id);
			$stmt->execute();
			$affected = $stmt->affected_rows;
			$stmt->close();

			if($affected) {
				// Delete the message images posted in the group
				$this->deleteMessagesImages($user, $this->group_data['id']);

				// Get the messages id of that user
				$mids = $this->getMessagesIds($user, $this->group_data['id']);

				// If the user had any content in the group
				if($mids) {
					$sids = $this->getMessagesIds(null, null, null, $mids);

					// If there are any messages shared
					if($sids) {
						$this->deleteShared($sids);
					}

					// Delete the shared messages by other users
					$this->db->query(sprintf("DELETE FROM `messages` WHERE `type` = 'shared' AND `value` IN (%s)", $mids));

					// Delete all the comments made to the messages
					$this->db->query(sprintf("DELETE FROM `comments` WHERE `mid` IN (%s)", $mids));

					// Delete all the likes from messages
					$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` IN (%s) AND `type` = 0", $mids));

					// Remove all the reports of the message
					$this->db->query(sprintf("DELETE FROM `reports` WHERE `post` IN (%s)", $mids));

					// Remove the notifications of the message
					$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` IN (%s)", $mids));
				}

				// Delete all the messages posted in the group
				$this->db->query(sprintf("DELETE FROM `messages` WHERE `uid` = '%s' AND `group` = '%s'", $this->db->real_escape_string($user), $this->group_data['id']));

				// Delete the `last message` notification
				$this->db->query(sprintf("DELETE FROM `notifications` WHERE `type` = '7' AND `from` = '%s' AND `parent` = '%s'", $this->db->real_escape_string($user), $this->group_data['id']));
			}
		}

		// Get the user group status
		$newQuery = $this->db->query(sprintf("SELECT * FROM `groups_users` WHERE `user` = '%s' AND `group` = '%s'", $this->db->real_escape_string($user), $this->group_data['id']));

		$new = $newQuery->fetch_assoc();

		// If the user was approved from the Requests page
		if($type == 1 && $old['status'] == 0 && $new['status'] == 1) {
			$add = 1;
		}
		// If the user was unblocked from the Blocked page
		if($type == 3 && $old['status'] == 2 && $new['status'] == 1) {
			$add = 1;
		}
		// If the user was blocked from Members page
		if($type == 2 && $old['status'] == 1 && $new['status'] == 2) {
			$remove = 1;
		}
		// If the user was removed from the Members page
		if($type == 0 && $old['status'] == 1 && isset($new['status']) && $new['status'] === NULL) {
			$remove = 1;
		}

		if(isset($add) && $add) {
			$this->db->query(sprintf("UPDATE `groups` SET `members` = (`members` + 1), `time` = `time` WHERE `id` = '%s'", $this->group_data['id']));
		} elseif(isset($remove) && $remove) {
			$this->db->query(sprintf("UPDATE `groups` SET `members` = (`members` - 1), `time` = `time` WHERE `id` = '%s'", $this->group_data['id']));
		}
	}

	function createPage($values, $type = null) {
		if(isset($values['token_id']) && $values['token_id'] == $_SESSION['token_id']) {
            // Type 1: Edit the page
            global $LNG;
            $values['page_name'] = ($type ? $this->page_data['name'] : mb_strtolower($values['page_name']));
            $values['page_title'] = htmlspecialchars($values['page_title']);
            $values['page_desc'] = htmlspecialchars(trim(nl2clean($values['page_desc'])));
            $values['page_phone'] = isset($values['page_phone']) ? substr(htmlspecialchars($values['page_phone']), 0, 64) : '';
            $values['page_address'] = isset($values['page_address']) ? substr(htmlspecialchars($values['page_address']), 0, 128) : '';
            $values['page_website'] = isset($values['page_website']) ? substr($values['page_website'], 0, 128) : '';

            $avatar = $_FILES['page_avatar'] ?? null;
            $cover = $_FILES['page_cover'] ?? null;

            if(!empty($avatar['name'])) {
                foreach($avatar['error'] as $key => $err) {
                    $allowedExt = explode(',', $this->image_format);
                    $ext = pathinfo($avatar['name'][$key], PATHINFO_EXTENSION);
                    if(!empty($avatar['size'][$key]) && $avatar['size'][$key] > $this->max_size) {
                        $error = sprintf($LNG['file_too_big'], $avatar['name'][$key], fsize($this->max_size));
                    } elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
                        $error = sprintf($LNG['format_not_exist'], $avatar['name'][$key], $this->image_format);
                    } else {
                        if(isset($avatar['name'][$key]) && $avatar['name'][$key] !== '' && $avatar['size'][$key] > 0) {
                            $rand = mt_rand();
                            $tmp_name_avatar = $avatar['tmp_name'][$key];
                            $name = pathinfo($avatar['name'][$key], PATHINFO_FILENAME);
                            $fullname = $avatar['name'][$key];
                            $size = $avatar['size'][$key];
                            $ext = pathinfo($avatar['name'][$key], PATHINFO_EXTENSION);
                            $avatar = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$this->db->real_escape_string($ext);

                            // Fix the image orientation if possible
                            imageOrientation($tmp_name_avatar);

                            // Prevents uploading files before validation is taking place
                            $move_avatar = 1;

                            // If the avatar is not the default one, and the user edits the page
                            if($type) {
                                deleteImages(array($this->page_data['image']), 1);
                            }
                            $values['page_image'] = $avatar;
                        } else {
                            if($type) {
                                $values['page_image'] = $this->page_data['image'];
                            } else {
                                $values['page_image'] = 'default.png';
                            }
                        }
                    }
                }
            } else {
                if($type) {
                    $values['page_image'] = $this->page_data['image'];
                } else {
                    $values['page_image'] = 'default.png';
                }
            }

            if(!empty($cover['name'])) {
                foreach($cover['error'] as $key => $err) {
                    $allowedExt = explode(',', $this->image_format);
                    $ext = pathinfo($cover['name'][$key], PATHINFO_EXTENSION);
                    if(!empty($cover['size'][$key]) && $cover['size'][$key] > $this->max_size) {
                        $error = sprintf($LNG['file_too_big'], $cover['name'][$key], fsize($this->max_size));
                    } elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
                        $error = sprintf($LNG['format_not_exist'], $cover['name'][$key], $this->image_format);
                    } else {
                        if(isset($cover['name'][$key]) && $cover['name'][$key] !== '' && $cover['size'][$key] > 0) {
                            $rand = mt_rand();
                            $tmp_name = $cover['tmp_name'][$key];
                            $name = pathinfo($cover['name'][$key], PATHINFO_FILENAME);
                            $fullname = $cover['name'][$key];
                            $size = $cover['size'][$key];
                            $ext = pathinfo($cover['name'][$key], PATHINFO_EXTENSION);
                            $cover = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$this->db->real_escape_string($ext);

                            // Fix the image orientation if possible
                            imageOrientation($tmp_name);

                            // Prevents uploading files before validation is taking place
                            $move_cover = 1;

                            // If the cover is not the default one, and the user edits the page
                            if($type) {
                                deleteImages(array($this->page_data['cover']), 0);
                            }
                            $values['page_cover'] = $cover;
                        } else {
                            if($type) {
                                $values['page_cover'] = $this->page_data['cover'];
                            } else {
                                $values['page_cover'] = 'default.png';
                            }
                        }
                    }
                }
            } else {
                if($type) {
                    $values['page_cover'] = $this->page_data['cover'];
                } else {
                    $values['page_cover'] = 'default.png';
                }
            }

            if($type == 0) {
                $max_pages = $this->pages_limit;
                $mpq = $this->db->query(sprintf("SELECT COUNT(*) as `count` FROM `pages` WHERE `by` = '%s'", $this->db->real_escape_string($this->id)));
                $mpr = $mpq->fetch_assoc();
                if($mpr['count'] > $max_pages) {
                    $error = sprintf($LNG['page_maximum'], $max_pages);
                }
            }

            if(!ctype_alnum($values['page_name'])) {
                $error = $LNG['page_name_consist'];
            }

            $desc_length = 10000;
            if(strlen($values['page_desc']) > $desc_length) {
                $error = sprintf($LNG['page_desc_less'], $desc_length);
            }

            $name_length = 64;
            if(strlen($values['page_name']) > $name_length) {
                $error = sprintf($LNG['page_name_less'], $name_length);
            }

            if(!preg_match('/^[+\d-]+$/i', $values['page_phone']) && !empty($values['page_phone'])) {
                $error = sprintf($LNG['invalid_phone']);
            }

            $title_length = 64;
            if(strlen($values['page_title']) > $title_length) {
                $error = sprintf($LNG['page_title_less'], $title_length);
            }

            if(!in_array($values['page_category'], array(1,2,3,4,5,6))) {
                $error = sprintf($LNG['select_category']);
            }

            if((!filter_var($values['page_website'], FILTER_VALIDATE_URL) && !empty($values['page_website'])) || (substr($values['page_website'], 0, 7) != 'http://' && substr($values['page_website'], 0, 8) != 'https://' && !empty($values['page_website']))) {
                $error = sprintf($LNG['valid_url']);
            }

            if(!$type) {
                // Check if the page name exists
                $query = $this->db->query(sprintf("SELECT `name` FROM `pages` WHERE `name` = '%s'", $this->db->real_escape_string($values['page_name'])));

                if($query->num_rows > 0 || in_array($values['page_name'], array('likes', 'edit', 'about', 'delete', 'search', 'messages'))) {
                    $error = $LNG['page_name_taken'];
                }
            }

            if(empty($values['page_name']) || empty($values['page_title']) || empty($values['page_desc'])) {
                $error = $LNG['all_fields'];
            }

            if(isset($error)) {
                return array(1, $error);
            }

            if(isset($move_avatar)) {
                move_uploaded_file($tmp_name_avatar, 'uploads/avatars/'.$avatar);
            }
            if(isset($move_cover)) {
                move_uploaded_file($tmp_name, 'uploads/covers/'.$cover);
            }

            if($type) {
                // Prepare the statement
                $stmt = $this->db->prepare("UPDATE `pages` SET `title` = ?, `description` = ?, `address` = ?, `website` = ?, `phone` = ?, `category` = ?, `image` = ?, `cover` = ?, `verified` = ?, `time` = `time` WHERE `name` = ?");
                $stmt->bind_param('ssssssssss', $values['page_title'], $values['page_desc'], $values['page_address'], $values['page_website'], $values['page_phone'], $values['page_category'], $values['page_image'], $values['page_cover'], $values['page_verified'], $values['page_name']);

                // Execute the statement
                $stmt->execute();

                // Save the affected rows
                $affected = $stmt->affected_rows;

                $stmt->close();

                return array(0, ($affected ? 1 : 0));
            } else {
                // Create the Page
                $createPage = $this->db->query(sprintf("INSERT INTO `pages` (`name`, `by`, `title`, `category`, `description`, `image`, `cover`) VALUES ('%s', '%s', '%s','%s', '%s', '%s', '%s');",
                    $this->db->real_escape_string($values['page_name']),
                    $this->db->real_escape_string($this->id),
                    $this->db->real_escape_string($values['page_title']),
                    $this->db->real_escape_string($values['page_category']),
                    $this->db->real_escape_string($values['page_desc']),
                    $this->db->real_escape_string($values['page_image']),
                    $this->db->real_escape_string($values['page_cover'])));

                $selectPage = $this->db->query(sprintf("SELECT `id` FROM `pages` WHERE `name` = '%s'", $values['page_name']));
                $page = $selectPage->fetch_assoc();

                // Insert the notification
                $insertNotification = $this->db->query(sprintf("INSERT INTO `notifications` (`from`, `parent`, `child`, `type`) VALUES ('%s', '%s', '0', '10');", $this->id, $page['id']));

                return array(0, $values['page_name']);
            }
		}
	}

	function deletePage($id, $type = null, $from = null) {
		// From: The request is being made from the Admin Panel
		if($_GET['token_id'] == $_SESSION['token_id']) {
			global $LNG;
			// Verify the page owner
			$query = $this->db->query(sprintf("SELECT * FROM `pages` WHERE `id` = '%s' AND `by` = '%s'", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

			$result = $query->fetch_assoc();

			if($query->num_rows) {
				// Delete the Page
				$query = $this->db->query(sprintf("DELETE FROM `pages` WHERE `id` = '%s'", $this->db->real_escape_string($id)));

				// Delete all the images from messages
				$this->deleteMessagesImages(null, null, $id);

				// Delete the page image
				deleteImages(array($result['image']), 1);

				// Delete the page cover
				deleteImages(array($result['cover']), 0);

				$mids = $this->getMessagesIds(null, null, $id, null, 1);

				// If the page had any content
				if($mids) {
					$sids = $this->getMessagesIds(null, null, null, $mids);

					// If there are any messages shared
					if($sids) {
						$this->deleteShared($sids);
					}

					// Delete all the shared messages of the page
					$this->db->query(sprintf("DELETE FROM `messages` WHERE `type` = 'shared' AND `value` IN (%s)", $mids));

					// Delete all the comments made to the messages of the page
					$this->db->query(sprintf("DELETE FROM `comments` WHERE `mid` IN (%s)", $mids));

					// Delete all the likes from messages of the page
					$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` IN (%s)", $mids));

					// Remove all the reports of the message of the page
					$this->db->query(sprintf("DELETE FROM `reports` WHERE `post` IN (%s)", $mids));

					// Remove the notifications of the message of the page
					$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` IN (%s)", $mids));
				}

				// Delete all the messages posted on the page
				$query = $this->db->query(sprintf("DELETE FROM `messages` WHERE `page` = '%s'", $this->db->real_escape_string($id)));

				// Delete the notifications (both page invite and the last amount of likes viewed)
				$query = $this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` = '%s' AND `type` = '9' OR `type` = '10'", $this->db->real_escape_string($id)));

				// Delete the likes
				$query = $this->db->query(sprintf("DELETE FROM `likes` WHERE `post` = '%s' AND `type` = 2", $this->db->real_escape_string($id)));

				if(!$type) {
					header("Location: ".permalink($this->url."/index.php?a=".($from ? "admin&b=manage_pages" : "page")."&deleted=".$result['name']));
				}
			}
		}
	}

	function createGroup($values, $type = null) {
        if(isset($values['token_id']) && $values['token_id'] == $_SESSION['token_id']) {
            // Type 1: Edit the group
            global $LNG;
            $values['group_name'] = ($type ? $this->group_data['name'] : mb_strtolower($values['group_name']));
            $values['group_title'] = htmlspecialchars($values['group_title']);
            $values['group_desc'] = htmlspecialchars(trim(nl2clean($values['group_desc'])));
            

            $image = $_FILES['group_cover'] ?? null;

            if(!empty($image['name'])) {
                foreach($image['error'] as $key => $err) {
                    $allowedExt = explode(',', $this->image_format);
                    $ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
                    if(!empty($image['size'][$key]) && $image['size'][$key] > $this->max_size) {
                        $error = sprintf($LNG['file_too_big'], $image['name'][$key], fsize($this->max_size));
                    } elseif(!empty($ext) && !in_array(strtolower($ext), $allowedExt)) {
                        $error = sprintf($LNG['format_not_exist'], $image['name'][$key], $this->image_format);
                    } else {
                        if(isset($image['name'][$key]) && $image['name'][$key] !== '' && $image['size'][$key] > 0) {
                            $rand = mt_rand();
                            $tmp_name = $image['tmp_name'][$key];
                            $name = pathinfo($image['name'][$key], PATHINFO_FILENAME);
                            $fullname = $image['name'][$key];
                            $size = $image['size'][$key];
                            $ext = pathinfo($image['name'][$key], PATHINFO_EXTENSION);
                            $cover = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$this->db->real_escape_string($ext);

                            // Fix the image orientation if possible
                            imageOrientation($tmp_name);

                            // Prevents uploading files before validation is taking place
                            $move_cover = 1;

                            // If the cover is not the default one, and the user edits the group
                            if($type) {
                                deleteImages(array($this->group_data['cover']), 0);
                            }
                            $values['group_cover'] = $cover;
                        } else {
                            if($type) {
                                $values['group_cover'] = $this->group_data['cover'];
                            } else {
                                $values['group_cover'] = 'default.png';
                            }
                        }
                    }
                }
            } else {
                if($type) {
                    $values['group_cover'] = $this->group_data['cover'];
                } else {
                    $values['group_cover'] = 'default.png';
                }
            }

            if($type == 0) {
                $max_groups = $this->groups_limit;
                $mgq = $this->db->query(sprintf("SELECT COUNT(*) as `count` FROM `groups_users` WHERE `user` = '%s'", $this->db->real_escape_string($this->id)));
                $mgr = $mgq->fetch_assoc();
                if($mgr['count'] > $max_groups) {
                    $error = sprintf($LNG['group_maximum'], $max_groups);
                }
            }

            if(!ctype_alnum($values['group_name'])) {
                $error = $LNG['group_name_consist'];
            }

            $desc_length = 10000;
            if(strlen($values['group_desc']) > $desc_length) {
                $error = sprintf($LNG['group_desc_less'], $desc_length);
            }
           

            $name_length = 64;
            if(strlen($values['group_name']) > $name_length) {
                $error = sprintf($LNG['group_name_less'], $name_length);
            }

            $title_length = 64;
            if(strlen($values['group_title']) > $title_length) {
                $error = sprintf($LNG['group_title_less'], $title_length);
            }

            if(!$type) {
                // Check if the group name exists
                $query = $this->db->query(sprintf("SELECT `name` FROM `groups` WHERE `name` = '%s'", $this->db->real_escape_string($values['group_name'])));

                if($query->num_rows > 0 || in_array($values['group_name'], array('edit', 'members', 'admins', 'blocked', 'requests', 'about', 'delete', 'search', 'messages'))) {
                    $error = $LNG['group_name_taken'];
                }
            }

            if(empty($values['group_name']) || empty($values['group_title']) || empty($values['group_desc'])) {
                $error = $LNG['all_fields'];
            }

            if(!in_array($values['group_privacy'], array(0, 1, 2))) {
                $values['group_privacy'] = 0;
            }

            if(!in_array($values['group_posts'], array(0, 1))) {
                $values['group_posts'] = 0;
            }

            if(isset($error)) {
                return array(1, $error);
            }

            if(isset($move_cover)) {
                move_uploaded_file($tmp_name, 'uploads/covers/'.$cover);
            }

            if($type) {
                // Prepare the statement
                $stmt = $this->db->prepare("UPDATE `groups` SET `title` = ?, `description` = ?, `cover` = ?, `privacy` = ?, `posts` = ?, `time` = `time` WHERE `name` = ?");
                $stmt->bind_param('ssssss', $values['group_title'], $values['group_desc'], $values['group_cover'], $values['group_privacy'], $values['group_posts'], $values['group_name']);

                // Execute the statement
                $stmt->execute();
                
                // Save the affected rows
                $affected = $stmt->affected_rows;

                $stmt->close();

                return array(0, ($affected ? 1 : 0));
            } else {
                
                $query = $this->db->query(sprintf("SELECT `id` FROM `groups` WHERE `id` = '%s'", $insertgroupid));

                $result = $query->fetch_assoc();
                
                for($insertgroupid; $insertgroupid< 1000;$insertgroupid++ ){
                    $inviteCode = "";
                    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
                    for ($p = 0; $p < $length; $p++) {
                        $inviteCode .= $characters[mt_rand(0, strlen($characters)-1)];
                    }
                    $this->db->query(sprintf("INSERT INTO `groups`(`invitecode`)  value('%s') WHERE `id` = '%s'",$invitecode, $insertgroupid));
                }
          
                $length = 10;
				$inviteCode = "";
				$characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
				for ($p = 0; $p < $length; $p++) {
				$inviteCode .= $characters[mt_rand(10, strlen($characters))];
				}  
                
                // Create the Group
                $createGroup = $this->db->query(sprintf("INSERT INTO `groups` (`name`, `title`, `description`, `cover`, `privacy`, `posts`, `members`,`invitecode`) VALUES ('%s', '%s', '%s', '%s', '%s', '%s', '1','%s');", $this->db->real_escape_string($values['group_name']), $this->db->real_escape_string($values['group_title']), $this->db->real_escape_string($values['group_desc']), $this->db->real_escape_string($values['group_cover']), $this->db->real_escape_string($values['group_privacy']), $this->db->real_escape_string($values['group_posts']),$inviteCode));

                // Get the Group's ID
                $getGroup = $this->db->query(sprintf("SELECT `id` FROM `groups` WHERE `name` = '%s'", $this->db->real_escape_string($values['group_name'])));
                $fetchGroup = $getGroup->fetch_assoc();

                // Create the Admin of the Group
                $addAdmin = $this->db->query(sprintf("INSERT INTO `groups_users` (`group`, `user`, `status`, `permissions`) VALUES ('%s', '%s', '1', '2')", $this->db->real_escape_string($fetchGroup['id']), $this->db->real_escape_string($this->id)));

                $this->groupActivity(3, null, $fetchGroup['id']);

                return array(0, $values['group_name']);
            }
		}
	}

	function deleteGroup($id, $type = null, $from = null) {
		// From: The request is being made from the Admin Panel
		if($_GET['token_id'] == $_SESSION['token_id']) {
			global $LNG;
			// Verify the group owner
			$query = $this->db->query(sprintf("SELECT * FROM `groups`, `groups_users` WHERE `group` = '%s' AND `user` = '%s' AND `permissions` = '2' AND `groups_users`.`group` = `groups`.`id`", $this->db->real_escape_string($id), $this->db->real_escape_string($this->id)));

			$result = $query->fetch_assoc();

			if($query->num_rows) {
				// Delete the Group
				$query = $this->db->query(sprintf("DELETE FROM `groups` WHERE `id` = '%s'", $this->db->real_escape_string($id)));

				// Delete all the group members
				$query = $this->db->query(sprintf("DELETE FROM `groups_users` WHERE `group` = '%s'", $this->db->real_escape_string($id)));

				// Delete all the images from messages
				$this->deleteMessagesImages(null, $id);

				// Delete the group's cover
				deleteImages(array($result['cover']), 0);

				$mids = $this->getMessagesIds(null, null, $id);

				// If the group had any content
				if($mids) {
					$sids = $this->getMessagesIds(null, null, null, $mids);

					// If there are any messages shared
					if($sids) {
						$this->deleteShared($sids);
					}

					// Delete all the shared messages of the group
					$this->db->query(sprintf("DELETE FROM `messages` WHERE `type` = 'shared' AND `value` IN (%s)", $mids));

					// Delete all the comments made to the messages of the group
					$this->db->query(sprintf("DELETE FROM `comments` WHERE `mid` IN (%s)", $mids));

					// Delete all the likes from messages of the group
					$this->db->query(sprintf("DELETE FROM `likes` WHERE `post` IN (%s)", $mids));

					// Remove all the reports of the message of the group
					$this->db->query(sprintf("DELETE FROM `reports` WHERE `post` IN (%s)", $mids));

					// Remove the notifications of the message of the group
					$this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` IN (%s)", $mids));
				}

				// Delete all the messages posted in the group
				$query = $this->db->query(sprintf("DELETE FROM `messages` WHERE `group` = '%s'", $this->db->real_escape_string($id)));

				// Delete the notifications (both group invite and the last message viewed)
				$query = $this->db->query(sprintf("DELETE FROM `notifications` WHERE `parent` = '%s' AND `type` = '6' OR `type` = '7'", $this->db->real_escape_string($id)));

				if(!$type) {
					header("Location: ".permalink($this->url."/index.php?a=".($from ? "admin&b=manage_groups" : "group")."&deleted=".$result['name']));
				}
			}
		}
	}
}
function nl2clean($text) {
	// Replace two or more new lines with two new rows [blank space between them]
	return preg_replace("/(\r?\n){2,}/", "\n\n", $text);
}
function sendMail($to, $subject, $message, $from) {
	// Load up the site settings
	global $settings;

	// If the SMTP emails option is enabled in the Admin Panel
	if($settings['smtp_email']) {
		//Create a new PHPMailer instance
        $mail = new \PHPMailer\PHPMailer\PHPMailer();

		//Tell PHPMailer to use SMTP
		$mail->isSMTP();
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$mail->SMTPDebug = 0;
		//Set the CharSet encoding
		$mail->CharSet = 'UTF-8';
		//Ask for HTML-friendly debug output
		$mail->Debugoutput = 'html';
		//Set the hostname of the mail server
		$mail->Host = $settings['smtp_host'];
		//Set the SMTP port number - likely to be 25, 465 or 587
		$mail->Port = $settings['smtp_port'];
		//Whether to use SMTP authentication
		$mail->SMTPAuth = $settings['smtp_auth'] ? true : false;
		//Username to use for SMTP authentication
		$mail->Username = $settings['smtp_username'];
		//Password to use for SMTP authentication
		$mail->Password = $settings['smtp_password'];
		//Set who the message is to be sent from
		$mail->setFrom($from, $settings['title']);
		//Set an alternative reply-to address
		$mail->addReplyTo($from, $settings['title']);
		if(!empty($settings['smtp_secure'])) {
			$mail->SMTPSecure = $settings['smtp_secure'];
		} else {
			$mail->SMTPSecure = false;
		}
		//Set who the message is to be sent to
		if(is_array($to)) {
			foreach($to as $address) {
				$mail->addBCC($address);
			}
		} else {
			$mail->addAddress($to);
		}
		//Set the subject line
		$mail->Subject = $subject;
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$mail->msgHTML($message);

		//send the message, check for errors
		if(!$mail->send()) {
			// Return the error in the Browser's console
			#echo $mail->ErrorInfo;
		}
	} else {
		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=utf-8' . PHP_EOL;
		$headers .= 'From: '.$settings['title'].' <'.$from.'>' . PHP_EOL .
			'Reply-To: '.$settings['title'].' <'.$from.'>' . PHP_EOL .
			'X-Mailer: PHP/' . phpversion();
		if(is_array($to)) {
			foreach($to as $address) {
				@mail($address, $subject, $message, $headers);
			}
		} else {
			@mail($to, $subject, $message, $headers);
		}
	}
}
function strip_tags_array($value) {
	return strip_tags($value);
}
function admin_stats($db, $type, $values = null, $category = null, $extra = null) {
	$days = array();
	$days[0] = date('Y-m-d', strtotime('+1 days'));
	$days[1] = date('Y-m-d');
	$days[2] = date('Y-m-d', strtotime('-1 days'));
	$days[3] = date('Y-m-d', strtotime('-2 days'));
	$days[4] = date('Y-m-d', strtotime('-3 days'));
	$days[5] = date('Y-m-d', strtotime('-4 days'));
	$days[6] = date('Y-m-d', strtotime('-5 days'));
	$days[7] = date('Y-m-d', strtotime('-6 days'));
	$hours = ' 00:00:00';
	if($type == 2) {
		$query = "SELECT
		(SELECT count(id) FROM `reports`) as total_reports,
		(SELECT count(id) FROM `reports` WHERE `state` = 0) as pending_reports,
		(SELECT count(id) FROM `reports` WHERE `state` = 1) as safe_reports,
		(SELECT count(id) FROM `reports` WHERE `state` = 2) as deleted_reports";
	} elseif($type == 1) {
		$query = sprintf("SELECT
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_today,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_yesterday,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_two_days,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_three_days,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_four_days,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_five_days,
		(SELECT count(idu) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users_six_days,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_today,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_yesterday,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_two_days,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_three_days,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_four_days,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_five_days,
		(SELECT COUNT(id) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') AS pages_six_days,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_today,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_yesterday,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_two_days,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_three_days,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_four_days,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_five_days,
		(SELECT COUNT(id) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') AS groups_six_days,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_today,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_yesterday,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_two_days,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_three_days,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_four_days,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_five_days,
		(SELECT COUNT(id) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') AS messages_six_days,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_today,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_yesterday,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_two_days,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_three_days,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_four_days,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_five_days,
		(SELECT COUNT(id) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') AS comments_six_days,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_today,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_yesterday,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_two_days,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_three_days,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_four_days,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_five_days,
		(SELECT COUNT(id) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') AS shared_six_days,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_today,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_yesterday,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_two_days,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_three_days,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_four_days,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_five_days,
		(SELECT count(id) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes_six_days,
		(SELECT count(idu) FROM `users` WHERE `online` > '%s'-'%s') AS online_users",
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		$days[1].$hours, $days[0].$hours, $days[2].$hours, $days[1].$hours, $days[3].$hours, $days[2].$hours, $days[4].$hours, $days[3].$hours, $days[5].$hours, $days[4].$hours, $days[6].$hours, $days[5].$hours, $days[7].$hours, $days[6].$hours,
		time(), $values['conline']
		);
	} else {
	    $queries = '';
		if($extra == 2) {
			$start	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'])).$hours;
			$end	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$_GET['day'].' +1days')).$hours;

			$queries .= sprintf("(SELECT count(`idu`) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as users,", $start, $end);
			$queries .= sprintf("(SELECT count(`id`) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') as messages,", $start, $end);
			$queries .= sprintf("(SELECT COUNT(`id`) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') as comments,", $start, $end);
			$queries .= sprintf("(SELECT count(`id`) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as likes,", $start, $end);
			$queries .= sprintf("(SELECT count(`id`) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') as shares,", $start, $end);
			$queries .= sprintf("(SELECT COUNT(`id`) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') as pages,", $start, $end);
			$queries .= sprintf("(SELECT COUNT(`id`) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') as groups,", $start, $end);
			$queries .= sprintf("(SELECT COUNT(`id`) FROM `reports` WHERE `time` >= '%s' AND `time` < '%s') as reports,", $start, $end);
		}

		foreach($values as $value) {
			if($extra == 3) {
				$start	= date('Y-m-d', strtotime($value.'-01-01')).$hours;
				$end	= date('Y-m-d', strtotime($value.'-01-01 +1years')).$hours;
			} elseif($extra == 1) {
				$start	= date('Y-m-d', strtotime($_GET['year'].'-'.$value.'-01')).$hours;
				$end	= date('Y-m-d', strtotime($_GET['year'].'-'.$value.'-'.cal_days_in_month(CAL_GREGORIAN, $value, $_GET['year']).' +1days')).$hours;
			} else {
				$start	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$value)).$hours;
				$end	= date('Y-m-d', strtotime($_GET['year'].'-'.$_GET['month'].'-'.$value.' +1days')).$hours;
			}

			if($category == 'users') {
				$queries .= sprintf("(SELECT count(`idu`) FROM `users` WHERE `date` >= '%s' AND `date` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'messages') {
				$queries .= sprintf("(SELECT count(`id`) FROM `messages` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'comments') {
				$queries .= sprintf("(SELECT COUNT(`id`) FROM `comments` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'likes') {
				$queries .= sprintf("(SELECT count(`id`) FROM `likes` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'shares') {
				$queries .= sprintf("(SELECT count(`id`) FROM `messages` WHERE `type` = 'shared' AND `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'pages') {
				$queries .= sprintf("(SELECT COUNT(`id`) FROM `pages` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'groups') {
				$queries .= sprintf("(SELECT COUNT(`id`) FROM `groups` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			} elseif($category == 'reports') {
				$queries .= sprintf("(SELECT COUNT(`id`) FROM `reports` WHERE `time` >= '%s' AND `time` < '%s') as value_%s,", $start, $end, $value);
			}
		}
		$query = substr("SELECT ".$queries, 0, -1);
	}
	$result = $db->query($query);
	while($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}
	$stats = array();
	foreach($rows[0] as $value) {
		$stats[] = $value;
	}
	return $stats;
}
function percentage($current, $old) {
	// Prevent dividing by zero
	if($old != 0) {
		$result = number_format((($current - $old) / $old * 100), 0);
	} else {
		$result = 0;
	}
	if($result < 0) {
		return '<span class="negative">'.$result.'%</span>';
	} elseif($result > 0) {
		return '<span class="positive">+'.$result.'%</span>';
	} else {
		return '<span class="neutral">'.$result.'%</span>';
	}
}
function smiles() {
	// Define smiles
	$smiles = array(
		':)'	=> '🙂',
		':('	=> '😞',
		';)'	=> '😉',
		'xD'	=> '😈',
		'x('	=> '😒',
		'=('	=> '😭',
		':*'	=> '😘',
		':D'	=> '😁',
		':x'	=> '😍',
		'(:|'	=> '😴',
		'B)'	=> '😎',
		':P'	=> '😛',
		':\\'	=> '😕',
		':o'	=> '😲',
		'&lt;3'	=> '💖',
		'(y)'	=> '👍',
	);
	return $smiles;
}
function fsize($bytes) { #Determine the size of the file, and print a human readable value
   if ($bytes < 1024) return $bytes.' B';
   elseif ($bytes < 1048576) return round($bytes / 1024, 2).' KiB';
   elseif ($bytes < 1073741824) return round($bytes / 1048576, 2).' MiB';
   elseif ($bytes < 1099511627776) return round($bytes / 1073741824, 2).' GiB';
   else return round($bytes / 1099511627776, 2).' TiB';
}
function base64Image($string, $name) {
	$explode = explode(',', $string, 2);
    $image = imagecreatefromstring(base64_decode($explode[1]));
	if(!$image) {
		return false;
    }

	// Store the image info
	$path = __DIR__ .'/../uploads/media/'.$name;
    imagepng($image, $path);
    $info = getimagesize($path);
	$filesize = filesize($path);
	// Delete the temporary image
	unlink($path);

    if($info[0] > 0 && $info[1] > 0 && $info['mime'] == 'image/png') {
		// Return the image data
		return array('size' => $filesize, 'data' => base64_decode($explode[1]));
    }
    return false;
}
function audioContainer($type, $sound) {
	global $CONF;
	if($sound) {
		$output = '<audio id="soundNew'.$type.'"><source src="'.$CONF['url'].'/'.$CONF['theme_url'].'/sounds/sound'.$type.'.ogg" type="audio/ogg"><source src="'.$CONF['url'].'/'.$CONF['theme_url'].'/sounds/sound'.$type.'.mp3" type="audio/mpeg"><source src="'.$CONF['url'].'/'.$CONF['theme_url'].'/sounds/sound'.$type.'.wav" type="audio/wav"></audio>';
	} else {
		$output = '<audio id="soundNew'.$type.'"></audio>';
	}
	return $output;
}
function realName($username, $first = null, $last = null, $fullname = null) {
	if($fullname) {
		if($first && $last) {
			return $first.' '.$last;
		} else {
			return $username;
		}
	}
	if($first && $last) {
		return $first.' '.$last;
	} elseif($first) {
		return $first;
	} elseif($last) {
		return $last;
	} elseif($username) { // If username is not set, return empty (example: the real-name under the subscriptions)
		return $username;
	}
}
function showUsers($users, $url) {
    $x = '';
	foreach($users as $user) {
		$x .= '<div class="welcome-user"><a href="'.permalink($url.'/index.php?a=profile&u='.$user['username']).'" rel="loadpage"><img src="'.permalink($url.'/image.php?t=a&w=112&h=112&src='.$user['image']).'"></a></div>';
	}
	return $x;
}
function parseCallback($matches) {
	// If match www. at the beginning of the string, add http before
	if(substr($matches[1], 0, 4) == 'www.') {
		$url = 'http://'.$matches[1];
	} else {
		$url = $matches[1];
	}
	return '<a href="'.$url.'" target="_blank" rel="nofollow">'.$matches[1].'</a>';
}
function generateTimezoneForm($current) {
	global $LNG;
	$rows = '<option value="" '.($current == '' ? ' selected' : '').'>'.$LNG['default'].'</option>';
	foreach(timezone_identifiers_list() as $value) {
		$rows .= '<option value="'.htmlspecialchars($value).'" '.($current == $value ? ' selected' : '').'>'.$value.'</option>';
    }

	return $rows;
}
function generateDateForm($type, $current) {
	global $LNG;
	$rows = '';
	if($type == 0) {
		$rows .= '<option value="">'.$LNG['year'].'</option>';
		for($i = date('Y'); $i >= (date('Y') - 100); $i--) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
	} elseif($type == 1) {
		$rows .= '<option value="">'.$LNG['month'].'</option>';
		for($i = 1; $i <= 12; $i++) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$LNG["month_$i"].'</option>';
		}
	} elseif($type == 2) {
		$rows .= '<option value="">'.$LNG['day'].'</option>';
		for($i = 1; $i <= 31; $i++) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
	}
	return $rows;
}
function generateStatsForm($type, $current, $min = null) {
	global $LNG;
	$rows = '';
	if($type == 0) {
		if(empty($min)) {
			$min = date('Y');
		}
		$rows .= '<option value="">'.$LNG['year'].'</option>';
		for($i = date('Y'); $i >= $min; $i--) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
	} elseif($type == 1) {
		$rows .= '<option value="">'.$LNG['month'].'</option>';
		for($i = 1; $i <= 12; $i++) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$LNG["month_$i"].'</option>';
		}
	} elseif($type == 2) {
		$rows .= '<option value="">'.$LNG['day'].'</option>';
		for($i = 1; $i <= cal_days_in_month(CAL_GREGORIAN, $_GET['month'], $_GET['year']); $i++) {
			if($i == $current) {
				$selected = ' selected="selected"';
			} else {
				$selected = '';
			}
			$rows .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
		}
	}
	return $rows;
}
function generateAd($content) {
	global $LNG;
	if(empty($content)) {
		return false;
	}
	$ad = '<div class="sidebar-container widget-ad"><div class="sidebar-content"><div class="sidebar-header">'.$LNG['sponsored'].'</div>'.$content.'</div></div>';
	return $ad;
}
function sortDateDesc($a, $b) {
	// Convert the array value into a UNIX timestamp
	strtotime($a['time']);
	strtotime($b['time']);

	return strcmp($a['time'], $b['time']);
}
function sortDateAsc($a, $b) {
	// Convert the array value into a UNIX timestamp
	strtotime($a['time']);
	strtotime($b['time']);

	if($a['time'] == $b['time']) {
		return 0;
	}
	return ($a['time'] > $b['time']) ? -1 : 1;
}
function sortOnlineUsers($a, $b) {
	// Convert the array value into a UNIX timestamp
	strtotime($a['online']);
	strtotime($b['online']);

	if ($a['online'] == $b['online']) {
		return 0;
	}
	return ($a['online'] > $b['online']) ? -1 : 1;
}

function getLanguage($url, $ln = null, $type = null, $plugin = null) {
	global $settings;
	// Type 1: Output the available languages

	// Define the languages folder
	if(isset($plugin) && !empty($plugin)) {
		$lang_folder = __DIR__ .'/../plugins/'.$plugin.'/languages/';
	} else {
		$lang_folder = __DIR__ .'/../languages/';
	}

	// Open the languages folder
	if($handle = opendir($lang_folder)) {
		// Read the files (this is the correct way of reading the folder)
		while(false !== ($entry = readdir($handle))) {
			// Excluse the . and .. paths and select only .php files
			if($entry != '.' && $entry != '..' && substr($entry, -4, 4) == '.php') {
				$name = pathinfo($entry);
				$languages[] = $name['filename'];
			}
		}
		closedir($handle);
	}
	// Sort the languages by name
	sort($languages);
	if($type == 1) {
		// Add to array the available languages
        $available = '';
		foreach($languages as $lang) {
			// The path to be parsed
			$path = pathinfo($lang);

			// Add the filename into $available array
			$available .= '<span><a href="'.$url.'/index.php?lang='.$path['filename'].'">'.ucfirst(mb_strtolower($path['filename'])).'</a></span>';
		}
		return $available;
	} else {
		// If get is set, set the cookie and stuff
		$lang = $settings['language']; // Default Language

        if($plugin == null) {
            if(isset($_GET['lang'])) {
                if(in_array($_GET['lang'], $languages)) {
                    $lang = $_GET['lang'];
                    setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
                } else {
                    setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
                }
            } elseif(isset($_COOKIE['lang'])) {
                if(in_array($_COOKIE['lang'], $languages)) {
                    $lang = $_COOKIE['lang'];
                }
            } else {
                setcookie('lang', $lang, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH); // Expire in one month
            }

            // If the language file doens't exist, fall back to an existent language file
            if(!file_exists($lang_folder.$lang.'.php')) {
                $lang = $languages[0];
            }
        } else {
            if(isset($_COOKIE['lang']) && in_array($_COOKIE['lang'], $languages)) {
                $lang = $_COOKIE['lang'];
            }

            // If the language file doens't exist, fall back to an existent language file
            if(!file_exists($lang_folder.$lang.'.php')) {
                $lang = $languages[0];
            }
        }

		return $lang_folder.$lang.'.php';
	}
}
function saniscape($value) {
	return htmlspecialchars(addslashes($value), ENT_QUOTES, 'UTF-8');
}
function generateToken($type = null) {
	if($type) {
		return '<input type="hidden" name="token_id" value="'.$_SESSION['token_id'].'">';
	} else {
		if(!isset($_SESSION['token_id'])) {
			$token_id = md5(substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10));
			$_SESSION['token_id'] = $token_id;
			return $_SESSION['token_id'];
		}
		return $_SESSION['token_id'];
	}
}
function getUserIp() {
	if($_SERVER['REMOTE_ADDR']) {
		return $_SERVER['REMOTE_ADDR'];
	} else {
		return false;
	}
}
function adminMenuCounts($db, $type) {
	// Type 0: Return the reports number

	if($type == 0) {
		$query = $db->query('SELECT COUNT(`id`) as `count` FROM `reports` WHERE `state` = 0');
	}
	$result = $query->fetch_assoc();

	return $result['count'];
}
function imageOrientation($filename) {
	if(function_exists('exif_read_data')) {
		// Read the image exif data
        $exif = @exif_read_data($filename);

		// Store the image exif orientation data
		$orientation = (isset($exif['Orientation']) ? $exif['Orientation'] : null);

		// Check whether the image has an orientation, and if the orientation is 3, 6, 8
		if(!empty($orientation) && in_array($orientation, array(3, 6, 8))) {
			$image = imagecreatefromjpeg($filename);
			if($orientation == 3) {
				$image = imagerotate($image, 180, 0);
			} elseif($orientation == 6) {
				$image = imagerotate($image, -90, 0);
			} elseif($orientation == 8) {
				$image = imagerotate($image, 90, 0);
			}

			// Save the new rotated image
			imagejpeg($image, $filename, 90);
		}
	}
}
function deletePhotos($type, $value) {
	// If the message type is picture
	if($type == 'picture') {
		// Explode the images string value
		$images = explode(',', $value);

		// Remove any empty array elements
		$images = array_filter($images);

		// Delete each image
		foreach($images as $image) {
			unlink(__DIR__ .'/../uploads/media/'.$image);
		}
	}
}
function deleteImages($image, $type) {
	// Type 0: Delete covers
	// Type 1: Delete avatars

	$path = ($type ? 'avatars' : 'covers');
	foreach($image as $name) {
		if($name !== 'default.png') {
			unlink(__DIR__ .'/../uploads/'.$path.'/'.$name);
		}
	}
}
function fetch($url) {
	if(function_exists('curl_exec')) {
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/46.0.2490.80 Safari/537.36');
        curl_setopt($ch, CURLOPT_ENCODING, "");
        $response = curl_exec($ch);
	}
	if(empty($response)) {
		$response = file_get_contents($url);
	}
	return $response;
}
function isAjax() {
	/*
	 * Check if the request is dynamic (ajax)
	 *
	 * @return bolean
	 */

	if(	isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'
		// || isset($_GET['live'])
		) {
		return true;
	} else {
		return false;
	}
}
/**
 * @return string
 */
function generateSalt($length = 10) {
	$characters = '0123456789-=+@#.?&gt,&lt;!$%&amp;*abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomstr = '';
    for ($i = 0; $i < $length; $i++) {
      $randomstr .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomstr;
}
function permalink($url) {
	// url: the URL to be rewritten
	global $settings;

	if($settings['permalinks']) {
		$path['profile'] 			= 'index.php?a=profile';
		$path['group'] 				= 'index.php?a=group';
		$path['page'] 				= 'index.php?a=page';
		$path['feed']			 	= 'index.php?a=feed';
		$path['notifications'] 		= 'index.php?a=notifications';
		$path['settings'] 			= 'index.php?a=settings';
		$path['messages']			= 'index.php?a=messages';
		$path['post']				= 'index.php?a=post';
		$path['search']				= 'index.php?a=search';
		$path['info']				= 'index.php?a=info';
		$path['welcome']			= 'index.php?a=welcome';
		$path['recover']			= 'index.php?a=recover';
		$path['image']				= 'image.php';

		if(strpos($url, $path['profile'])) {
			$url = str_replace(array($path['profile'], '&u=', '&r=', '&filter='), array('profile', '/', '/', '/filter/'), $url);
		} elseif(strpos($url, $path['group'])) {
			$url = str_replace(array($path['group'], '&name=', '&r=', '&search=', '&friends=', '&deleted='), array('group', '/', '/', '/search/', '/friends/', '/deleted/'), $url);
		} elseif(strpos($url, $path['page'])) {
			$url = str_replace(array($path['page'], '&name=', '&r=', '&friends=', '&deleted='), array('page', '/', '/', '/friends/', '/deleted/'), $url);
		} elseif(strpos($url, $path['feed'])) {
			$url = str_replace(array($path['feed'], '&filter=', '&logout', '&token_id='), array('feed', '/filter/', '/logout', ''), $url);
		} elseif(strpos($url, $path['notifications'])) {
			$url = str_replace(array($path['notifications'], '&filter='), array('notifications', '/filter/'), $url);
		} elseif(strpos($url, $path['settings'])) {
			$url = str_replace(array($path['settings'], '&b='), array('settings', '/'), $url);
		} elseif(strpos($url, $path['messages'])) {
			$url = str_replace(array($path['messages'], '&u=', '&id='), array('messages', '/', '/'), $url);
		} elseif(strpos($url, $path['post'])) {
			$url = str_replace(array($path['post'], '&m='), array('post', '/'), $url);
		} elseif(strpos($url, $path['search'])) {
			$url = str_replace(array($path['search'], '&q=', '&tag=', '&pages=', '&groups=', '&filter=', '&age='), array('search', '/', '/tag/', '/pages/', '/groups/', '/filter/', '/age/'), $url);
		} elseif(strpos($url, $path['info'])) {
			$url = str_replace(array($path['info'], '&b='), array('info', '/'), $url);
		} elseif(strpos($url, $path['welcome'])) {
			$url = str_replace(array($path['welcome']), array('welcome'), $url);
		} elseif(strpos($url, $path['recover'])) {
			$url = str_replace(array($path['recover'], '&r=1'), array('recover', '/do/'), $url);
		} elseif(strpos($url, $path['image'])) {
			$url = str_replace(array($path['image'], '?t=', '&w=', '&h=', '&src='), array('image', '/', '/', '/', '/'), $url);
		}
	}

	return $url;
}
function plugin($event, $values = null, $type = null) {
	global $CONF;
   /*
	*
	* @param string 			$events		Function name to be loaded and executed
	* @param string(array)		$values		The values to be passed to the function
	* @param string				$type		The type of the request, 1 to append _output, 2 to append _sidebar
	*
	*/
	if($type == 1) {
		$suffix = '_output';
	} elseif($type == 2) {
		$suffix = '_sidebar';
	} elseif($type == 3) {
		$suffix = '_event';
	} elseif($type == 4) {
		$suffix = '_delete';
	} else {
		$suffix = '';
	}
	$fn = ($type) ? $event.$suffix : $event;

	// Define the path of the plugin
	$path = __DIR__ .'/../'.$CONF['plugin_path'].'/'.$event.'/'.$fn.'.php';

	// Check if the file exists and open it
	if(file_exists($path)) {
		require_once($path);
	} else {
		return false;
	}

	$plugin = call_user_func($fn, $values);

	// If there is an output and the $type is for Messages event or Sidebar
	if($plugin && ($type == 1 || $type == 2)) {
		return $plugin;
	}
	// Else if there is an output with no $type
	else {
		$output = $plugin;
	}
	return $output;
}
function loadPlugins($db) {
    $query = $db->query('SELECT * FROM `plugins` ORDER BY `priority` DESC');

    $result = [];
	while($column = $query->fetch_assoc()) {
		$result[] = array('name' => $column['name'], 'type' => $column['type']);
	}
	return $result;
}
function getPluginsSettings($db) {
    $query = $db->query('SELECT * FROM `plugins_settings`');

    $result = [];

    while($column = $query->fetch_assoc()) {
        $result[$column['name']] = $column['value'];
    }
    return $result;
}
?>
