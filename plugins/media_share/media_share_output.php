<?php
function media_share_output($values) {
	$value	= $values['value'];
	$type 	= $values['type'];
	
	// Check if the message is a file and there's no type set
	if(substr($value, 0, 6) == 'media:') {
		global $CONF;
		$files = json_decode(str_replace('media:', '', $value), true);

		$output = '';
		if(isset($files['videos'])) {
            foreach($files['videos'] as $file) {
                $output .= '
			<video controls controlsList="nodownload" preload="metadata">
				<source src="'.$CONF['url'].'/plugins/'.basename(__DIR__).'/uploads/'.$file['filename'].'" type="video/'.$file['ext'].'">
			</video>';
            }
        }

        if(isset($files['audios'])) {
            foreach($files['audios'] as $file) {
                $output .= '
			<audio controls controlsList="nodownload">
				<source src="'.$CONF['url'].'/plugins/'.basename(__DIR__).'/uploads/'.$file['filename'].'" type="audio/'.$file['ext'].'">
			</audio>';
            }
        }
		
		if(isset($values['plugin_chat']) && $values['plugin_chat'] == 1) {
			$output = '<div class="media-share-container-chat">'.$output.'</div>';
		} else {
			$output = '<div class="media-share-container">'.$output.'</div><div class="message-divider"></div>';
		}
		
		return $output;
	}

    // SoundCloud
    if(preg_match(sprintf('/(.+)%s(.+)$/ui', '\/sets\/'), $value, $match)) {
        $height = '450';
    } else {
        $height = '166';
    }
    if(substr($value, 0, 3) == 'sc:') {
        return '<iframe width="100%" height="'.$height.'" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https://soundcloud.com'.str_replace('sc:', '', $value).'"></iframe><div class="message-divider"></div>';
    }

    // Mixcloud
    if(substr($value, 0, 3) == 'mc:') {
        return '<iframe width="100%" height="120" src="https://www.mixcloud.com/widget/iframe/?feed='.str_replace('mc:', 'https://www.mixcloud.com', $value).'&light=1" frameborder="0" style="float: left;"></iframe><div class="message-divider"></div>';
    }

    // Tunein
    if(substr($value, 0, 3) == 'ti:') {
        return '<iframe src="https://tunein.com/embed/player/'.str_replace('ti:', '', $value).'/" style="width: 100%; height: 100px; float: left;" scrolling="no" frameborder="no"></iframe><div class="message-divider"></div>';
    }

    // Spotify
    if(substr($value, 0, 3) == 'sp:') {
        return '<iframe src="https://embed.spotify.com/?uri='.str_replace('sp:', 'spotify:', $value).'" width="100%" height="80" frameborder="0" allowtransparency="true" style="float: left;"></iframe><div class="message-divider"></div>';
    }

    // YouTube
    if(substr($value, 0, 3) == 'yt:') {
        return '<div class="message-type-player event-video"><iframe width="100%" height="315" src="//www.youtube.com/embed/'.str_replace('yt:', '', $value).'" frameborder="0" allowfullscreen></iframe></div><div class="message-divider"></div>';
    }

    // Vimeo
    if(substr($value, 0, 3) == 'vm:') {
        return '<div class="message-type-player event-video"><iframe width="100%" height="315" src="//player.vimeo.com/video/'.str_replace('vm:', '', $value).'" frameborder="0" allowfullscreen></iframe></div><div class="message-divider"></div>';
    }

    // Twitch
    if(substr($value, 0, 3) == 'tw:') {
        return '<div class="message-type-player event-video"><iframe src="//player.twitch.tv/?channel='.str_replace('tw:', '', $value).'&autoplay=false" frameborder="0" scrolling="no" height="378" width="100%" style="float: left;"></iframe></div><div class="message-divider"></div>';
    }

    // Dailymotion
    if(substr($value, 0, 3) == 'dm:') {
        return '<div class="message-type-player event-video"><iframe frameborder="0" width="100%" height="315" src="//www.dailymotion.com/embed/video/'.str_replace('dm:', '', $value).'" style="float: left;" allowfullscreen></iframe></div><div class="message-divider"></div>';
    }

    // Metacafe
    if(substr($value, 0, 3) == 'mc:') {
        return '<div class="message-type-player event-video"><iframe width="100%" height="315" src="http://www.metacafe.com/embed/'.str_replace('mc:', '', $value).'" frameborder="0" style="float: left;" allowfullscreen></iframe></div><div class="message-divider"></div>';
    }

    // Giphy
    if(substr($value, 0, 3) == 'gy:') {
        return '<div class="message-type-player event-video"><iframe src="//giphy.com/embed/'.str_replace('gy:', '', $value).'?html5=true&playOnHover=true&hideSocial=true" width="100%" height="266" frameborder="0" class="giphy-embed" style="float: left;" allowfullscreen=""></iframe></div><div class="message-divider"></div>';
    }

    // Streamable
    if(substr($value, 0, 3) == 'sa:') {
        return '<div class="message-type-player event-video"><iframe src="//streamable.com/t/'.str_replace('sa:', '', $value).'" width="100%" height="315" frameborder="0" allowfullscreen></iframe></div><div class="message-divider"></div>';
    }

    // Gfycat
    if(substr($value, 0, 3) == 'gf:') {
        return '<div class="message-type-player event-video"><iframe src="https://gfycat.com/ifr/'.str_replace('gf:', '', $value).'" frameborder="0" scrolling="no" width="100%" height="415" allowfullscreen></iframe></div>';
    }
}
?>