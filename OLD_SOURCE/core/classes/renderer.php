<?php 

	ini_set("default_socket_timeout", 60);

	require_once $_SERVER['DOCUMENT_ROOT']."/core/utilities/userutils.php";

	$directory = $_SERVER['DOCUMENT_ROOT']."/core/Assemblies/Roblox/Grid/Rcc/";
	$scanned_directory = array_diff(scandir($directory), array('..', '.'));

	foreach($scanned_directory as $file) {
		if(str_contains($file, "wsdl")) {
			continue;
		}
		require $directory.$file;
	}

	$settings = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/settings.env", true);
	


	class TheFuckingRenderer {
		public static string $arbiter_ip = ""; //! important
		public static string $arbiter_token = "";
		public static bool $cantuserenderer = false;

		private static function RequestA(string $endpoint, array $data) {
			self::UpdateAndSetConfig();
			$arb_ip = self::$arbiter_ip;
			$arb_token = self::$arbiter_token;
			$ch = curl_init("http://$arb_ip" . $endpoint);

			curl_setopt_array($ch, [
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_HTTPHEADER => [
					"roblox-server-authorization: $arb_token",
					"Content-Type: application/json",
					"User-Agent: ANORRL/1.0"
				],
				CURLOPT_TIMEOUT => 60
			]);

			$response = curl_exec($ch);

			if ($response === false) {
				curl_close($ch);
				return null;
			}

			curl_close($ch);
			$json = json_decode($response, true);
			return $json;
		}

		private static function UpdateAndSetConfig() {
			$settings = parse_ini_file($_SERVER['DOCUMENT_ROOT']."/settings.env", true);
			$renderer_settings = $settings['renderer'];
			if(self::$cantuserenderer != boolval($renderer_settings['DISABLED'])) {
				self::$cantuserenderer = boolval($renderer_settings['DISABLED']);
			}
			if(self::$arbiter_ip != $settings['renderer']['LOC']) {
				self::$arbiter_ip = $settings['renderer']['LOC'];
			}
			if(self::$arbiter_token != $settings['renderer']['token']) {
				self::$arbiter_token = $settings['renderer']['token'];
			}
		}

		private static function GetAvatarRenderPayload(int $id, bool $headshot) {
			$user = User::FromID($id);
			if($user == null) return null;

			$colors = $user->GetBodyColours();
			$wearing = $user->GetWearingArray();
			$assets = [];
			foreach($wearing as $assetId) {
				$asset = Asset::FromID($assetId);
				if($asset) {
					$assets[] = [
						"id" => $assetId,
						"assetType" => ["id" => $asset->type->ordinal()]
					];
				}
			}

			$methodName = $headshot ? "GenerateThumbnailHeadshot" : "GenerateThumbnail";
			
			return [
				"method" => $methodName,
				"arguments" => [[
					"userId" => $id,
					"playerAvatarType" => "R6",
					"scales" => [],
					"bodyColors" => [
						"headColorId" => intval($colors['head']),
						"torsoColorId" => intval($colors['torso']),
						"rightArmColorId" => intval($colors['rightarm']),
						"leftArmColorId" => intval($colors['leftarm']),
						"rightLegColorId" => intval($colors['rightleg']),
						"leftLegColorId" => intval($colors['leftleg'])
					],
					"assets" => $assets
				]]
			];
		}

		public static function RenderPlayer(int $id = 0) {
			self::UpdateAndSetConfig();
			if(self::$cantuserenderer) return null;
			$payload = self::GetAvatarRenderPayload($id, false);
			if(!$payload) return null;
			return self::RequestA("/api/public-method", $payload);
		}

		public static function RenderUser(int $id = 0, bool $headshot = false) {
			self::UpdateAndSetConfig();
			if(self::$cantuserenderer) return null;
			$payload = self::GetAvatarRenderPayload($id, $headshot);
			if(!$payload) return null;
			return self::RequestA("/api/public-method", $payload);
		}

		public static function RenderMesh(int $id = 0) {
			self::UpdateAndSetConfig();
			if(self::$cantuserenderer) return null;
			return self::RequestA("/api/public-method", [
				"method" => "GenerateThumbnailMesh",
				"arguments" => [$id]
			]);
		}

		public static function RenderPlace(int $id = 0) {
			self::UpdateAndSetConfig();
			if(self::$cantuserenderer) return null;
			return self::RequestA("/api/public-method", [
				"method" => "GenerateThumbnailGame",
				"arguments" => [$id]
			]);
		}

		public static function RenderModel(int $id = 0) {
			self::UpdateAndSetConfig();
			if(self::$cantuserenderer) return null;
			return self::RequestA("/api/public-method", [
				"method" => "GenerateThumbnailAsset",
				"arguments" => [$id]
			]);
		}
	}
?>