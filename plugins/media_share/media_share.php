<?php
function media_share($values) {
	global $LNG, $pluginsSettings;
	$value	    = $values['value'];
	$type 	    = $values['type'];
    $message	= $values['message'];

	$files = $_FILES['media-share-files'] ?? null;

	$videoExt = explode(',', $pluginsSettings['media_share_video_extensions']);
	$audioExt = explode(',', $pluginsSettings['media_share_audio_extensions']);
	$mediaServices = explode(',', $pluginsSettings['media_share_services']);

    // If the event type and values are empty (prevents interfering with event based plugins)
    if(empty($type) && empty($value) && !empty($message)) {
        preg_match_all('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', $message, $link);

        // Get the first URL in the message
        $url = $link[0][0] ?? '';

        // If the message contains an URL
        if($url) {
            // SoundCloud
            if(in_array('soundcloud', $mediaServices)) {
                if(substr($url, 0, 23) == "https://soundcloud.com/") {
                    return htmlspecialchars('sc:'.parse_url($url, PHP_URL_PATH), ENT_QUOTES, 'UTF-8');
                }
            }

            // Mixcloud
            if(in_array('mixcloud', $mediaServices)) {
                if(substr($url, 0, 25) == 'https://www.mixcloud.com/') {
                    // Parse the song path
                    $pUrl = parse_url($url);

                    return htmlspecialchars('mc:'.$pUrl['path'], ENT_QUOTES, 'UTF-8');
                }
            }

            // Tunein
            if(in_array('tunein', $mediaServices)) {
                if(substr($url, 0, 19) == 'https://tunein.com/') {
                    // Parse the song path
                    $pUrl = explode('-', $url);

                    return htmlspecialchars('ti:'.str_replace('/', '', end($pUrl)), ENT_QUOTES, 'UTF-8');
                }
            }

            // Spotify
            if(in_array('spotify', $mediaServices)) {
                if(substr($url, 0, 25) == 'https://play.spotify.com/' || substr($url, 0, 25) == 'https://open.spotify.com/') {
                    // Parse the song
                    $pUrl = parse_url($url);

                    $values = array_filter(explode('/', $pUrl['path']));

                    if(in_array("track", $values)) {
                        return htmlspecialchars('sp:track:'.$values[2], ENT_QUOTES, 'UTF-8');
                    }

                    if(in_array("artist", $values)) {
                        return htmlspecialchars('sp:artist:'.$values[2], ENT_QUOTES, 'UTF-8');
                    }

                    if(in_array("playlist", $values)) {
                        return htmlspecialchars('sp:user:'.$values[2].':playlist:'.$values[4], ENT_QUOTES, 'UTF-8');
                    }
                }
            }

            // YouTube
            if(in_array('youtube', $mediaServices)) {
                if(substr($url, 0, 24) == "https://www.youtube.com/" || substr($url, 0, 17) == "https://youtu.be/") {
                    parse_str(parse_url($url, PHP_URL_QUERY), $my_array_of_vars);
                    if(substr($url, 0, 17) == "https://youtu.be/") {
                        return htmlspecialchars(str_replace('https://youtu.be', 'yt:', $url), ENT_QUOTES, 'UTF-8');
                    } else {
                        return htmlspecialchars('yt:'.$my_array_of_vars['v'], ENT_QUOTES, 'UTF-8');
                    }
                }
            }

            // Vimeo
            if(in_array('vimeo', $mediaServices)) {
                if(substr($url, 0, 18) == "https://vimeo.com/") {
                    return htmlspecialchars('vm:' . (int)substr(parse_url($url, PHP_URL_PATH), 1), ENT_QUOTES, 'UTF-8');
                }
            }

            // Twitch
            if(in_array('twitch', $mediaServices)) {
                if(substr($url, 0, 22) == 'https://www.twitch.tv/') {
                    // Parse the channel name
                    $pUrl = parse_url($url);

                    return htmlspecialchars('tw:'.$pUrl['path'], ENT_QUOTES, 'UTF-8');
                }
            }

            // Dailymotion
            if(in_array('dailymotion', $mediaServices)) {
                if(substr($url, 0, 34) == 'https://www.dailymotion.com/video/' || substr($url, 0, 15) == 'https://dai.ly/') {
                    // Parse the video id
                    $pUrl = parse_url($url);

                    $id = str_replace('/', '', explode('_', str_replace('/video/', '', $pUrl['path'])));

                    return htmlspecialchars('dm:'.$id[0], ENT_QUOTES, 'UTF-8');
                }
            }

            // Metacafe
            if(in_array('metacafe', $mediaServices)) {
                if(substr($url, 0, 30) == 'http://www.metacafe.com/watch/') {
                    // Parse the video id

                    $id = str_replace('http://www.metacafe.com/watch/', '', $url);

                    return htmlspecialchars('mc:'.$id, ENT_QUOTES, 'UTF-8');
                }
            }

            // Giphy
            if(in_array('giphy', $mediaServices)) {
                if(substr($url, 0, 23) == 'https://giphy.com/gifs/') {
                    // Parse the gif id
                    $pUrl = explode('-', $url);

                    // If the Giphy doesn't have a title
                    if(!is_array($pUrl)) {
                        $pUrl = explode('/', $url);
                    }

                    return htmlspecialchars('gy:'.end($pUrl), ENT_QUOTES, 'UTF-8');
                }
            }

            // Streamable
            if(in_array('streamable', $mediaServices)) {
                if(substr($url, 0, 23) == 'https://streamable.com/') {
                    $pUrl = str_replace('#', '', explode('/', $url));

                    return htmlspecialchars('sa:'.end($pUrl), ENT_QUOTES, 'UTF-8');
                }
            }

            // Gfycat
            if(in_array('gfycat', $mediaServices)) {
                if(substr($url, 0, 19) == 'https://gfycat.com/' || substr($url, 0, 32) == 'https://gfycat.com/gifs/detail/') {
                    $pUrl = str_replace('#', '', explode('/', $url));

                    return htmlspecialchars('gf:'.end($pUrl), ENT_QUOTES, 'UTF-8');
                }
            }
        }
    } else {
        if($files['name'][0]) {
            // Get the settings
            $max_file_size = $pluginsSettings['media_share_max_size'];
            $all_ext = array_merge($videoExt, $audioExt);

            // If the number of files selected is higher than allowed
            if(count($files['name']) > 1) {
                return array($LNG['plugin_media_share_one_file']);
            }

            if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
                if($files['error'] == 0) {
                    // Store the file infos
                    $file_name = pathinfo($files['name'], PATHINFO_FILENAME);
                    $file_ext = pathinfo($files['name'], PATHINFO_EXTENSION);
                    $file_size = $files['size'];
                    $file_temp = $files['tmp_name'];

                    // If the file_size exceeds the allowed size per file limitation
                    if($file_size < 1 || $file_size > $max_file_size) {
                        $err_size[] = $file_name.' <strong>('.fsize($file_size).'</strong>)';
                    }

                    // If the file extension does not match the allowed file extensions
                    if(empty($file_ext) || !in_array(strtolower($file_ext), $all_ext)) {
                        $err_ext[] = $file_name.' <strong>('.$file_ext.'</strong>)';
                    }

                    // Generate the files
                    $size[] = $file_size;
                    $ext[] = $file_ext;
                    $orig_name[] = $file_name;
                    $tmp_name[] = $file_temp;
                    $final_name[] = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$file_ext;
                    $media_type = (in_array($file_ext, $videoExt) ? 'videos' : 'audios');
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
                        $all_ext = ($type == 'video' ? $videoExt : $audioExt);

                        // If the file_size exceeds the allowed size per file limitation
                        if($file_size < 1 || $file_size > $max_file_size) {
                            $err_size[] = $file_name.' <strong>('.fsize($file_size).'</strong>)';
                        }

                        // If the file extension does not match the allowed file extensions
                        if(empty($file_ext) || !in_array(strtolower($file_ext), $all_ext)) {
                            $err_ext[] = $file_name.' <strong>('.$file_ext.'</strong>)';
                        }

                        // Generate the files
                        $size[] = $file_size;
                        $ext[] = $file_ext;
                        $orig_name[] = $file_name;
                        $tmp_name[] = $file_temp;
                        $final_name[] = mt_rand().'_'.mt_rand().'_'.mt_rand().'.'.$file_ext;
                        $media_type = (in_array($file_ext, $videoExt) ? 'videos' : 'audios');
                    } else {
                        return array('Error code: '.$files['error'][$key]);
                    }
                }
            }

            // If there's any error registered
            if(isset($err_size) || isset($err_ext)) {
                $err = '';
                if(isset($err_size)) {
                    $err .= sprintf($LNG['plugin_media_share_size'], implode(', ', $err_size), fsize($max_file_size));
                }
                if(isset($err_ext)) {
                    $err .= sprintf($LNG['plugin_media_share_format'], implode(', ', $err_ext), implode(', ', $all_ext));
                }
                return array($err);
            }

            // Get the total size of the uploaded files
            $total = 0;
            foreach($size as $count) {
                $total = $total+$count;
            }

            // Store the files
            foreach($final_name as $key => $name) {
                if(move_uploaded_file($tmp_name[$key], __DIR__ .'/uploads/'.$name)) {
                    $store[] = array('name' => $orig_name[$key], 'filename' => $name, 'size' => $size[$key], 'ext' => $ext[$key]);
                }
            }

            $array = array($media_type => $store);

            // Return the formatted result (prefix:{json_value})
            return 'media:'.json_encode($array);
        }
    }
}
?>