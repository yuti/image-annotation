<?php

if(file_exists('../../../wp-config.php') ) {
	include_once('../../../wp-config.php' );
} else {
	$wpDir = realpath("../../../");
	define('ABSPATH', $wpDir.'/');
	
	if(file_exists($wpDir.'/wp-config.php') ) {
		include_once($wpDir.'/wp-config.php' );
	} else {
		$parentDir = realpath("{$wpDir}/..");
		if(file_exists($parentDir.'/wp-config.php') && !file_exists($parentDir.'/wp-settings.php')) {
			include_once($parentDir.'/wp-config.php');
		}
		else {
			echo "Error loading wp-config.php";
		}
		unset($parentDir);
	}
	unset($wpDir);
}
unset($wpDir);
