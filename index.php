<?php


    require_once "lib/GifCreator.php";
    require_once "lib/PNGMetadataExtractor.php";

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }
    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }


    $spritesPerLoad = 1000;
    $dmiFiles = Array();
    $dir = new DirectoryIterator("in");
    foreach ($dir as $fileinfo) {
        if (!$fileinfo->isDot()) {
            $fileName = $fileinfo->getFilename();
    		$ext = pathinfo($fileName, PATHINFO_EXTENSION);
    		if($ext == "dmi"){
    			$dmiFiles[] = $fileName;
    		}
        }
    }

    print "<body style='background-color: #888888;'></body>";
    $verbose = false;

    foreach($dmiFiles as $i => $inFile){
    	//if($verbose){
    	print "<h1>$inFile</h1>";
    	//}

    	$metadata = PNGMetadataExtractor::getMetadata("in/".$inFile);
    	$dmi = $metadata["text"]["ImageDescription"]["x-default"];
    	$metadata = explode("\n", trim($dmi));

    	$width = 32;
    	$height = 32;
    	$version = 0;

    	$state = "";
    	$dirs = "";
    	$frames = "";
    	$delay = "";
    	$loop = "";
    	$hotspot = "";
    	$rewind = "";
    	$movement = "";
    	$first = true;

    	$sprites = Array();

    	foreach($metadata as $i => $line){
    		$line = trim($line);
    		$dataPair = explode("=", $line);
    		if(count($dataPair) != 2){
    			continue;
    		}
    		//print "<br>$key - $value";
    		
    		$key = trim($dataPair[0]);
    		$value = trim($dataPair[1]);
    		
    		if($key == "version"){
    			$version = intval($value);
    			continue;
    		}
    		if($key == "width"){
    			$width = intval($value);
    			continue;
    		}
    		if($key == "height"){
    			$height = intval($value);
    			continue;
    		}
    		if($key == "state"){
    			if($first){
    				$first = false;
    			}else{
    				$sprites[] = Array(
    					"state" => $state, 
    					"dirs" => $dirs, 
    					"frames" => $frames, 
    					"delay" => $delay, 
    					"loop" => $loop, 
    					"hotspot" => $hotspot, 
    					"movement" => $movement, 
    					"rewind" => $rewind
    				);
    			}
    			$state = "";
    			$dirs = "";
    			$frames = "";
    			$delay = "";
    			$loop = "";
    			$rewind = "";
    			$hotspot = "";
    			$movement = "";
    			if(startsWith($value, '"')){
    				$value = substr($value, 1);
    			}
    			if(endsWith($value, '"')){
    				$value = substr($value, 0, strlen($value) -1);
    			}
    			$state = $value;
    			continue;
    		}
    		if($key == "dirs"){
    			$dirs = $value;
    			continue;
    		}
    		if($key == "frames"){
    			$frames = $value;
    			continue;
    		}
    		if($key == "loop"){
    			$loop = $value;
    			continue;
    		}
    		if($key == "delay"){
    			$delay = $value;
    			continue;
    		}
    		if($key == "hotspot"){
    			$hotspot = $value;
    			continue;
    		}
    		if($key == "movement"){
    			$movement = $value;
    			continue;
    		}
    		if($key == "rewind"){
    			$rewind = $value;
    			continue;
    		}
    		print "<br><font color='red'>$key - $value</font>";
    	}

    	if(!$first){
    		$sprites[] = Array(
    					"state" => $state, 
    					"dirs" => $dirs, 
    					"frames" => $frames, 
    					"delay" => $delay, 
    					"loop" => $loop, 
    					"hotspot" => $hotspot, 
    					"movement" => $movement, 
    					"rewind" => $rewind
    				);
    	}

    	//print_r($sprites);

    	//Load image
    	$image = imagecreatefrompng("in/".$inFile);
    	imagesavealpha($image, true);
    	$trans_colour = imagecolorallocatealpha($image, 0, 0, 0, 127);
    	imagefill($image, 0, 0, $trans_colour);
    	$imageWidth = imagesx($image);
    	$imageHeight = imagesy($image);
    	$spritesX = $imageWidth / $width;
    	$spritesY = $imageHeight / $height;

    	$folderName = str_replace(".dmi", "", $inFile);
    	$folderName = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $folderName);
    	//$folderName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $folderName);
    	//$folderName = preg_replace("([\.]{2,})", '', $folderName);
    	$folderName = "out/".$folderName;
    	if($verbose){
    		print "<h2>$folderName</h2>";
    	}

    	if(!file_exists("out")){
    		mkdir("out");
    	}
    	if(!file_exists("$folderName")){
    		mkdir("$folderName");
    	}


    	$spriteNumber = 0;
    	foreach($sprites as $i => $spriteData){
    		$spriteName = $spriteData["state"];
    		$spriteName = preg_replace('/[^A-Za-z0-9 _ .-]/', '', $spriteName);
    		//$spriteName = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $spriteName);
    		//$spriteName = preg_replace("([\.]{2,})", '', $spriteName);
    		if($verbose){
    			print "<h3>$spriteName</h3>";
    		}
    		
    		
    		$frames = intval($spriteData["frames"]);
    		$dirs = intval($spriteData["dirs"]);
    		$rewind = intval($spriteData["rewind"]);
    		if(!$frames){
    			$frames = 1;
    		}
    		if(!$dirs){
    			$dirs = 1;
    		}
    		if(!$rewind){
    			$rewind = 0;
    		}
    		
    		//print "<h2>Sprite: $spriteName</h2>";
    		for($dir = 0; $dir < $dirs; $dir++){
    			//print "<h3>Dir: $dir (spriteNum = $spriteNumber)</h3>";
    			if($frames == 1){	
    				$sprite = imagecreatetruecolor($width, $height);
    				imagesavealpha($sprite, true);
    				$trans_colour = imagecolorallocatealpha($sprite, 0, 0, 0, 127);
    				imagefill($sprite, 0, 0, $trans_colour);
    				
    				$posX = ($spriteNumber + $dir) % $spritesX;
    				$posY = floor(($spriteNumber + $dir) / $spritesX);
    				//print "<br>posX = $posX; posY = $posY<br>";
    				
    				imagecopy($sprite, $image, 0, 0, $posX * $width, $posY * $height, $width, $height);
    				imagepng($sprite, "$folderName/$spriteName"."_$dir.png");
    				print "<img src='$folderName/$spriteName"."_$dir.png' alt='$spriteName'>";
    			}else{
    				$gifFrameList = Array();
    				for($frameNum = 0; $frameNum < $frames; $frameNum++){
    					$sprite = imagecreatetruecolor($width, $height);
    					imagesavealpha($sprite, true);
    					$trans_colour = imagecolortransparent($sprite, 127<<24);
    					imagefill($sprite, 0, 0, $trans_colour);
    					
    					$posX = ($spriteNumber + ($dirs * $frameNum) + $dir) % $spritesX;
    					$posY = floor(($spriteNumber + ($dirs * $frameNum) + $dir) / $spritesX);
    					//print "<br>posX = $posX; posY = $posY<br>";
    					imagecopy($sprite, $image, 0, 0, $posX * $width, $posY * $height, $width, $height);
    					if(!file_exists("$folderName/$spriteName"."_$dir")){
    						mkdir("$folderName/$spriteName"."_$dir");
    					}
    					imagepng($sprite, "$folderName/$spriteName"."_$dir/$frameNum.png");
    					print "<img src='$folderName/$spriteName"."_$dir/$frameNum.png' alt='$spriteName'>";
    					$gifFrameList[] = $sprite;
    				}
    				
    				$delayList = Array();
    				$delay = $spriteData["delay"];
    				$delays = explode(",", $delay);
    				foreach($delays as $i => $dl){
    					$dl = intval(trim($dl));
    					$delayList[] = $dl * 10;
    				}
    				
    				if($rewind){
    					//print "REWIND";
    					for($i = count($gifFrameList) - 2; $i > 0; $i--){
    						$gifFrameList[] = $gifFrameList[$i];
    					}
    					for($i = count($delayList) - 2; $i > 0; $i--){
    						$delayList[] = $delayList[$i];
    					}
    					//print_r($gifFrameList);
    				}
    				
    				$gc = new GifCreator();
    				$gc->create($gifFrameList, $delayList, 0);
    				$gifBinary = $gc->getGif();
    				file_put_contents("$folderName/$spriteName"."_$dir.gif", $gifBinary);
    				//print "<br><b>Animated: </b>";
    				print "<img src='$folderName/$spriteName"."_$dir.gif' alt='$spriteName'>";
    			}
    		}
    		$spriteNumber += $frames * $dirs;
    	}
    	
    	if(!file_exists("done")){
    		mkdir("done");
    	}
    	copy("in/".$inFile, "done/".$inFile);
    	unlink("in/".$inFile);
    	
    	$spritesPerLoad -= $spriteNumber;
    	if($spritesPerLoad < 0){
    		break;
    	}
    }
