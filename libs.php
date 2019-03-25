<?php

	require_once "lib/GifCreator.php";
	require_once "lib/PNGMetadataExtractor.php";

	require_once "lib/byond/dmi.php";
	require_once "lib/byond/state.php";


	function startsWith($haystack, $needle) {
		// search backwards starting from haystack length characters from the end
		return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
	}
	function endsWith($haystack, $needle) {
		// search forward starting from end minus needle length characters
		return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
	}

