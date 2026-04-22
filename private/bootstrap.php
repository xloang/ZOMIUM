<?php
	if(!function_exists('anorrl_bootstrap_normalize_path')) {
		function anorrl_bootstrap_normalize_path(string $path): string {
			$resolved = realpath($path);
			$value = $resolved !== false ? $resolved : $path;

			return rtrim(str_replace("\\", "/", $value), "/");
		}
	}

	if(!function_exists('anorrl_bootstrap_merge')) {
		function anorrl_bootstrap_merge(mixed $defaults, mixed $overrides): mixed {
			if($overrides === null) {
				return $defaults;
			}

			if(is_object($defaults) && is_object($overrides)) {
				foreach(get_object_vars($overrides) as $key => $value) {
					$defaults->$key = property_exists($defaults, $key)
						? anorrl_bootstrap_merge($defaults->$key, $value)
						: $value;
				}

				return $defaults;
			}

			return $overrides;
		}
	}

	if(!defined('APP_ROOT')) {
		define('APP_ROOT', anorrl_bootstrap_normalize_path(dirname(__DIR__)));
	}

	if(!defined('APP_DATA_ROOT')) {
		$parent = realpath(APP_ROOT . DIRECTORY_SEPARATOR . '..');
		define('APP_DATA_ROOT', $parent !== false ? anorrl_bootstrap_normalize_path($parent) : APP_ROOT);
	}

	$documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? APP_ROOT;
	$_SERVER['DOCUMENT_ROOT'] = anorrl_bootstrap_normalize_path((string)$documentRoot ?: APP_ROOT);

	if(!defined('CONFIG')) {
		$configPaths = [
			APP_ROOT . '/settings.json',
			APP_DATA_ROOT . '/settings.json'
		];

		$config = null;
		$checkedPaths = [];

		foreach($configPaths as $configPath) {
			$checkedPaths[] = $configPath;

			if(!is_file($configPath)) {
				continue;
			}

			$contents = file_get_contents($configPath);
			if($contents === false) {
				throw new RuntimeException("Unable to read settings.json at {$configPath}.");
			}

			$decoded = json_decode($contents);
			if(!is_object($decoded)) {
				throw new RuntimeException("Invalid JSON in settings.json at {$configPath}.");
			}

			$config = $decoded;
			break;
		}

		if(!$config) {
			throw new RuntimeException(
				"settings.json was not found. Checked: " . implode(', ', $checkedPaths)
			);
		}

		$defaults = json_decode('{
			"database": {
				"hostname": "localhost",
				"username": "root",
				"password": "",
				"name": "",
				"port": 3306
			},
			"asset": {
				"key": "",
				"webhook": "",
				"canforward": false,
				"roblosec": ""
			},
			"arbiter": {
				"location": {
					"private": "",
					"public": ""
				},
				"token": "",
				"disabled": false
			},
			"domain": "localhost"
		}');

		define('CONFIG', anorrl_bootstrap_merge($defaults, $config));
	}

	$GLOBALS['__config'] = CONFIG;
?>
