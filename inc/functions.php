<?php
namespace slc;

define('TERM_SPRING',   10);
define('TERM_SUMMER1',  20);
define('TERM_SUMMER2',  30);
define('TERM_FALL',     40);

/**
 * encode
 * 
 * Use Blowfish encryption to hash the banner ID so a unique
 * but anonymous client is stored
 * 
 * @param int $banner
 * @throws BannerNotDefinedException
 */
function encode($banner = null) {
	// Hash with banner, banner, and term (or year?)
	
	//return $banner;
	
	if (!isset($banner)) 
		throw new \BannerNotDefinedException('Missing Banner ID');
		
	$hashString  = $banner;
	
	$encode_salt = \PHPWS_Settings::get('slc', 'encode_salt');
	
    $blowfish_salt = "$2a$07$".$encode_salt."$";
    return crypt($hashString, $blowfish_salt);
}


/**
 * problemLink
 * 
 * Create an ajax link to add problems based on paramaters
 * 
 * @return string $htmlLink
 * 
 * note: these links are LIVE
 */

function problemLink( $text, $id ) {
	$html = "<span id='PROBLEM_".$id."' name='".urlencode($text)."'>".htmlentities($text)."</span>";
	return $html;
}


function prettyAccess($timestamp) {
	// Get difference
	$difference = timestamp() - $timestamp;
	
	// Adjust to scale of minutes
	$difference /= 60;
	
	if ( $difference < 1 )
		return "a few seconds ago";
	
	// determine weeks
	$weeks = floor($difference / (60 *  24 * 7))/1;
	
	$difference -= $weeks * 60 * 24 * 7;
	
	// determine days
	$days = floor($difference / (60 * 24));
	
	$difference -= $days * 60 * 24;
	
	// determine hours
	$hours = floor($difference / (60));
	
	$difference -= $hours * 60;

	// determine minutes
	$minutes = floor($difference);	
	
	// determine days
	$days = floor($difference / (60 * 24));

	if ( $days == 0 && $weeks == 0 ) {
		$minutes = $minutes > 0 ? $minutes . " " . pluralize("minute", $minutes) . " " : "";
		$hours = $hours > 0 ? $hours . " " . pluralize("hour", $hours) . " " : "";
	} else
		$minutes = $hours = "";
	$days = $days > 0 ? $days . " " . pluralize("day", $days) . " " : "";
	$weeks = $weeks > 0 ? $weeks . " " . pluralize("week", $weeks) . " " : "";

	return $weeks . $days . $hours . $minutes . "ago";
}

function pluralize( $word, $number ) {
	if ( $number > 1 )
		return $word ."s";
	else
		return $word;
}


function timestamp() {
	return time();
}

function prettyTime($timestamp) {
	return date('l, F jS, Y', intval($timestamp));
}

// http://webcache.googleusercontent.com/search?q=cache:mWlWJfzCE4IJ:stackoverflow.com/questions/834303/php-startswith-and-endswith-functions+php+startswith&cd=2&hl=en&ct=clnk&gl=us
function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    $start  = $length * -1; //negative
    return (substr($haystack, $start) === $needle);
}

?>
