<?php

function url_parser($values) {
	$value		= $values['value'];
	$type 		= $values['type'];
	$message	= $values['message'];
	
	// If the event type and values are empty (prevents interfering with event based plugins)
	if(empty($type) && empty($value) && !empty($message)) {
		preg_match_all('/(?i)\b((?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))/', $message, $link);
		
		// Get the first URL in the message
		$url = $link[0][0] ?? '';
		
		// If the message contains an URL
		if($url) {
			// If match www. at the beginning of the string, add http before
			if(substr($link[0][0], 0, 4) == 'www.') {
				$url = 'http://'.$link[0][0];
			}
			
			// Fetch the URL content
            $httpClient = new GuzzleHttp\Client();

			try {
                $content = $httpClient->request('GET', $url,
                    [   'timeout' => 5,
                        'headers' => [
                            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.97 Safari/537.36'
                        ]
                    ]);

                $headerType = $content->getHeader('content-type');

                $parsed = GuzzleHttp\Psr7\parse_header($headerType);

                $content = mb_convert_encoding($content->getBody(), 'UTF-8', $parsed[0]['charset'] ?? 'UTF-8');

                // Get the metadata content
                $meta = getMetaTags($content);

                // Site Title
                $title = $meta['title'] ?? '';

                // Site Description
                $description = $meta['description'] ?? '';

                // If the page has a title
                if($title) {
                    if(isset($meta['og:image']) && !empty($meta['og:image'])) {
                        $imageUrl = parse_url($meta['og:image']);
                        $extension = pathinfo($imageUrl['path'], PATHINFO_EXTENSION);

                        if(in_array($extension, ['png', 'jpg', 'jpeg', 'gif', 'webp']) && isset($imageUrl['host'])) {
                            try {
                                $imageName = uniqid().'.'.$extension;
                                $image = $httpClient->request('GET', $meta['og:image'], ['sink' => __DIR__ .'/uploads/'.$imageName, 'timeout' => 5]);
                            } catch(Exception $e) {
                                #return array($e->getMessage());
                            }

                        }
                    }

                    // Build the URL information
                    $array = array('url' => (strlen($url) > 350 ? substr($url, 0, 350).'#' : $url), 'title' => (strlen($title) > 64 ? substr($title, 0, 64).'...' : $title), 'description' => (strlen($description) > 350 ? substr($description, 0, 350).'...' : $description), 'image' => $imageName ?? null, 'cache_date' => $cache_date ?? null);

                    $output = json_encode($array);

                    // This condition checks for rare cases where the output could be empty when the content has special characters not supported by json_encode
                    if(!empty($output)) {
                        // Return the formatted result (prefix:{json_value})
                        return 'url:'.json_encode($array);
                    } else {
                        return false;
                    }
                } else {
                    #return $value;
                }
            } catch(Exception $e) {
			    return array($e->getMessage());
            }
		} else {
			return $value;
		}
	}
}

function getMetaTags($value) {
	$array = array();

	// Match the meta tags
	$pattern = '
	~<\s*meta\s

	# using lookahead to capture type to $1
	(?=[^>]*?
	\b(?:name|property|http-equiv)\s*=\s*
	(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
	([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
	)

	# capture content to $2
	[^>]*?\bcontent\s*=\s*
	(?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
	([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
	[^>]*>

	~ix';
	if(preg_match_all($pattern, $value, $out)) {
		$array = array_combine(array_map('strtolower', $out[1]), $out[2]);
	}

	// Match the title tags
	preg_match("/<title[^>]*>(.*?)<\/title>/is", $value, $title);
	$array['title'] = $title[1];
	
	// Return the result
	return $array;
}
?>