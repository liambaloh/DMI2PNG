<?php


	namespace BYOND;

	class DMI {

		protected $filename		= null;
		protected $version		= null;
		protected $width		= 32;
		protected $height		= 32;
		protected $states		= [];
		protected $unnamed		= 0;

		protected $image		= null;
		protected $dimensions	= [];


		public function __construct($filename) {
			$this->filename	= $filename;
			$this->parseMetadata();
		}


		protected function parseMetadata() {

			$metadata	= \PNGMetadataExtractor::getMetadata($this->filename);
			$dmi		= $metadata["text"]["ImageDescription"]["x-default"];
			$metadata	= preg_split("/\n(?!\t)/m", $dmi, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
			foreach ($metadata as $i => $block) {

				$block	= trim($block);
				if ($block === "# BEGIN DMI") {
					print "  DMI information start marker -------\n";
					continue;
				} elseif ($block === "# END DMI") {
					print "  DMI information end marker ---------\n";
					continue;
				}

				// At this point, first line is the type, other lines are indented data
				list($head, $body)	= explode("\n", $block, 2);
				list($key, $value)	= array_map("trim", explode("=", $head, 2));

				switch ($key) {
					case "version":
						print "  DMI version info: $value\n";
						$this->version	= $value;
						$this->parseDMIMetadata($body);
						break;

					case "state":
						$value	= trim($value, '"');
						if ($value === "") {
							$value	= "(unnamed {$this->unnamed})";
							$this->unnamed++;
						}

						print "  New state: $value\n";
						$value	= preg_replace("#[\x01-\x1f<>:\"/\\|?*]#", '_', $value);
						$test	= $value;
						$tries	= 1;
						while (array_key_exists($test, $this->states)) {
							$test	= "$value ($tries)";
							$tries++;
						}
						$value	= $test;


						$this->states[$value]	= new DMI\State($this, $value, $body);
						break;
				}

			}

		}


		protected function parseDMIMetadata($data) {
			$lines	= explode("\n", $data);

			foreach ($lines as $line) {
	
				list($key, $value)	= array_map("trim", explode("=", $line, 2));

				if ($key === "width" || $key === "height") {
					$this->$key	= $value;
					print "    DMI metadata: $key = $value\n";
	
				} else {
					throw new \UnexpectedValueException("Unknown/unexpected DMI metadata, key '$key' with value '$value'");

				}
			}
		}


		public function loadImage() {
			$this->image		= imagecreatefrompng($this->filename);
			$this->dimensions	= [
				'w'		=> imagesx($this->image),
				'h'		=> imagesy($this->image),
				];

			$this->dimensions['sx']	= $this->dimensions['w'] / $this->width;
			$this->dimensions['sy']	= $this->dimensions['h'] / $this->height;

		}

		public function getSpriteNumber($n) {
			$posX	= ($n % $this->dimensions['sx']) * $this->width;
			$posY	= (floor($n / $this->dimensions['sx'])) * $this->height;

			$ret	= $this->getEmptyImage();
			imagecopymerge($ret, $this->image, 0, 0, $posX, $posY, $this->width, $this->height, 100);
			return $ret;
		}


		public function convertToPNGs() {

			$baseOutDir	= str_replace(".dmi", "/", $this->filename);
			if (!file_exists($baseOutDir)) {
				mkdir($baseOutDir);
			}

			$spriteNumber	= 0;
			foreach ($this->states as $name => $state) {

				$outDir		= $baseOutDir;
				if ($state->data['dirs'] > 1 || $state->data['frames'] > 1) {
					$outDir	.= "$name/";
					if (!file_exists($outDir)) {
						mkdir($outDir);
					}
				}

				// TODO fix: each animation state is handled first, *then* the direction
				// rather than d1[f1,f2,f3,f4] d2[...] it seems to be f1[d1,d2,d3,d4] f2[...]
				// shit

				$baseSpriteNumber	= $spriteNumber;

				for ($dir = 1; $dir <= $state->data['dirs']; $dir++) {
					$outNameD	= "";
					$outNameF	= "";
					if ($state->data['dirs'] > 1) {
						$outNameD	= "-dir$dir";
					}
					$frameImages	= [];
					$APNG			= null;
					if ($state->data['frames'] > 1) {
						$APNG	= new \APNG_Creator();
						if ($state->data['loop']) $APNG->play_count = $state->data['loop'];
					}

					for ($frame = 1; $frame <= $state->data['frames']; $frame++) {

						$spriteNumber	= $baseSpriteNumber + ($frame - 1) * $state->data['dirs'] + ($dir - 1);

						if ($state->data['frames'] > 1) {
							$outNameF	= "-fr$frame";
						}
						$outputFile	= "$outDir$name$outNameD$outNameF.png";

						printf("    %04d ", $spriteNumber);
						print "$outputFile\n";

						$sprite	= $this->getSpriteNumber($spriteNumber);
						imagepng($sprite, $outputFile);
	
						if ($APNG) {
							$APNG->add_image($sprite, "MIDDLE_CENTER", $state->data['delay'][$frame - 1] * 100, 'APNG_DISPOSE_OP_BACKGROUND');
						}

						$frameImages[]	= $sprite;


					}

					if ($APNG) {
						$outputFile	= "$outDir$name$outNameD.png";
						$APNG->save($outputFile);
						print "       anim $outputFile\n";
					}

					foreach ($frameImages as $delete) {
						imagedestroy($delete);
					}


				}

				$spriteNumber	= $baseSpriteNumber + $state->data['dirs'] * $state->data['frames'];

			}

		}


		protected function getEmptyImage() {
			$i		= imagecreatetruecolor($this->width, $this->height);
			imagealphablending($i, false);
			imagesavealpha($i, true);
			imagefilledrectangle($i, 0, 0, $this->width, $this->height, imagecolorallocatealpha($i, 0, 0, 0, 127));
			return $i;
		}

	}
