<?php

	namespace anorrl\utilities;

	class ClientDetector {
		
		public static function IsAClient(): bool {
			$user_agent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
			$user_agent_lower = strtolower($user_agent);

			return str_contains($user_agent, "ANORRLStudio") || 
				str_contains($user_agent_lower, "anorrl/wininet") ||
				str_contains($user_agent_lower, "anorrl/winhttp");
		}

		public static function HasAccess(): bool {
			$REQaccessKey = $_SERVER["HTTP_ACCESSKEY"] ?? null;
			return !($REQaccessKey !== $GLOBALS['__config']->asset->key);
		}

	}
?>
