<?php


	namespace BYOND;

	class DMI {

		protected $filename		= null;
		protected $image		= null;
		protected $version		= null;
		protected $width		= 32;
		protected $height		= 32;
		protected $states		= [];
		protected $unnamed		= 0;


		public function __construct($filename) {
			$this->filename	= $filename;
			$this->parseMetadata();
		}


		protected function parseMetadata() {

			$metadata	= \PNGMetadataExtractor::getMetadata($this->filename);
			$dmi		= $metadata["text"]["ImageDescription"]["x-default"];
			$metadata	= preg_split("/\n(?!\t)/m", $dmi, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			
			$states	= [];

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
						new DMI\State($this, $value, $body);
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

	}
