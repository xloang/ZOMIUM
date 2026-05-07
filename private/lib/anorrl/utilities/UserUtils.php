<?php

	namespace anorrl\utilities;

	use anorrl\User;
	use anorrl\Database;

	/**
	 * Utilities for User stuff<br>
	 * Paging, Logging, Registering etc.
	 */
	class UserUtils {
		
		/**
		 * Creates a 255 long random strings from a character set to be used for the security of a user
		 * @return string Security key
		 */
		public static function GenerateSecurityKey(): string {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_?-/=;#!';
			$randomString = '';
			
			for ($i = 0; $i < 255; $i++) {
				$index = rand(0, strlen($characters) - 1);
				$randomString .= $characters[$index];
			}
	
			return $randomString;
		}

		/**
		 * Creates a user and does checks to ensure that all data given is correct.
		 * 
		 * If some data is invalid, it will return an array of the errors.
		 * @param string $username
		 * @param string $password
		 * @param string $confirm_password
		 * @param string $accesskey
		 * @return array|string
		 */
		public static function RegisterUser(string $username, string $password, string $confirm_password, string $accesskey): string|array {
			$errors = [];

			if(self::IsUsernameValid($username)) {
				if(!self::IsUsernameAvailable($username)) {
					$errors["username"] = "Username has already been taken!";
				}
			} else {
				$errors["username"] = "a-z A-Z 0-9 and 3-20 characters only!";
			}

			if(strlen($password) >= 7) {
				if(strcmp($password, $confirm_password) !== 0) {
					$errors["password"] = "Passwords do not match!";
				}
			} else {
				$errors["password"] = "Password must be minimum 7 characters!";
			}

			if(!self::IsValidKey($accesskey)) {
				$errors["accesskey"] = "Invalid access key.";
			}

			if(sizeof($errors) != 0) {
				return $errors;
			}

			$discordid = self::UseAccessKey($accesskey);
			$hashedpass = password_hash($password, PASSWORD_ARGON2ID);
			$securitykey = self::GenerateSecurityKey();
			
			if(Database::singleton()->run(
				"INSERT INTO `users`(`name`, `blurb`, `discord`, `password`, `security`) VALUES (:name,'',:discord,:password,:security);",
				[
					":name" => $username,
					":discord" => $discordid,
					":password" => $hashedpass,
					":security" => $securitykey
				]
			)->errorInfo()[0] == SQL_ALLOK) {
				self::SetCookies($securitykey);
				return "success"; // todo return ["error" => false] bc what the fuck is this
			}

			return ['unknown'=>"Something went wrong!"];
		}

		/**
		 * Verify details given and set cookies to allow logins.
		 * @param mixed $username
		 * @param mixed $password
		 * @return string|array
		 */
		public static function LoginUser(string $username, string $password): string|array {
			$errors = [];

			$pass_username = trim($username);
			$pass_password = trim($password);

			$pass_username_length = strlen($pass_username);
			$pass_password_length = strlen($pass_password);

			if($pass_username_length == 0) {
				$errors["username"] = "Username field cannot be empty!";
			} 
			else if(!preg_match("/^[a-zA-Z0-9]{3,20}$/", $pass_username)) {
				$errors["username"] = "a-z A-Z 0-9 and 3-20 characters only!";
			}

			if($pass_password_length == 0) {
				$errors["password"] = "Password field cannot be empty!";
			}

			if(sizeof($errors) != 0) {
				return $errors;
			}

			$user = User::FromNamePercise($username);

			if($user) {
				error_log("[Login] Found user id=" . $user->id . " for name=" . $username);
				@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " FOUND user id=" . $user->id . " name=" . $username . PHP_EOL, FILE_APPEND);
				if(password_verify($pass_password, $user->password)) {
					error_log("[Login] Password verified for user id=" . $user->id);
					@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " PASSWORD_OK user id=" . $user->id . PHP_EOL, FILE_APPEND);
					self::SetCookies($user->security_key);
					if(session_status() != PHP_SESSION_ACTIVE) {
						session_start();
					}

					$_SESSION['SESSION_TOKEN_YAA'] = $user->security_key;
					error_log("[Login] Session token set for user id=" . $user->id);
					@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " SESSION_SET user id=" . $user->id . PHP_EOL, FILE_APPEND);
					return  ['login' => $user->security_key]; // why what
				}
			}
			error_log("[Login] Login failed for name=" . $username);
			@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " LOGIN_FAILED name=" . $username . PHP_EOL, FILE_APPEND);
			return ['login' => "Incorrect details provided!"];
		}

		/**
		 * Summary of IsValidKey
		 * @param mixed $accesskey
		 * @return bool
		 */
		static function IsValidKey(string $accesskey): bool {
			return Database::singleton()->run(
				'SELECT `key` FROM `accesskeys` WHERE `key` = :key',
				[":key" => $accesskey]
			)->rowCount() != 0;
		}

		/**
		 * Uses the access key provided. Will return the discord user id it was created for.
		 * @param string $accesskey
		 * @return string|null
		 */
		static function UseAccessKey(string $accesskey): string|null {
			$db = Database::singleton();
			// yup
			$discorduid =  $db->run("SELECT `discorduid` FROM `accesskeys` WHERE `key` = :key", [":key" => $accesskey])->fetchObject()->discorduid;
			/* use key */  $db->run("DELETE FROM `accesskeys` WHERE `key` = :key", [":key" => $accesskey]);

			return $discorduid;
		}

		/**
		 * Checks if given username is not being already used.
		 * @param string $username
		 * @return bool True if it's not being used
		 */
		public static function IsUsernameAvailable(string $username): bool {
			return User::FromName($username) == null;
		}

		public static function IsUsernameValid(string $username): bool {
			return preg_match("/^[a-zA-Z0-9]{3,20}$/", $username);
		}
		
		public static function RetrieveUser(): User|null {
			if(session_status() != PHP_SESSION_ACTIVE) {
				session_start();
			}

			$user = null;

			// log cookie/session values (masked) for debugging
			$cookieVal = isset($_COOKIE['ANORRLSECURITY']) ? $_COOKIE['ANORRLSECURITY'] : '';
			$sessionVal = isset($_SESSION['SESSION_TOKEN_YAA']) ? $_SESSION['SESSION_TOKEN_YAA'] : '';
			$cookieExcerpt = $cookieVal === '' ? '' : substr($cookieVal, 0, 32) . '...' . ' (len=' . strlen($cookieVal) . ')';
			$sessionExcerpt = $sessionVal === '' ? '' : substr($sessionVal, 0, 32) . '...' . ' (len=' . strlen($sessionVal) . ')';
			@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " RETRIEVE_COOKIE=" . (isset($_COOKIE['ANORRLSECURITY']) ? '1' : '0') . " COOKIE_VAL=" . $cookieExcerpt . " SESSION_TOKEN=" . (isset($_SESSION['SESSION_TOKEN_YAA']) ? '1' : '0') . " SESSION_VAL=" . $sessionExcerpt . PHP_EOL, FILE_APPEND);

			if(isset($_COOKIE['ANORRLSECURITY'])) {
				$user = User::FromSecurityKey(urldecode($_COOKIE['ANORRLSECURITY']));
			}

			// Fall back to the session token when the browser still sends a stale cookie.
			if($user == null && isset($_SESSION['SESSION_TOKEN_YAA'])) {
				$user = User::FromSecurityKey($_SESSION['SESSION_TOKEN_YAA']);
				if($user) {
					self::SetCookies($user->security_key);
				}
			}

			@file_put_contents(__DIR__ . '/../../../logs/login.log', date('c') . " RETRIEVE_RESULT=" . ($user ? '1' : '0') . PHP_EOL, FILE_APPEND);

			if((isset($_COOKIE['ANORRLSECURITY']) || isset($_SESSION['SESSION_TOKEN_YAA'])) && $user == null) {
				unset($_SESSION['SESSION_TOKEN_YAA']);
				self::RemoveCookies();
			}

			if($user) {
				$user->registerAction("Website");
			}
			
			return $user;
		}

		private static function ResolveCookieDomain(): ?string {
			$configDomain = defined('CONFIG') && isset(CONFIG->domain) ? strtolower(trim(CONFIG->domain)) : null;
			$requestHost = !empty($_SERVER['HTTP_HOST']) ? strtolower(trim($_SERVER['HTTP_HOST'])) : (!empty($_SERVER['SERVER_NAME']) ? strtolower(trim($_SERVER['SERVER_NAME'])) : null);
			$requestHost = $requestHost ? preg_replace('/:\d+$/', '', $requestHost) : null;

			if($requestHost === 'localhost' || ($requestHost && filter_var($requestHost, FILTER_VALIDATE_IP))) {
				return null;
			}

			if($requestHost && $configDomain) {
				if($requestHost === $configDomain || str_ends_with($requestHost, "." . $configDomain)) {
					return $configDomain;
				}

				return $requestHost;
			}

			return $requestHost ?: $configDomain;
		}

		static function SetCookies(string $security): void {
			unset($_COOKIE['ANORRLSECURITY']);
			$domain = self::ResolveCookieDomain();
			$options = [
				'expires' => time() + (460800 * 30),
				'path' => '/',
				'samesite' => 'Lax'
			];

			if($domain !== null) {
				$options['domain'] = $domain;
			}

			setcookie("ANORRLSECURITY", $security, $options);
			$_COOKIE['ANORRLSECURITY'] = $security;
		}

		public static function RemoveCookies(): void {
			unset($_COOKIE['ANORRLSECURITY']);
			$domain = self::ResolveCookieDomain();
			$options = [
				'expires' => time() - 3600,
				'path' => '/',
				'samesite' => 'Lax'
			];

			if($domain !== null) {
				$options['domain'] = $domain;
			}

			setcookie("ANORRLSECURITY", "", $options);
		}

		public static function GetRandomUsers(int $count): array {
			$fetch_users = Database::singleton()->run(
				"SELECT id FROM `users` ORDER BY RAND() LIMIT :limit",
				[ ":limit" => $count ]
			)->fetchAll(\PDO::FETCH_OBJ);

			$users =  [];
			foreach($fetch_users as $obj_user) {
				$users[] = User::FromID($obj_user->id);
			}

			return $users;
		}


		public static function GetLatestUsers(int $count): array {
			$fetch_users = Database::singleton()->run(
				"SELECT * FROM `users` ORDER BY `joindate` DESC LIMIT :limit",
				[ ":limit" => $count ]
			)->fetchAll(\PDO::FETCH_OBJ);

			$users =  [];
			foreach($fetch_users as $obj_user) {
				$users[] = User::FromID($obj_user->id);
			}

			return $users;
		}

		public static function GetAllUsersPaged(int $page, int $count, string $query = ""): array|null {
			$queryfiltered = "%$query%";
			if($queryfiltered == "%%") {
				$queryfiltered = "%";
			}

			$db = Database::singleton();

			$fetch_users = $db->run("SELECT `id` FROM `users`")->fetchAll(\PDO::FETCH_OBJ);
			
			foreach($fetch_users as $obj_user) {
				User::FromID($obj_user->id)->isOnline();
			}

			$userids = $db->run(
				"SELECT `users`.`id` FROM `users`, `activity` WHERE `activity`.`userid` = `users`.`id` AND `name` LIKE :query ORDER BY `users`.`online` DESC, `activity`.`action_time` DESC LIMIT :page, :rows",
				[
					":query" => $queryfiltered,
					":page" => (($page-1)*$count),
					":rows" => $count
				]
			)->fetchAll(\PDO::FETCH_OBJ);

			$users = [];

			foreach($userids as $row) {
				error_log("[Login] Found user id=" . $user->id . " for name=" . $username);
				@file_put_contents(__DIR__ . '/../../logs/login.log', date('c') . " FOUND user id=" . $user->id . " name=" . $username . PHP_EOL, FILE_APPEND);
			}

					@file_put_contents(__DIR__ . '/../../logs/login.log', date('c') . " PASSWORD_OK user id=" . $user->id . PHP_EOL, FILE_APPEND);
			return $users;
		}

		public static function GetAllUsers(string $query = ""): array|null {
			$queryfiltered = "%$query%";

					@file_put_contents(__DIR__ . '/../../logs/login.log', date('c') . " SESSION_SET user id=" . $user->id . PHP_EOL, FILE_APPEND);
			$result_array = [];
				error_log("[Login] Login failed for name=" . $username);
				@file_put_contents(__DIR__ . '/../../logs/login.log', date('c') . " LOGIN_FAILED name=" . $username . PHP_EOL, FILE_APPEND);
			$getallusers = Database::singleton()->run(
				"SELECT * FROM `users` WHERE `name` LIKE :query",
				[
					":query" => $queryfiltered
				]
			)->fetchAll(\PDO::FETCH_ASSOC);

			foreach($getallusers as $user) {
				$result_array[] = new User($user);
			}
			
			return $result_array;
		}
	}

?>
