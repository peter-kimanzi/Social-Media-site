<?php
function file_share_settings() {
	global $CONF, $LNG, $pluginsSettings;

	// Settings Content
	return '
	<form action="'.$CONF['url'].'/index.php?a=admin&b=plugins&settings='.$_GET['settings'].'" method="post">
	<div class="page-inner">
		'.generateToken(1).'
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_file_share_max_files'].'</div>
			<div class="page-input-content">
				<input type="number" name="file_share_max_files" value="'.$pluginsSettings['file_share_max_files'].'">
				<div class="page-input-sub">'.$LNG['plugin_file_share_max_files_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_file_share_max_size'].'</div>
			<div class="page-input-content">
				<input type="number" name="file_share_max_size" value="'.round(($pluginsSettings['file_share_max_size'] / 1024) / 1024).'">
				<div class="page-input-sub">'.$LNG['plugin_file_share_max_size_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_file_share_max_upload_size'].'</div>
			<div class="page-input-content">
				<input type="number" name="file_share_max_upload_size" value="'.round(($pluginsSettings['file_share_max_upload_size'] / 1024) / 1024).'">
				<div class="page-input-sub">'.$LNG['plugin_file_share_max_upload_size_sub'].'</div>
			</div>
		</div>
		
		<div class="page-input-container">
			<div class="page-input-title">'.$LNG['plugin_file_share_allowed_extensions'].'</div>
			<div class="page-input-content">
				<input type="text" name="file_share_allowed_extensions" value="'.$pluginsSettings['file_share_allowed_extensions'].'">
				<div class="page-input-sub">'.$LNG['plugin_file_share_allowed_extensions_sub'].'</div>
			</div>
		</div>
	</div>
	<div class="message-divider"></div>
	<div class="page-inner">
		<div class="page-input-title"></div><input type="submit" value="'.$LNG['plugin_file_share_save'].'">
	</div>
	</form></div><div>';
}

function file_share_save($values) {
	global $db;

	// Validate the inputs
	$values['file_share_max_files'] = (int)$values['file_share_max_files'];
    $values['file_share_max_size'] = (((int)$values['file_share_max_size']) * 1024) * 1024;
    $values['file_share_max_upload_size'] = (((int)$values['file_share_max_upload_size']) * 1024) * 1024;
    $values['file_share_allowed_extensions'] = strtolower(str_replace(' ', '', $values['file_share_allowed_extensions']));

    $query = $db->prepare("INSERT INTO `plugins_settings` (`name`, `value`) VALUES('file_share_max_files', ?), ('file_share_max_size', ?), ('file_share_max_upload_size', ?), ('file_share_allowed_extensions', ?) ON DUPLICATE KEY UPDATE `name` = VALUES(`name`), `value` = VALUES(`value`)");
    $query->bind_param('iiis', $values['file_share_max_files'], $values['file_share_max_size'], $values['file_share_max_upload_size'], $values['file_share_allowed_extensions']);
    $query->execute();
    $affected = $query->affected_rows;
    $query->close();

    return 1;
}
?>