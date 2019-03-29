<?php


	namespace BYOND\DMI;

	class State {

		public $name		= null;
		public $data		= [
					'dirs'		=> 1, 
					'frames'	=> 1, 
					'delay'		=> null, 
					'loop'		=> null, 
					'hotspot'	=> null, 
					'movement'	=> null, 
					'rewind'	=> 0,
				];

		protected $dmi		= null;


		public function __construct(\BYOND\DMI $dmi, $name, $data) {
			$this->dmi	= $dmi;
			$this->name	= $name;
			$this->parseData($data);
		}

		protected function parseData($data) {
			$lines	= explode("\n", $data);
			
			foreach ($lines as $line) {
			
				list($key, $value)	= array_map("trim", explode("=", $line, 2));

				if (array_key_exists($key, $this->data)) {
			
					switch ($key) {
						case "delay":
							$this->data[$key]	= explode(",", $value);
							break;
						default:
							$this->data[$key]	= $value;
							break;
					}
					print "    $key -> $value\n";

				} else {
					throw new \UnexpectedValueException("Unknown/unexpected state definition, key '$key' with value '$value'");

				}
			}
		}

	}
