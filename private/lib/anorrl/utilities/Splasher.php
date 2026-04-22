<?php

	namespace anorrl\utilities;

	class Splasher {

		public array $splashes;
		public bool $true_random = true;
		public string $name = "";

		function __construct(array $splashes, bool $true_random = true, string $name = "") {
			$this->true_random = $true_random;
			$this->splashes = array_values(array_filter($splashes, fn($splash) => is_string($splash) && strlen(trim($splash)) != 0));

			if(!$this->true_random && strlen(trim($name)) != 0) {
				$this->name = "ANORRL\$Splashes\${$name}";
			}

			if(count($this->splashes) > 1) {
				shuffle($this->splashes);
			}
		}

		private function roll() {
			if($this->true_random || strlen($this->name) == 0)
				return;

			if(session_status() == PHP_SESSION_NONE)
				session_start();
			
			if(!isset($_SESSION[$this->name])) {
				$_SESSION[$this->name] = $this->splashes;
			}

			$session_splashes = $_SESSION[$this->name];
			
			if(count($session_splashes) == 0) {
				$_SESSION[$this->name] = $this->splashes;
				$session_splashes = $_SESSION[$this->name];
			}
			
			if(count($session_splashes) != 1) {
				$rand_splash = $session_splashes[0];
				array_splice($_SESSION[$this->name], 0, 1);
			} else {
				$rand_splash = end($session_splashes);
				$_SESSION[$this->name] = $this->splashes;
			}

			return $rand_splash;
		}

		function getRandomSplash() {
			if(count($this->splashes) == 0) {
				return "";
			}

			if(!$this->true_random && strlen($this->name) > 0)
				return $this->roll();
			else
				return $this->splashes[array_rand($this->splashes)];
		}
	}
?>
