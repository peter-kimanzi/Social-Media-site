<?php
// Set the header
header("Content-type: image/png");

// Start the session
session_start();

// The string to select random characters
$seed = str_split('abcdefghijklmnopqrstuvwxyz0123456789');

// Randomize the characters
shuffle($seed);

// Select the first n characters
$text = null;
for($i = 1; $i <= 6; $i++) {
	$text .= $seed[$i];
}
$text = mt_rand(100000, 999999);
// Explode the letters into separate array elements
$letters = str_split($text);

// Store the generated code into the _SESSION captcha
$_SESSION['captcha'] = $text;
 
// Define the Image Height & Width
$width = 75;
$height = 37;  

// Create the Image
$image = imagecreate($width, $height); 

// Set the background color
$black = imagecolorallocate($image, 255, 255, 255);
// Set the text color
$white = imagecolorallocate($image, 0, 0, 0);

$lg_color = mt_rand(130, 160);
$light_gray = imagecolorallocate($image, $lg_color, $lg_color, $lg_color);

// Set the font size
$font_size = 1; 

// Draw background circles
for($i = 0; $i < 1; $i++) {
	// The outside circle diameter
	$outside = 60-$i*20;

	// The inside circle diamater
	$inside = 59-$i*20;

	// Randomize the horizontal position and vertical position
	$oc = array(mt_rand(30, 40), mt_rand(10, 20));

	// Draw the outer circle
	imagefilledellipse($image, $oc[0], $oc[1], $outside, $outside, $white);
	
	// Draw the inner circle
	imagefilledellipse($image, $oc[0], $oc[1], $inside, $inside, $black);
}

// Generate noise
for($noise = 0; $noise <= 15; $noise++) {
	$x = mt_rand(10, $width-10);
	$y = mt_rand(10, $height-10);
	imageline($image, $x, $y, $x, $y, $white);
}

function generateStars($image, $color, $repeat_x, $repeat_y) {
	$ry = mt_rand(3, 6);
	$one_y = $ry-2;
	$two_y = $ry-1;
	$three_y = $ry;
	for($x = 1; $x <= $repeat_y; $x++) {
		$one_x = $ry-2;
		$two_x = $ry-1;
		$three_x = $ry;
		// Generate horizontal lines
		for($n = 1; $n <= $repeat_x; $n++) {
			imageline($image, $one_x, $one_y, $one_x, $one_y, $color);
			imageline($image, $three_x, $three_y, $three_x, $three_y, $color);
			imageline($image, $three_x, $one_y, $three_x, $one_y, $color);
			imageline($image, $one_x, $three_y, $one_x, $three_y, $color);
			imageline($image, $two_x, $two_y, $two_x, $two_y, $color);
			$one_x = $one_x+8;
			$two_x = $two_x+8;
			$three_x = $three_x+8;
		}
		$one_y = $one_y+8;
		$two_y = $two_y+8;
		$three_y = $three_y+8;
	}
}
generateStars($image, $light_gray, 15, 5);

// Letter position
$position = array(8, 18, 28, 38, 48, 58);

for($i = 0; $i < count($letters); $i++) {
	// Generate an rgb random value, from light gray to white
	$color = rand(0, 150);
	
	// Output the letters
	imagestring($image, 5, $position[$i], mt_rand(9, 11), $letters[$i], imagecolorallocate($image, $color, $color, $color));
}

// Generate random vertical and horizontal lines
imageline($image, 0, mt_rand(10, $height-10), $width, mt_rand(10, $height-10), $light_gray);
imageline($image, mt_rand(15, $width-15), 0, 0, mt_rand(15, $width-15), $light_gray);

// Output the $image, don't save the file name, set quality
imagepng($image, null, 9);
?>