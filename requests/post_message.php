<?php
require_once(__DIR__ .'/../includes/autoload.php');
if(isset($_SESSION['token_id']) == false || $_POST['token_id'] != $_SESSION['token_id']) {
    return false;
}

// If message is not empty
if(!empty($_POST['message']) || !empty($_FILES['images']['size'][0]) || !empty($_POST['value'])) {
	if(isset($user['username'])) {
		$feed = new feed();
		$feed->db = $db;
		$feed->url = $CONF['url'];
		$feed->title = $settings['title'];
		$feed->user = $user;
		$feed->id = $user['idu'];
		$feed->username = $user['username'];
		$feed->per_page = $settings['perpage'];
		$feed->c_per_page = $settings['cperpage'];
		$feed->c_start = 0;
		$feed->censor = $settings['censor'];
		$feed->smiles = $settings['smiles'];
		$feed->max_size = $settings['sizemsg'];
		$feed->image_format = $settings['formatmsg'];
		$feed->message_length = $settings['message'];
		$feed->max_images = $settings['ilimit'];
		$feed->email_mention = $settings['email_mention'];
		$feed->time = $settings['time'];
		
		if($_POST['group']) {
			$feed->group_data = $feed->groupData(null, $_POST['group']);
			if(!$feed->group_data['id']) {
				return false;
			}
			$feed->group_member_data = $feed->groupMemberData($feed->group_data['id']);
			if(!$feed->groupPermission($feed->group_data, $feed->group_member_data, 1)) {
				return false;
			}
		} else {
			$_POST['group'] = 0;
		}
		
		if($_POST['page']) {
			$feed->page_data = $feed->pageData(null, $_POST['page']);
			if($feed->page_data['by'] != $feed->id) {
				return false;
			}
		} else {
			$_POST['page'] = 0;
		}
		
		$feed->plugins = loadPlugins($db);
		
		// If the value of a type is empty unset it (prevent empty events)
		if(!empty($_POST['type']) && empty($_POST['value'])) {
			unset($_POST['type']);
		}
		
		// Set the $x to output the value via JS
		$x = 1;
	}
}
?>
<?php if(isset($x)) { ?>
<script language="javascript" type="text/javascript">window.top.window.stopUpload('<?php echo $feed->postMessage($_POST['message'], $_FILES['images'], (isset($_POST['type']) ? $_POST['type'] : null), $_POST['value'], $_POST['privacy'], $_POST['group'], $_POST['page']); ?>');</script>
<?php } else { ?>
<script language="javascript" type="text/javascript">window.top.window.stopUpload(' ')</script>
<?php } ?>
<?php
mysqli_close($db);
?>