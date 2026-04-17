<?php
	enum Client {
		case C2013;
		case C2016;
		case Unknown;

		public function ordinal(): string {
			return match($this) {
				Client::C2012   => "2012", // TODO add 2012 and 2018
				Client::C2013 	=> "2013",
				Client::C2016	=> "2016",
				Client::C2018   => "2018", //! there will be 2018 soon
			};
		}
	}

	class ClientDetector {

		public static function DetectClient(): Client {
			if(str_contains($_SERVER['HTTP_USER_AGENT'], "ANORRLStudio")) {
				return Client::C2016;
			}
			else if(str_contains($_SERVER['HTTP_USER_AGENT'], "RobloxStudio/2013. 8. 13. 2") || str_contains($_SERVER['HTTP_USER_AGENT'], "ANORRL/13nInet")) {
				return Client::C2013;
			}
			
			return Client::Unknown;
		}

	}
?>