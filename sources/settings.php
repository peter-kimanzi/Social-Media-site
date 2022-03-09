<?php
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;
	
	require_once('./includes/countries.php');
	// Prevent user adding himself to a group
	unset($_POST['user_group'], $_POST['suspended'], $_POST['verified']);

	if(isset($user['username'])) {
		$TMPL_old = $TMPL; $TMPL = array();
		
		$TMPL['url'] = $CONF['url'];
		$TMPL['form_url'] = (empty($_GET['b']) ? permalink($CONF['url'].'/index.php?a=settings') : permalink($CONF['url'].'/index.php?a=settings&b='));
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		
		// Create the class instance
		$updateUserSettings = new updateUserSettings();
		$updateUserSettings->db = $db;
		$updateUserSettings->url = $CONF['url'];
		$updateUserSettings->id = $user['idu'];
		
		if(isset($_GET['b']) && $_GET['b'] == 'security') {
			$skin = new skin('settings/security'); $page = '';
			
			if(!empty($_POST)) {	
				$TMPL['message'] = $updateUserSettings->query_array('users', $_POST);
			}
			
			$userSettings = $updateUserSettings->getSettings();
		} elseif(isset($_GET['b']) && $_GET['b'] == 'delete') {
			$skin = new skin('settings/delete'); $page = '';
			
			if(isset($_POST['current_password'])) {
				// If the password is valid
				if($updateUserSettings->validate_password($_POST['current_password']) && $_POST['token_id'] == $_SESSION['token_id']) {
					$userSettings = $updateUserSettings->getSettings();

					// Delete the profile images
					deleteImages(array($userSettings['image']), 1);
					deleteImages(array($userSettings['cover']), 0);
					
					$manageUsers = new manageUsers();
					$manageUsers->db = $db;
					$manageUsers->deleteUser($user['idu']);
					
					// Redirect the user on the home-page after the account has been deleted
					$logout = new User;
					$logout->db = $db;
					$logout->username = $user['username'];
					$logout->logOut(true);
					header("Location: ".$CONF['url']."/index.php?a=welcome");
				} else {
					$TMPL['message'] = notificationBox('error', $LNG['wrong_current_password']);
				}
			}
		} elseif(isset($_GET['b']) && $_GET['b'] == 'avatar') {
			$skin = new skin('settings/avatar'); $page = '';

            $TMPL['message'] = $_SESSION['error'] ?? ''; $_SESSION['error'] = '';

			$TMPL['image'] = $CONF['url'].'/image.php?src='.$user['image'].'&t=a&w=112&h=112';
			$TMPL['cover'] = $CONF['url'].'/image.php?src='.$user['cover'].'&t=c&w=900&h=200';
			
			$maxsize = $settings['size'];

			if(isset($_FILES['avatarselect']['name'])) {
				foreach ($_FILES['avatarselect']['error'] as $key => $error) {
                    $ext = pathinfo($_FILES['avatarselect']['name'][$key], PATHINFO_EXTENSION);
                    $size = $_FILES['avatarselect']['size'][$key];
                    $extArray = explode(',', $settings['format']);

                    // Get the image size
                    list($width, $height) = getimagesize($_FILES['avatarselect']['tmp_name'][0]);
                    if(in_array(strtolower($ext), $extArray) && $size < $maxsize && $size > 0 && !empty($width) && !empty($height)) {
                        $rand = mt_rand();
                        $tmp_name = $_FILES['avatarselect']['tmp_name'][$key];
                        $name = pathinfo($_FILES['avatarselect']['name'][$key], PATHINFO_FILENAME);
                        $fullname = $_FILES['avatarselect']['name'][$key];
                        $size = $_FILES['avatarselect']['size'][$key];
                        $type = pathinfo($_FILES['avatarselect']['name'][$key], PATHINFO_EXTENSION);
                        $finalName = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$db->real_escape_string($ext);

                        // Fix the image orientation if possible
                        imageOrientation($tmp_name);

                        // Move the file into the uploaded folder
                        move_uploaded_file($tmp_name, 'uploads/avatars/'.$finalName);

                        // Delete the old image
                        deleteImages(array($user['image']), 1);

                        // Send the image name in array format to the function
                        $image = array('image' => $finalName, 'token_id' => $_POST['token_id']);
                        $updateUserSettings->query_array('users', $image);

                        $_SESSION['error'] = notificationBox('success', $LNG['image_saved']);
                    } elseif($_FILES['avatarselect']['name'][$key] == '') {
                        // If there's no file selected
                        $_SESSION['error'] = notificationBox('error', $LNG['no_file']);
                    } elseif($size > $maxsize || $size == 0) {
                        // If the file size is higher than allowed or empty
                        $_SESSION['error'] = notificationBox('error', sprintf($LNG['file_exceeded'], round($maxsize / 1048576, 2)));
                    } else {
                        // If the files does not have a valid format
                        $_SESSION['error'] = notificationBox('error', sprintf($LNG['file_format'], $settings['format']));
                    }
                }
				if(!empty($_SESSION['error'])) {
					header('Location: '.permalink($CONF['url'].'/index.php?a=settings&b=avatar'));
				}
			}
			
			if(isset($_FILES['coverselect']['name'])) {
				foreach($_FILES['coverselect']['error'] as $key => $error) {
                    $ext = pathinfo($_FILES['coverselect']['name'][$key], PATHINFO_EXTENSION);
                    $size = $_FILES['coverselect']['size'][$key];
                    $extArray = explode(',', $settings['format']);

                    // Get the image size
                    list($width, $height) = getimagesize($_FILES['coverselect']['tmp_name'][0]);
                    if(in_array(strtolower($ext), $extArray) && $size < $maxsize && $size > 0 && !empty($width) && !empty($height)) {
                        $rand = mt_rand();
                        $tmp_name = $_FILES['coverselect']['tmp_name'][$key];
                        $name = pathinfo($_FILES['coverselect']['name'][$key], PATHINFO_FILENAME);
                        $fullname = $_FILES['coverselect']['name'][$key];
                        $size = $_FILES['coverselect']['size'][$key];
                        $type = pathinfo($_FILES['coverselect']['name'][$key], PATHINFO_EXTENSION);
                        $finalName = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$db->real_escape_string($ext);

                        // Fix the image orientation if possible
                        imageOrientation($tmp_name);

                        // Move the file into the uploaded folder
                        move_uploaded_file($tmp_name, 'uploads/covers/'.$finalName);

                        // Delete the old image
                        deleteImages(array($user['cover']), 0);

                        // Send the image name in array format to the function
                        $image = array('cover' => $finalName, 'token_id' => $_POST['token_id']);
                        $updateUserSettings->query_array('users', $image);

                        $_SESSION['error'] = notificationBox('success', $LNG['image_saved']);
                    } elseif($_FILES['coverselect']['name'][$key] == '') {
                        // If there's no file selected
                        $_SESSION['error'] = notificationBox('error', $LNG['no_file']);
                    } elseif($size > $maxsize || $size == 0) {
                        // If the file size is higher than allowed or empty
                        $_SESSION['error'] = notificationBox('error', sprintf($LNG['file_exceeded'], round($maxsize / 1048576, 2)));
                    } else {
                        // If the files does not have a valid format
                        $_SESSION['error'] = notificationBox('error', sprintf($LNG['file_format'], $settings['format']));
                    }
                }
				if(!empty($_SESSION['error'])) {
					header('Location: '.permalink($CONF['url'].'/index.php?a=settings&b=avatar'));
				}
			}
			
			if(!empty($TMPL['message'])) {
				$_SESSION['error'] = '';
			}
		} elseif(isset($_GET['b']) && $_GET['b'] == 'notifications') {
			$skin = new skin('settings/notifications'); $page = '';
			
			if(!empty($_POST)) {
				$TMPL['message'] = $updateUserSettings->query_array('users', array_map("strip_tags_array", $_POST));
			}
			
			$userSettings = $updateUserSettings->getSettings();
			
			if($userSettings['notificationl'] == '0') {
				$TMPL['loff'] = 'selected="selected"';
			} else {
				$TMPL['lon'] = 'selected="selected"';
			}
			
			if($userSettings['notificationc'] == '0') {
				$TMPL['coff'] = 'selected="selected"';
			} else {
				$TMPL['con'] = 'selected="selected"';
			}
			
			if($userSettings['notifications'] == '0') {
				$TMPL['soff'] = 'selected="selected"';
			} else {
				$TMPL['son'] = 'selected="selected"';
			}
			
			if($userSettings['notificationd'] == '0') {
				$TMPL['doff'] = 'selected="selected"';
			} else {
				$TMPL['don'] = 'selected="selected"';
			}
			
			if($userSettings['notificationf'] == '0') {
				$TMPL['foff'] = 'selected="selected"';
			} else {
				$TMPL['fon'] = 'selected="selected"';
			}
			
			if($userSettings['notificationm'] == '0') {
				$TMPL['moff'] = 'selected="selected"';
			} else {
				$TMPL['mon'] = 'selected="selected"';
			}
			
			if($userSettings['notificationg'] == '0') {
				$TMPL['goff'] = 'selected="selected"';
			} else {
				$TMPL['gon'] = 'selected="selected"';
			}
			
			if($userSettings['notificationx'] == '0') {
				$TMPL['xoff'] = 'selected="selected"';
			} else {
				$TMPL['xon'] = 'selected="selected"';
			}
			
			if($userSettings['notificationp'] == '0') {
				$TMPL['poff'] = 'selected="selected"';
			} else {
				$TMPL['pon'] = 'selected="selected"';
			}
			
			if($userSettings['sound_new_notification'] == '0') {
				$TMPL['snnoff'] = 'selected="selected"';
			} else {
				$TMPL['snnon'] = 'selected="selected"';
			}
			
			if($userSettings['sound_new_chat'] == '0') {
				$TMPL['sncoff'] = 'selected="selected"';
			} else {
				$TMPL['sncon'] = 'selected="selected"';
			}
			
			if($userSettings['email_comment'] == '0') {
				$TMPL['ecoff'] = 'selected="selected"';
			} else {
				$TMPL['econ'] = 'selected="selected"';
			}
			
			if($userSettings['email_like'] == '0') {
				$TMPL['eloff'] = 'selected="selected"';
			} else {
				$TMPL['elon'] = 'selected="selected"';
			}
			
			if($userSettings['email_new_friend'] == '0') {
				$TMPL['enfoff'] = 'selected="selected"';
			} else {
				$TMPL['enfon'] = 'selected="selected"';
			}
			
			if($userSettings['email_mention'] == '0') {
				$TMPL['emoff'] = 'selected="selected"';
			} else {
				$TMPL['emon'] = 'selected="selected"';
			}
			
			if($userSettings['email_page_invite'] == '0') {
				$TMPL['epioff'] = 'selected="selected"';
			} else {
				$TMPL['epion'] = 'selected="selected"';
			}
			
			if($userSettings['email_group_invite'] == '0') {
				$TMPL['egioff'] = 'selected="selected"';
			} else {
				$TMPL['egion'] = 'selected="selected"';
			}
			
			if(empty($settings['pages'])) {
				$TMPL['empty_pages'] = ' style="display: none;"';
			}
			
			if(empty($settings['groups'])) {
				$TMPL['empty_groups'] = ' style="display: none;"';
			}
		} elseif(isset($_GET['b']) && $_GET['b'] == 'privacy') {
			$skin = new skin('settings/privacy'); $page = '';
			
			if(!empty($_POST)) {
				$TMPL['message'] = $updateUserSettings->query_array('users', array_map("strip_tags_array", $_POST));
			}
			
			$userSettings = $updateUserSettings->getSettings();
			
			if($userSettings['private'] == '1') {
				$TMPL['on'] = 'selected="selected"';
			} elseif($userSettings['private'] == '2') {
				$TMPL['semi'] = 'selected="selected"';
			} else {
				$TMPL['off'] = 'selected="selected"';
			}
			
			if($userSettings['privacy'] == '0') {
				$TMPL['pon'] = 'selected="selected"';
			} elseif($userSettings['privacy'] == '2') {
				$TMPL['psemi'] = 'selected="selected"';
			} else {
				$TMPL['poff'] = 'selected="selected"';
			}
			
			if($userSettings['offline'] == '1') {
				$TMPL['con'] = 'selected="selected"';
			} else {
				$TMPL['coff'] = 'selected="selected"';
			}
		} elseif(isset($_GET['b']) && $_GET['b'] == 'invite') {
			$skin = new skin('settings/invite'); $page = '';
			
			$updateUserSettings->per_page = $settings['perpage'];
			
			$TMPL['invite_users'] = $updateUserSettings->inviteUsers();

		} elseif(isset($_GET['b']) && $_GET['b'] == 'blocked') {
			$skin = new skin('settings/blocked'); $page = '';
			
			$updateUserSettings->per_page = $settings['perpage'];
			
			$TMPL['blocked_users'] = $updateUserSettings->getBlockedUsers();
		} else {
			$skin = new skin('settings/general'); $page = '';

            $TMPL['message'] = $_SESSION['error'] ?? ''; $_SESSION['error'] = '';

			if(!empty($_POST)) {
				$_SESSION['error'] = $updateUserSettings->query_array('users', array_map("strip_tags_array", $_POST));
				header('Location: '.permalink($CONF['url'].'/index.php?a=settings'));
				return;
			}
			
			$userSettings = $updateUserSettings->getSettings();
            if(isset($userSettings['born'])) {
                $date = explode('-', $userSettings['born']);
            } else {
                $date = [0, 0, 0];
            }

			$TMPL['years'] = generateDateForm(0, $date[0]);
			$TMPL['months'] = generateDateForm(1, $date[1]);
			$TMPL['days'] = generateDateForm(2, $date[2]);

            $TMPL['countries'] = countries(1, $userSettings['country']);

            $TMPL['currentFirstName'] = $userSettings['first_name']; $TMPL['currentLastName'] = $userSettings['last_name']; $TMPL['currentEmail'] = $userSettings['email']; $TMPL['currentLocation'] = $userSettings['location']; $TMPL['currentWebsite'] = $userSettings['website']; $TMPL['currentBio'] = $userSettings['bio']; $TMPL['currentFacebook'] = $userSettings['facebook']; $TMPL['currentTwitter'] = $userSettings['twitter']; $TMPL['currentAddress'] = $userSettings['address']; $TMPL['currentWork'] = $userSettings['work']; $TMPL['currentSchool'] = $userSettings['school'];
			
			if($userSettings['gender'] == '0') {
				$TMPL['ngender'] = 'selected="selected"';
			} elseif($userSettings['gender'] == '1') {
				$TMPL['mgender'] = 'selected="selected"';
			} else {
				$TMPL['fgender'] = 'selected="selected"';
			}
			
			if($userSettings['interests'] == '0') {
				$TMPL['ninterests'] = 'selected="selected"';
			} elseif($userSettings['interests'] == '1') {
				$TMPL['minterests'] = 'selected="selected"';
			} else {
				$TMPL['winterests'] = 'selected="selected"';
			}
		}
		$page .= $skin->make();
		$TMPL = $TMPL_old; unset($TMPL_old);
		$TMPL['settings'] = $page;
	} else {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	}
	
	// Start the sidebar menu
	if(isset($_GET['b'])) {
		$TMPL['welcome'] = $LNG["user_ttl_{$_GET['b']}"];
	} else {
		$TMPL['welcome'] = $LNG["user_ttl_general"];
	}
	
	$menu = array(	''					=> array('user_menu_general', 'settings'),
					'&b=avatar'			=> array('user_menu_avatar', 'themes'),
					'&b=notifications'	=> array('user_menu_notifications', 'notification'),
					'&b=privacy'		=> array('user_menu_privacy', 'privacy'),
					'&b=security'		=> array('user_menu_security', 'security'),
				 	'&b=invite'		    => array('user_menu_invite', 'invite'
				 	    ),
					'&b=blocked'		=> array('user_menu_blocked', 'blocked'),
					'&b=delete'			=> array('user_menu_delete', 'delete'));

	$TMPL['menu'] = '';
	foreach($menu as $link => $title) {
		$class = '';
		if(isset($_GET['b']) && $link == '&b='.$_GET['b']) {
			$class = ' sidebar-link-active';
		} elseif(empty($link) && empty($_GET['b'])) {
            $class = ' sidebar-link-active';
        }
		$TMPL['menu'] .= '<div class="sidebar-link'.$class.'"><a href="'.permalink($CONF['url'].'/index.php?a=settings'.$link).'" rel="loadpage"><img src="'.$CONF['url'].'/'.$CONF['theme_url'].'/images/icons/settings/'.$title[1].'.svg">'.$LNG[$title[0]].'</a></div>';
	}

	$TMPL['title'] = $LNG['title_settings'].' - '.$settings['title'];
	
	$skin = new skin('settings/content');
	return $skin->make();
}
?>