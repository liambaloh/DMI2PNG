<?php


	namespace BYOND\DMI;

	class State {

		protected $dmi		= null;
		protected $name		= null;
		protected $data		= [
					'dirs'		=> null, 
					'frames'	=> null, 
					'delay'		=> null, 
					'loop'		=> null, 
					'hotspot'	=> null, 
					'movement'	=> null, 
					'rewind'	=> null,
				];

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
					$this->data[$key]	= $value;
				} else {
					throw new \UnexpectedValueException("Unknown/unexpected state definition, key '$key' with value '$value'");

				}
			}
		}

	}
