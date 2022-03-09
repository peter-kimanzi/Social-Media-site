<?php

$db = new mysqli($CONF['host'], $CONF['user'], $CONF['pass'], $CONF['name']);
if ($db->connect_errno) {
    echo "Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error;
}
$db->set_charset("utf8mb4");

// Verify whether the user imported the database or not
$resultSettings = $db->query(getSettings()); 
if($resultSettings) {
	$settings = $resultSettings->fetch_assoc();
} else {
	echo "Error: ".$db->error;
}