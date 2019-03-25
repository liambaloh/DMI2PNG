<?php

	require_once "libs.php";

	$test	= new BYOND\DMI("in/lunar.dmi");
	die("\n\n");




	$dmiFiles = [];
	$dir = new DirectoryIterator("in");

	foreach ($dir as $fileinfo) {
		if (!$fileinfo->isDot()) {
			$fileName	= $fileinfo->getFilename();
			$ext		= pathinfo($fileName, PATHINFO_EXTENSION);
			if ($ext == "dmi") {
				$dmiFiles[]	= $fileName;
			}
		}
	}

	foreach ($dmiFiles as $i => $inFile) {

		//Load image
		$image			= imagecreatefrompng("in/".$inFile);
		imagesavealpha($image, true);
		$trans_colour	= imagecolorallocatealpha($image, 0, 0, 0, 127);
		imagefill($image, 0, 0, $trans_colour);
		$imageWidth		= imagesx($image);
		$imageHeight	= imagesy($image);
		$spritesX		= $imageWidth / $width;
		$spritesY		= $imageHeight / $height;
		
		$folderName		= str_replace(".dmi", "", $inFile);
		$folderName		= preg_replace('/[^A-Za-z0-9 _ .-]/', '', $folderName);
		$folderName		= "out/".$folderName;

		if (!file_exists("out")) {
			mkdir("out");
		}
		if (!file_exists("$folderName")) {
			mkdir("$folderName");
		}


		$spriteNumber = 0;
		foreach ($sprites as $i => $spriteData) {
			$spriteName	= $spriteData["state"];
			$spriteName	= preg_replace('/[^A-Za-z0-9 _ .-]/', '', $spriteName);
			print "  $spriteName\n";
			
			
			$frames		= intval($spriteData["frames"]);
			$dirs		= intval($spriteData["dirs"]);
			$rewind		= intval($spriteData["rewind"]);
			if (!$frames) {
				$frames = 1;
			}
			if (!$dirs) {
				$dirs = 1;
			}
			if (!$rewind) {
				$rewind = 0;
			}
			
			for ($dir = 0; $dir < $dirs; $dir++) {
				print "    Dir: $dir (spriteNum $spriteNumber)\n";

				if ($frames == 1) {	
					$sprite			= imagecreatetruecolor($width, $height);
					imagesavealpha($sprite, true);
					$trans_colour	= imagecolorallocatealpha($sprite, 0, 0, 0, 127);
					imagefill($sprite, 0, 0, $trans_colour);
					
					$posX			= ($spriteNumber + $dir) % $spritesX;
					$posY			= floor(($spriteNumber + $dir) / $spritesX);
					
					imagecopy($sprite, $image, 0, 0, $posX * $width, $posY * $height, $width, $height);
					$outfile		= "$folderName/$spriteName"."_$dir.png";
					imagepng($sprite, $outfile);
					print "      $outfile\n";

				}else{
					$gifFrameList	= [];
					for ($frameNum = 0; $frameNum < $frames; $frameNum++) {
						$sprite			= imagecreatetruecolor($width, $height);
						imagesavealpha($sprite, true);
						$trans_colour	= imagecolortransparent($sprite, 127<<24);
						imagefill($sprite, 0, 0, $trans_colour);
						
						$posX			= ($spriteNumber + ($dirs * $frameNum) + $dir) % $spritesX;
						$posY			= floor(($spriteNumber + ($dirs * $frameNum) + $dir) / $spritesX);
						imagecopy($sprite, $image, 0, 0, $posX * $width, $posY * $height, $width, $height);
						if (!file_exists("$folderName/$spriteName"."_$dir")) {
							mkdir("$folderName/$spriteName"."_$dir");
						}
						$outfile		= "$folderName/$spriteName"."_$dir/$frameNum.png";
						imagepng($sprite, $outfile);
						print "      $outfile\n";
						$gifFrameList[]	= $sprite;
					}
					
					$delayList	= [];
					$delay		= $spriteData["delay"];
					$delays		= explode(",", $delay);
					foreach($delays as $i => $dl) {
						$dl				= intval(trim($dl));
						$delayList[]	= $dl * 10;
					}
					
					if ($rewind) {
						//print "REWIND";
						for ($i = count($gifFrameList) - 2; $i > 0; $i--) {
							$gifFrameList[]	= $gifFrameList[$i];
						}
						for ($i = count($delayList) - 2; $i > 0; $i--) {
							$delayList[]	= $delayList[$i];
						}
					}
					
					$gc			= new GifCreator();
					$gc->create($gifFrameList, $delayList, 0);
					$gifBinary	= $gc->getGif();
					$outfile	= "$folderName/$spriteName"."_$dir.gif";
					file_put_contents($outfile, $gifBinary);
					print "        $outfile\n";

				}
			}
			$spriteNumber	+= $frames * $dirs;
		}
		

	}
