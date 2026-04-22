<?php

	namespace anorrl\utilities;

	class FileSplasher extends Splasher {
		
		function __construct(string $filename, bool $true_random = true, string $name = "") {
			$path = $_SERVER["DOCUMENT_ROOT"]."/private/splashes/$filename.txt";
			$splashes = is_file($path) ? file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
			parent::__construct($splashes ?: [], $true_random, $name);
		}
	}

?>
