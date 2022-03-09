<?php
if(empty($settings['pages'])) {
	header("Location: ".$CONF['url']."/index.php?a=welcome");
}
function PageMain() {
	global $TMPL, $LNG, $CONF, $db, $user, $settings, $plugins;

	if(isset($_GET['name']) && empty($_GET['name']) || !isset($_GET['name']) && !$user['username']) {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	}
	
	// Start displaying the Feed
	$feed = new feed();
	$feed->db = $db;
	$feed->url = $CONF['url'];
	$feed->user = $user;
	$feed->id = $user['idu'] ?? null;
	$feed->username = $user['username'] ?? null;
	$feed->per_page = $settings['perpage'];
	$feed->time = $settings['time'];
	$feed->censor = $settings['censor'];
	$feed->smiles = $settings['smiles'];
	$feed->max_size = $settings['size'];
	$feed->image_format = $settings['format'];
	$feed->c_per_page = $settings['cperpage'];
	$feed->c_start = 0;
	$feed->online_time = $settings['conline'];
	$feed->friendsArray = $feed->getFriends($user['idu'] ?? null);
	$feed->updateStatus($user['offline'] ?? null);
	$feed->pages_limit = $settings['pages_limit'];
	$feed->page_data = $feed->pageData(isset($_GET['name']) ? $_GET['name'] : null);
	$feed->page_stats = $feed->getLikes(null, 2);
	$feed->plugins = $plugins;

	// If the page does not exist
	if(isset($_GET['name']) && !$feed->page_data['id']) {
		header("Location: ".$CONF['url']."/index.php?a=welcome");
	}

	if(isset($_SESSION['is_admin'])) {
		$feed->is_admin = 1;
	}
	
	$TMPL_old = $TMPL; $TMPL = array();
	$TMPL['url'] = $CONF['url'];
	// If the logged in user is the page owner
	if(isset($_GET['name']) && empty($_GET['r']) && $feed->id == $feed->page_data['by'] && empty($_GET['friends'])) {
		$skin = new skin('shared/top'); $top = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		// Load the sidebar plugins
        $TMPL['plugins'] = '';
		foreach($plugins as $plugin) {
			if(array_intersect(array("e"), str_split($plugin['type']))) {
				$data = $user; $data['site_url'] = $CONF['url']; $data['site_title'] = $settings['title']; $data['site_email'] = $CONF['email']; unset($data['password']); unset($data['salted']);
				$TMPL['plugins'] .= plugin($plugin['name'], $data, 3);
			}
		}

		$TMPL['theme_url'] = $CONF['theme_url'];
		$TMPL['private_message'] = $user['privacy'];
		$TMPL['privacy_class'] = (($user['privacy']) ? (($user['privacy'] == 2) ? 'friends' : 'public') : 'private');
        $TMPL['avatar'] = permalink($CONF['url'].'/image.php?t=a&w=48&h=48&src='.$feed->page_data['image']);
		$TMPL['page'] = $feed->page_data['id'];
		$TMPL['style'] = ' style="display: none;"';
		
		// Update the notifications
		$feed->pageActivity(1, $feed->page_data);

		$top = $skin->make();
	} else {
		$top = '';
	}
	
	if(isset($_GET['r']) && $_GET['r'] == 'edit' && $feed->page_data['by'] == $feed->id) {
		$skin = new skin('page/edit'); $rows = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		// The Group Title
		$TMPL['page_title'] = $LNG['edit_page'];
		
		// The Group button
		$TMPL['page_btn'] = $LNG['save_changes'];
		
		// The URL to append for the form
		$TMPL['edit_url'] = permalink($CONF['url'].'/index.php?a=page&name='.$feed->page_data['name'].'&r=edit');
		$TMPL['delete_url'] = permalink($CONF['url'].'/index.php?a=page&name='.$feed->page_data['name'].'&r=delete');
		
		if(!empty($_POST)) {
			$_POST['page_verified'] = $feed->page_data['verified'];
			$message = $feed->createPage($_POST, 1);
			
			$feed->page_data = $feed->pageData($_GET['name']);
			
			// If there's an error during group validation
			if($message[0]) {
				$TMPL['message'] = notificationBox('error', $message[1]);
			} else {
				if($message[1]) {
					$TMPL['message'] = notificationBox('success', $LNG['settings_saved']);
				} else {
					$TMPL['message'] = notificationBox('info', $LNG['nothing_changed']);
				}
			}
		}
		
		// The disabled attribute for inputs
		$TMPL['disabled'] = ' disabled';
		$TMPL['current_name'] = $feed->page_data['name'];
		$TMPL['current_title'] = $feed->page_data['title'];
		$TMPL['current_desc'] = $feed->page_data['description'];
		$TMPL['current_website'] = $feed->page_data['website'];
		$TMPL['current_phone'] = $feed->page_data['phone'];
		$TMPL['current_address'] = $feed->page_data['address'];
	} elseif(isset($_GET['r']) && $_GET['r'] == 'delete' && $feed->page_data['by'] == $feed->id) {
		$skin = new skin('page/delete'); $delete = '';
		$TMPL['token_id'] = $_SESSION['token_id'];
		$TMPL['id'] = $feed->page_data['id'];
		$delete = $skin->make();
	} elseif(isset($_GET['r']) && $_GET['r'] == 'likes' && isset($_GET['name'])) {
		$skin = new skin('page/likes'); $likes = '';
		$TMPL['total'] = $feed->page_stats['total'];
		$TMPL['today'] = $feed->page_stats['today'];
		$TMPL['this_week'] = $feed->page_stats['this_week'];
		$TMPL['this_month'] = $feed->page_stats['this_month'];
		$TMPL['this_year'] = $feed->page_stats['this_year'];
		
		$TMPL['yesterday'] = $feed->page_stats['yesterday'];
		
		if($feed->page_stats['total'] > 0) {
			$TMPL['latest'] = $feed->getLikes(10, 2);
		}
		$TMPL['today_percentage'] = percentage($TMPL['today'], $TMPL['yesterday']);
		$likes = $skin->make();
	} elseif(isset($_GET['name'])) {
		$skin = new skin('shared/rows'); $rows = '';
		
		$feed->s_per_page = $settings['sperpage'];
		if(!empty($_GET['friends']) && !empty($feed->id)) {
			$TMPL['messages'] = $feed->searchFriends($_GET['friends'], 1);
		} else {
			// Get the page feed
			list($timeline, $message) = $feed->getPage(0, $feed->page_data['id']);
			$TMPL['messages'] = $timeline;
		}
	} else {
		$skin = new skin('page/edit'); $rows = '';
		$TMPL['token_input'] = generateToken($_SESSION['token_id']);
		$TMPL['edit_url'] = permalink($CONF['url'].'/index.php?a=page');
		$TMPL['page_title'] = $TMPL['page_btn'] = $LNG['create_page'];
		$TMPL['style'] = ' style="display: none;"';
		$TMPL['start_optional'] = '<!--';
		$TMPL['end_optional'] = '-->';
		if(!empty($_GET['deleted'])) {
			$TMPL['message'] = notificationBox('success', sprintf($LNG['page_deleted'], $_GET['deleted']));
		}
		if(!empty($_GET['delete'])) {
			$feed->deletePage($_GET['delete']);
		}
		if(!empty($_POST)) {
			$message = $feed->createPage($_POST);
			// Display the current inputs
			$TMPL['current_name'] = htmlspecialchars($_POST['page_name']);
			$TMPL['current_title'] = htmlspecialchars($_POST['page_title']);
			$TMPL['current_desc'] = htmlspecialchars($_POST['page_desc']);

			// If there's an error during page validation
			if($message[0]) {
				$TMPL['message'] = notificationBox('error', $message[1]);
			} else {
				header("Location: ".permalink($CONF['url']."/index.php?a=page&name=".$message[1]));
			}
		}
	}

	$TMPL['page_'.(isset($_POST['page_category']) ? $_POST['page_category'] : ($feed->page_data['category'] ?? null))] = ' selected="selected"';

	$rows = $skin->make();
	
	$skin = new skin('page/sidebar'); $sidebar = '';
	if(isset($_GET['name'])) {
		$TMPL['about'] = $feed->sidebarPageInfo($feed->page_data);
		if(!empty($feed->id)) {
			$TMPL['invite'] = $feed->sidebarInput(2);
		}
	} else {
		$TMPL['pages'] = $feed->sidebarPages(1);
	}
	
	$TMPL['ad'] = generateAd($settings['ad3']);
	
	$sidebar = $skin->make();
	
	$TMPL = $TMPL_old; unset($TMPL_old);
	$TMPL['top'] = $top;
	$TMPL['rows'] = $rows;
	$TMPL['sidebar'] = $sidebar;
    if(isset($delete)) {
        $TMPL['delete'] = $delete;
    }
    if(isset($likes)) {
        $TMPL['likes'] = $likes;
    }
    if(isset($_GET['name'])) {
        $TMPL['cover'] = $feed->fetchPage($feed->page_data);
    }

	$TMPL['url'] = $CONF['url'];
	$TMPL['title'] = (isset($_GET['name']) ? $LNG['page'].'	- '.$feed->page_data['title'] : $LNG['title_page']).' - '.$settings['title'];

	$skin = new skin((isset($_GET['name']) ? 'shared/timeline' : 'page/content'));
	return $skin->make();
}
?>