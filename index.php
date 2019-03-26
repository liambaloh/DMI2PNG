<?php

	require_once "libs.php";

	$test	= new BYOND\DMI("in/lunar.dmi");
	$test->loadImage();
	$test->convertToPNGs();
	die("\n\n");
