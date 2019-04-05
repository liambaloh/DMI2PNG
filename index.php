<?php

	require_once "libs.php";

	$path = isset($argv[1]) ? $argv[1] : null;

	if (!$path) {
		die("Usage: $argv[0] [path]\n");
	} elseif (!file_exists($path)) {
		die("Path not found: $path\n");
	}

	if (is_dir($path)) {
		print "Recursively converting starting at $path ...\n";
		recursiveConvert($path);
		print "\n----------------------\nDone.\n";

	} elseif (is_file($path)) {
		if (substr($path, -4) !== ".dmi") {
			die("Not DMI file? $path\n");
		}
		echo "Converting $path ...\n";
		$test	= new BYOND\DMI($path);
		$test->loadImage();
		$test->convertToPNGs();
		print "\n----------------------\nDone.\n";
	}

	die();

	$test	= new BYOND\DMI("in/lunar.dmi");
	$test->loadImage();
	$test->convertToPNGs();
	die("\n\n");


	function recursiveConvert($path) {
		if (is_file($path)) {
			convertDMI($path);
			return;
		}

		if (substr($path, -1) !== "/") {
			$path	.= "/";
		}

		$files	= scandir($path);

		foreach ($files as $file) {
			if ($file === "." || $file === "..") continue;
			if (substr($file, -4) === ".dmi") {
				convertDMI($path . $file);
			} elseif (is_dir($path . $file)) {
				recursiveConvert($path . $file);
			}
		}

	}


	function convertDMI($path) {
		if (is_file($path)) {
			if (substr($path, -4) !== ".dmi") {
				die("Not DMI file? $path\n");
			}
			echo "Converting $path ...\n";
			$test	= new BYOND\DMI($path);
			$test->loadImage();
			$test->convertToPNGs();
		}
	}
