<?php

require_once(__DIR__ . '/config.php');

session_set_cookie_params(null, COOKIE_PATH);
session_start();

require_once(__DIR__ . '/skins.php');
require_once(__DIR__ . '/classes.php');
require_once(__DIR__ . '/database.php');
require_once(__DIR__ . '/misc.php');
require_once(getLanguage(null, (isset($_GET['lang']) && !empty($_GET['lang']) ? $_GET['lang'] : (isset($_COOKIE['lang']) ? $_COOKIE['lang'] : null)), null));
$plugins = loadPlugins($db);
foreach($plugins as $plugin) {
	require_once(getLanguage(null, null, null, $plugin['name']));
}
$pluginsSettings = getPluginsSettings($db);
require_once(__DIR__ . '/../info.php');
require_once(__DIR__ . '/vendor/autoload.php');