<?php
function weather_sidebar($values) {
	global $LNG, $pluginsSettings;

	if(empty($pluginsSettings['weather_api_key'])) {
	    return false;
    }

	// Define the location
	if($values['country'] && $values['location']) {
		$location = $values['location'].', '.$values['country'];
	} elseif($values['country']) {
		$location = $values['country'];
	} elseif($values['location']) {
		$location = $values['location'];
	} else {
		$location = $pluginsSettings['weather_default_location'];
	}

    if(isset($_COOKIE['lat']) == false || isset($_COOKIE['lon']) == false) {
        setcookie('lat', 0, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH);
        setcookie('lon', 0, time() + (10 * 365 * 24 * 60 * 60), COOKIE_PATH);
        $_COOKIE['lat'] = '0';
        $_COOKIE['lon'] = '0';
    }
	
	// Define the format
	if(isset($_GET['weather_format']) && $_GET['weather_format'] == 1) {
		setcookie("format", "1", time() + (10 * 365 * 24 * 60 * 60));
		$cf = 'F';
		$f = '1';
	} elseif(isset($_GET['weather_format']) && $_GET['weather_format'] == 0) {
		setcookie("format", "0", time() + (10 * 365 * 24 * 60 * 60));
		$cf = 'C';
		$f = '0';
	} elseif(isset($_COOKIE['format']) == false) {
		$f = $pluginsSettings['weather_format'];
		$cf = ($f) ? 'F' : 'C';
	} elseif($_COOKIE['format'] == 1) {
		$cf = 'F';
		$f = '1';
	} elseif($_COOKIE['format'] == 0) {
		$cf = 'C';
		$f = '0';
	}
	
	$weather = new Weather($pluginsSettings['weather_api_key']);
	$weather->units = $f;
	$weather->days = $pluginsSettings['weather_days'];
	if(!empty($_COOKIE['lat']) && !empty($_COOKIE['lon'])) {
        $weather_current = $weather->get(null, [$_COOKIE['lat'], $_COOKIE['lon']], null, 0, 0, null, null);
    } else {
        $weather_current = $weather->get($location, null, null, 0, 0, null, null);
    }
	$now = $weather->data(0, $weather_current);
	
	if($pluginsSettings['weather_days'] > 0 && isset($now['location_id'])) {
		$weather_forecast = $weather->get(null, null, $now['location_id'], 1, 0, null, null);
        $forecasts = $weather->data(1, $weather_forecast);
    }

	$output = '<script>geoLocation(\''.COOKIE_PATH.'\');</script>';
	if($now['cod'] != 200) {
		$output .= '
		<div class="sidebar-container widget-weather-plugin">
			<div class="sidebar-content">
				<div class="sidebar-header">'.(isset($now['location']) ? sprintf($LNG['plugin_weather_weather_in'], $now['location']) : $LNG['plugin_weather_weather']).'</div>
				<div class="sidebar-inner" style="overflow: auto">'.$LNG['plugin_weather_error'].'</div>
			</div>
		</div>';
	} else {
	    $forecast = '';
	    if(isset($forecasts['daily'])) {
            foreach($forecasts['daily'] as $x) {
                $forecast .= '<div class="sidebar-list">'.$LNG['plugin_weather_'.$x['day']].' <div class="forecast"><img src="'.$values['site_url'].'/plugins/'.basename(__DIR__).'/icons/'.$x['icon'].'.png'.'"><span class="low">'.$x['temp']['min'].'&deg;</span> / <span class="high">'.$x['temp']['max'].'&deg;</span></div></div>';
            }
        }
		
		$output .= '
		<div class="sidebar-container widget-weather-plugin">
			<div class="sidebar-content">
				<div class="sidebar-header">'.sprintf($LNG['plugin_weather_weather_in'], $now['location']).'</div>
				<div class="sidebar-inner" style="overflow: auto"><div class="weather-icon"><div class="weather-icon-image"><img src="'.$values['site_url'].'/plugins/'.basename(__DIR__).'/icons/'.$now['icon'].'.png'.'"></div><div class="weather-now">'.$now['temperature'].'&deg; <a href="'.$values['site_url'].'/index.php?a=feed&weather_format='.(($cf == 'C') ? '1' : '0').'" title="'.(($cf == 'C') ? $LNG['plugin_weather_f'] : $LNG['plugin_weather_c']).'">'.$cf.'</a></div></div>
				<div class="weather-now-info" title="'.$LNG['plugin_weather_wind_speed'].'"><img src="'.$values['site_url'].'/plugins/'.basename(__DIR__).'/icons/wind.png'.'">'.$now['wind']['speed'][0].' '.$now['wind']['speed'][1].'</div>
				<div class="weather-now-info" title="'.$LNG['plugin_weather_humidity'].'"><img src="'.$values['site_url'].'/plugins/'.basename(__DIR__).'/icons/drop.png'.'">'.$now['humidity'].'%'.'</div></div>
				'.$forecast.'
			</div>
		</div>';
	}
	return $output;
}

/**
 * Retrieves the forecast for a location
 *
 * Retrieves forecast details from an API endpoint and formats the results to be used universally
 */
class Weather {

    /**
     * The OpenWeatherMap API key
     * @var string
     */
    private $api_key;

    /**
     * The units format, 0 for metrics and 1 for imperial
     * @var int
     */
    public $units;

    /**
     * The number of forecasted days to be returned
     * @vary int
     */
    public $days;

    public function __construct($api_key = null, $units = null) {
        $this->api_key	= $api_key;
        $this->units	= $units;
    }

    /**
     * @param   string  $location       The location to be searched
     * @param   array   $coordinates    The geographic coordinates
     * @param   int     $id             The location ID
     * @param   int     $end_point      The endpoint link, 0 for current weather, 1 for forecast
     * @param   int     $format         The format output, 0 for json, 1 for xml
     * @param   int     $days           The number of days to be returned
     * @param   string  $lang           The language code
     * @return  string
     */
    function get($location = null, $coordinates = null, $id = null, $end_point = null, $format = null, $days = null, $lang = null) {
        // Define the API endpoint
        if($end_point == 1) {
            $api_endpoint = 'http://api.openweathermap.org/data/2.5/forecast?';
        } else {
            $api_endpoint = 'http://api.openweathermap.org/data/2.5/weather?';
        }

        // Parameters to build the query URL
        $params = [
            'q'     => $location,
            'units' => ($this->units == 1) ? 'imperial' : 'metric',
            'cnt'	=> ($days) ? $days : null,
            'mode'	=> ($format) ? 'xml' : 'json',
            'lang'	=> ($lang) ? $lang : 'en',
            'lat'   => $coordinates[0] ?? null,
            'lon'   => $coordinates[1] ?? null,
            'id'    => $id,
            'APPID'	=> $this->api_key
        ];

        // Build the request URL
        $url = $api_endpoint.http_build_query($params);
		
        // Check if the cURL is enabled and try to get a response
        if(function_exists('curl_exec')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            $response = curl_exec($ch);
            curl_close($ch);
        }

        // If there is no response using cURL
        if(empty($response)) {
            // Try using file_get_contents to get a response
            $response = file_get_contents($url);
        }

        // Return the result
        return $response;
    }

    /**
     * @param   int     $type   The type of the format, 0 for current weather, 1 for forecast
     * @param   string  $data   The data to be consumed
     * @param   int     $day    The day to filter the results for
     * @return  array
     */
    public function data($type, $data = null, $day = null) {
        // Decode the JSON content into an array
        $data = json_decode($data, true);

        if($type == 1) {
            if(isset($data['list'])) {
                foreach($data['list'] as $key => $value) {
                    // Format the content for universal use
                    $data['hourly'][$key]['temp']['min']        = number_format(round($value['main']['temp_min'], 0), 0);
                    $data['hourly'][$key]['temp']['max']        = number_format(round($value['main']['temp_max'], 0), 0);
                    $data['hourly'][$key]['condition']          = mb_strtolower($value['weather'][0]['id']);
                    $data['hourly'][$key]['wind']['speed']      = $this->transform(0, $value['wind']['speed']);
                    $data['hourly'][$key]['wind']['direction']  = (isset($value['wind']['deg']) ? round($value['wind']['deg']) : '');
                    $data['hourly'][$key]['humidity']		    = $value['main']['humidity'];
                    $data['hourly'][$key]['pressure']		    = $value['main']['pressure'];
                    $data['hourly'][$key]['icon']			    = $this->icons($value['weather'][0]['id']);
                    $data['hourly'][$key]['day']			    = $this->transform(1, gmdate("w", $value['dt']));
                    $data['hourly'][$key]['date']			    = explode('-', gmdate("d-m-Y", $value['dt']));
                    $data['hourly'][$key]['hour']			    = explode('-', gmdate("H-i-s", $value['dt']));

                    // When requesting an hourly forecast, unset any extra data from other days
                    if(isset($day) && $data['hourly'][$key]['date'][0] != $day) {
                        unset($data['hourly'][$key]);
                    }
                }

                if($day == null) {
                    $lastDay = '';
                    $count = 0;
                    foreach($data['list'] as $key => $value) {
                        // If the number of results exceeds the forecasted days limit
                        if($count > $this->days) {
                            unset($data['daily'][$lastDay]);
                            break;
                        }

                        if($data['hourly'][$key]['date'][0] != $lastDay) {
                            $day = $data['hourly'][$key]['date'][0];
                            $lastDay = $data['hourly'][$key]['date'][0];

                            $data['daily'][$day]['temp']['min'] = number_format(round($value['main']['temp_min'], 0), 0);
                            $data['daily'][$day]['temp']['max'] = number_format(round($value['main']['temp_max'], 0), 0);
                            $data['daily'][$day]['condition']   = mb_strtolower($value['weather'][0]['id']);
                            $data['daily'][$day]['humidity']    = $value['main']['humidity'];
                            $data['daily'][$day]['icon']		= $this->icons($value['weather'][0]['id']);
                            $data['daily'][$day]['day']		    = $this->transform(1, gmdate("w", $value['dt']));
                            $data['daily'][$day]['date']		= explode('-', gmdate("d-m-Y", $value['dt']));
                            $data['daily'][$day]['hour']		= explode('-', gmdate("H-i-s", $value['dt']));
                            $count++;
                        } else {
                            // Update the forecast values where needed
                            $data['daily'][$lastDay]['temp']['min']  = number_format(round($value['main']['temp_min'], 0), 0) < $data['daily'][$lastDay]['temp']['min'] ? number_format(round($value['main']['temp_min'])) : $data['daily'][$lastDay]['temp']['min'];
                            $data['daily'][$lastDay]['temp']['max']  = number_format(round($value['main']['temp_max'], 0), 0) > $data['daily'][$lastDay]['temp']['max'] ? number_format(round($value['main']['temp_max'])) : $data['daily'][$lastDay]['temp']['max'];
                            $data['daily'][$lastDay]['hour']         = explode('-', gmdate("H-i-s", $value['dt']));

                            // Prioritize the weather condition based on the hour
                            if($data['daily'][$lastDay]['hour'][0] >= 12 && $data['daily'][$lastDay]['hour'][0] <= 16) {
                                $data['daily'][$lastDay]['condition']    = mb_strtolower($value['weather'][0]['id']);
                                $data['daily'][$lastDay]['icon']         = $this->icons($value['weather'][0]['id']);
                            }
                        }
                    }
                }
            }
        } else {
            if(isset($data['id'])) {
                $data['location_id']                          = $data['id'];
                $data['city']                                 = $data['name'];
                $data['country_code']			              = $data['sys']['country'];
                $data['location']				              = $data['name'].', '.$data['sys']['country'];
                $data['temperature']                          = number_format(round($data['main']['temp'], 0), 0);
                $data['condition']                            = mb_strtolower($data['weather'][0]['id']);
                $data['wind']['speed']                        = $this->transform(0, $data['wind']['speed']);
                $data['wind']['direction']                    = (isset($data['wind']['deg']) ? round($data['wind']['deg']) : '');
                $data['humidity']				              = $data['main']['humidity'];
                $data['pressure']				              = $data['main']['pressure'];
                $data['icon']					              = $this->icons($data['weather'][0]['id']);
                $data['day']					              = $this->transform(1, gmdate("w", $data['dt']));
                $data['date']					              = explode('-', gmdate("d-m-Y", $data['dt']));
                $data['sunrise']				              = ($data['sys']['sunrise'] ? date('H:i', $data['sys']['sunrise']) : 0);
                $data['sunset']				                  = ($data['sys']['sunset'] ? date('H:i', $data['sys']['sunset']) : 0);
            }
        }

        // Return the result
        return $data;
    }

    /**
     * @param   string  $code   The code to be assigned to an image
     * @return  string
     */
    public function icons($code = null) {
        if(in_array($code, [200, 201, 202, 210, 211, 212, 221, 230, 231, 232])) {
            return "11";
        } elseif(in_array($code, [300, 301, 302, 310, 311, 312, 313, 314, 321])) {
            return "09";
        } elseif(in_array($code, [500, 501, 502, 503, 504])) {
            return "10";
        } elseif(in_array($code, [511])) {
            return "13";
        } elseif(in_array($code, [520, 521, 522, 531])) {
            return "09";
        } elseif(in_array($code, [600, 601, 602, 611, 612, 615, 616, 620, 621, 622])) {
            return "13";
        } elseif(in_array($code, [701, 711, 721, 731, 741, 751, 761, 762, 771, 781])) {
            return "50";
        } elseif(in_array($code, [800])) {
            return "01";
        } elseif(in_array($code, [801])) {
            return "02";
        } elseif(in_array($code, [802])) {
            return "03";
        } elseif(in_array($code, [803, 804])) {
            return "04";
        } elseif(in_array($code, [903])) {
            return "101";
        } elseif(in_array($code, [904])) {
            return "102";
        } elseif(in_array($code, [905])) {
            return "103";
        } elseif(in_array($code, [900, 901, 902, 906])) {
            return "104";
        } else {
            return "00";
        }
    }

    /**
     * @param   string  $type   The type of the transformation, 0 for units, 1 for days
     * @param   string  $data   The data to be consumed
     * @return  array
     */
    function transform($type, $data) {
        if($type == 1) {
            $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
            return $days[$data];
        } else {
            if($this->units) {
                return [round($data, 2), 'mph'];
            } else {
                // Transform m/s to km/s
                return [round($data * 3600 / 1000, 2), 'km/h'];
            }
        }
    }
}
?>