<?php
function file_share($values) {
	global $LNG, $pluginsSettings;
	$value	= $values['value'];
	$type 	= $values['type'];
	
	$files = $_FILES['file-share-files'] ?? null;

	// If there's no type set
	if(!$type && $files['name'][0]) {

		// Get the settings
		$max_files = $pluginsSettings['file_share_max_files'];
		$max_size = $pluginsSettings['file_share_max_size'];
		$max_file_size = $pluginsSettings['file_share_max_upload_size'];
		$all_ext = $pluginsSettings['file_share_allowed_extensions'];
		
		// If the number of files selected is higher than allowed
		if(count($files['name']) > $max_files) {
			return array(sprintf($LNG['plugin_file_share_number'], $max_files));
		}
		
		if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
			if($files['error'] == 0) {
				// Store the file infos
				$file_name = pathinfo($files['name'], PATHINFO_FILENAME);
				$file_ext = pathinfo($files['name'], PATHINFO_EXTENSION);
				$file_size = $files['size'];
				$file_temp = $files['tmp_name'];
				
				// If the file_size exceeds the allowed size per file limitation
				if($file_size < 1 || $file_size > $max_size) {
					$err_size[] = $file_name.' <strong>('.fsize($file_size).'</strong>)';
				}
				
				// If the file extension does not match the allowed file extensions
				if(empty($file_ext) || !in_array(strtolower($file_ext), explode(',', $all_ext))) {
					$err_ext[] = $file_name.' <strong>('.$file_ext.'</strong>)';
				}
				
				// Generate the files
				$size[] = $file_size;
				$ext[] = $file_ext;
				$orig_name[] = $file_name;
				$tmp_name[] = $file_temp;
				$final_name[] = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$file_ext;
			} else {
				return array('Error code: '.$files['error']);
			}
		} else {
			foreach($files['error'] as $key => $val) {
				if($files['error'][$key] == 0) {
					// Store the file infos
					$file_name = pathinfo($files['name'][$key], PATHINFO_FILENAME);
					$file_ext = pathinfo($files['name'][$key], PATHINFO_EXTENSION);
					$file_size = $files['size'][$key];
					$file_temp = $files['tmp_name'][$key];
					
					// If the file_size exceeds the allowed size per file limitation
					if($file_size < 1 || $file_size > $max_size) {
						$err_size[] = $file_name.' <strong>('.fsize($file_size).'</strong>)';
					}
					
					// If the file extension does not match the allowed file extensions
					if(empty($file_ext) || !in_array(strtolower($file_ext), explode(',', $all_ext))) {
						$err_ext[] = $file_name.' <strong>('.$file_ext.'</strong>)';
					}
					
					// Generate the files
					$size[] = $file_size;
					$ext[] = $file_ext;
					$orig_name[] = $file_name;
					$tmp_name[] = $file_temp;
					$final_name[] = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$file_ext;
				} else {
					return array('Error code: '.$files['error'][$key]);
				}
			}
		}
		
		// If there's any error registered
		if(isset($err_size) || isset($err_ext)) {
		    $err = '';
			if(isset($err_size)) {
				$err .= sprintf($LNG['plugin_file_share_exceeds'], implode(', ', $err_size), fsize($max_size));
			}
			if(isset($err_ext)) {
				$err .= sprintf($LNG['plugin_file_share_format'], implode(', ', $err_ext), implode(', ', explode(',', $all_ext)));
			}
			return array($err);
		}
		
		// Get the total size of the uploaded files
        $total = 0;
		foreach($size as $count) {
			$total = $total+$count;
		}
		
		// If the total size of the uploaded files exceed the total amount of size allowed
		if($total > $max_file_size) {
			return array(sprintf($LNG['plugin_file_share_total'], fsize($total), fsize($max_file_size)));
		}
		
		// Store the files
		foreach($final_name as $key => $name) {
			if(move_uploaded_file($tmp_name[$key], __DIR__ .'/uploads/'.$name)) {
				$store[] = array('name' => $orig_name[$key], 'filename' => $name, 'size' => $size[$key], 'ext' => $ext[$key]);
			}
		}
		
		$array = array('files' => $store);
		
		// Return the formatted result (prefix:{json_value})
		return 'file:'.json_encode($array);
	}
}
?>