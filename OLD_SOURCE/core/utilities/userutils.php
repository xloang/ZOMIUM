<?php

	require_once $_SERVER['DOCUMENT_ROOT'].'/core/classes/user.php';

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
		public static function RegisterUser(
    string $username,
    string $password,
    string $confirm_password,
    string $accesskey = ''
): string|array {
			$errors = [];

			if(self::IsUsernameValid($username)) {
				if(!self::IsUsernameAvailable($username)) {
					$errors["username"] = "Username is already taken :sob:";
				}
			} else {
				$errors["username"] = "a-z A-Z 0-9 and 3-20 characters only";
			}

			if(strlen($password) >= 7) {
				if(strcmp($password, $confirm_password) !== 0) {
					$errors["password"] = "Passwords doesnt match";
				}
			} else {
				$errors["password"] = "Passwords should be 7 or more characters.";
			}

			// Only validate access key if provided
			if($accesskey !== '' && !self::IsValidKey($accesskey)) {
				$errors["accesskey"] = "Invalid access key.";
			}

			if(sizeof($errors) != 0) {
				return $errors;
			}

			$hashedpass = password_hash($password, PASSWORD_DEFAULT);
			$securitykey = self::GenerateSecurityKey();
			$startingZiu = 100;

			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';

			$stmt_insertuser = $con->prepare("INSERT INTO `users`(`user_name`, `user_blurb`, `user_password`, `user_security`, `user_ziu`) VALUES (?, '', ?, ?, ?);");
			$stmt_insertuser->bind_param('sssi', $username, $hashedpass, $securitykey, $startingZiu);
			if($stmt_insertuser->execute()) {
				self::SetCookies($securitykey);
				return "success";
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

			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
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

			// login user
			$stmt_grabuser = $con->prepare('SELECT * FROM `users` WHERE `user_name` = ?;');
			$stmt_grabuser->bind_param('s', $username);
			$stmt_grabuser->execute();
			$result_grabuser = $stmt_grabuser->get_result();

			if($result_grabuser->num_rows == 1) {
				$user_row = $result_grabuser->fetch_assoc();

				if(password_verify($pass_password, $user_row['user_password'])) {
					self::SetCookies($user_row['user_security']);
					if(session_status() != PHP_SESSION_ACTIVE) {
						session_start();
					}

					$_SESSION['SESSION_TOKEN_YAA'] = $user_row['user_security'];
					return  ['login' => $user_row['user_security']];
				}
			}

			return ['login' => "Incorrect details provided!"];
		}

		/**
		 * Summary of IsValidKey
		 * @param mixed $accesskey
		 * @return bool
		 */
		static function IsValidKey(string $accesskey): bool {
			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
			$stmt_checkkey = $con->prepare('SELECT `access_key` FROM `accesskeys` WHERE `access_key` = ?;');
			$stmt_checkkey->bind_param('s', $accesskey);
			$stmt_checkkey->execute();
			$result_checkkey = $stmt_checkkey->get_result();
			$count = $result_checkkey->num_rows;
			error_log("Checking key: '$accesskey' - Found: $count");
			return $count != 0;
		}

		/**
		 * Uses the access key provided. Will return the discord user id it was created for.
		 * @param string $accesskey
		 * @return string|null
		 */

        public static function StorePendingRegistrationKey(string $accesskey): void {
            if(session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['ANORRL$PendingAccessKey'] = $accesskey;
        }

        public static function GetPendingRegistrationKey(): ?string {
            if(session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }

            if(!isset($_SESSION['ANORRL$PendingAccessKey'])) {
                return null;
            }

            $accesskey = trim((string) $_SESSION['ANORRL$PendingAccessKey']);
            return $accesskey === '' ? null : $accesskey;
        }

        public static function ClearPendingRegistrationKey(): void {
            if(session_status() != PHP_SESSION_ACTIVE) {
                session_start();
            }

            unset($_SESSION['ANORRL$PendingAccessKey']);
        }

		static function UseAccessKey(string $accesskey): string|null {
			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
			$stmt_checkkey = $con->prepare('SELECT `access_discorduid` FROM `accesskeys` WHERE `access_key` = ?;');
		$stmt_checkkey->bind_param('s', $accesskey);
		$stmt_checkkey->execute();
		$result_checkkey = $stmt_checkkey->get_result();

		if($result_checkkey && $result_checkkey->num_rows > 0) {
			$discorduid = $result_checkkey->fetch_assoc()['access_discorduid'];
		} else {
			$discorduid = null;
		}

			$stmt_usekey = $con->prepare('DELETE FROM `accesskeys` WHERE `access_key` = ?;');
			$stmt_usekey->bind_param('s', $accesskey);
			$stmt_usekey->execute();

			return $discorduid;
		}

		/**
		 * Checks if given username is not being already used.
		 * @param string $username
		 * @return bool True if it's not being used
		 */
		public static function IsUsernameAvailable(string $username): bool {
			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
			$stmt_checkusername = $con->prepare('SELECT `user_name` FROM `users` WHERE `user_name` LIKE ?;');
			$stmt_checkusername->bind_param('s', $username);
			$stmt_checkusername->execute();
			$result_checkusername = $stmt_checkusername->get_result();
			return $result_checkusername->num_rows == 0;
		}

		public static function IsUsernameValid(string $username): bool {
			return preg_match("/^[a-zA-Z0-9]{3,20}$/", $username);
		}

		private static function StringContainsFromArray(array $array, string $string) {
			foreach($array as $item) {
				if(str_contains($string, $item)) {
					return true;
				}
			}

			return false;
		}

		public static function RetrieveUser($data = null): User|null {
			if(session_status() != PHP_SESSION_ACTIVE) {
				session_start();
			}

			$user = null;

			if(isset($_COOKIE['ANORRLSECURITY'])) {
				$user = User::FromSecurityKey(urldecode($_COOKIE['ANORRLSECURITY']));	
			} else if(isset($_SESSION['SESSION_TOKEN_YAA'])) {
				$user = User::FromSecurityKey($_SESSION['SESSION_TOKEN_YAA']);	
			}

			$pages = [
				"Home"                              => "/my/home.php",
				"Looking at {username}'s profile"   => "/users/profile.php",
				"Looking at {username}'s friends"   => "/users/friends.php",
				"Looking at {username}'s followers" => "/users/following.php",
				"Looking at {username}'s following" => "/users/followers.php",
				"Stuff"                             => "/my/stuff.php",
				"Create Panel"                      => "/create.php",
				"Changing their profile info"       => "/my/profile.php",
				"People"                            => "/people.php",
				"Browsing games"                    => "/app/games.php",
                "Catalog"                           => "/app/catalog.php",
                "Gallery"                           => "/app/gallery.php",
                "Forum"                             => "/app/forum.php",
                "About Us"                          => "/info/about.php",
                "Legal"                             => "/info/legal.php",
                "Terms"                             => "/info/terms.php",
                "Privacy Policy"                    => "/info/privacy.php",
                "Frontpage"                         => "/index.php",
                "Entering an access key"            => "/app/keys/index.php",
                "Looking at {item}"                  => "/item.php",
				"Looking at {place}"			    => "/app/place.php",
				"Editing an item"				    => "/app/edit.php",
				"Editing their character"		    => "/my/character.php",
				"In Studio"						    => "/my/places.php",
				"Downloaded Zomium!"			    => "/download/thankyou.php",
				"Thinking of downloading Zomium..." => "/download/index.php",
				"Browsing games on mobile"		    => "/mobile/games.php",
				"Looking at their friends"			=> "/my/friends.php",
				"Looking at <b>THE</b> contributors"=> "/info/credits.php",
				"Home (on mobile)"					=> "/mobile/home.php",
				"Settings"							=> "/my/settings.php",
				"Admin Panel 2"                     => "/app/admin/index.php",
				"Upload Panel"                      => "/app/admi/itemcreate.php",
				"Gallery Item"                      => "/app/gallery_item.php",
				"Groups"                            => "/my/groups.php",
				"videos"                            => "/app/videos.php",
				"Group creating"                    => "/my/group/create.php",
				"Gameservers"                       => "/app/admi/gameservers.php", 
				"Banned"                            => "/banned.php",
				"New Create page"                   => "/app/create/newcreate.php"
			];

            $guest_allowed_pages = [
                '/index.php',
                '/login.php',
                '/register.php',
                '/keys/index.php'
            ];


			$dont_catalog_ever = [
				"/api/",
				"/core/",
				"/Admin/",
				"/Admi/",
				"/admi/",
				"/core/gamescripts/",
				"/login.php",
				"/register.php",
				"/secret_keygen/"
			];

            if($user == null && !in_array($_SERVER['SCRIPT_NAME'], $guest_allowed_pages) && !self::StringContainsFromArray($dont_catalog_ever, $_SERVER['SCRIPT_NAME']) && !str_starts_with(strtolower($_SERVER['SCRIPT_NAME']), '/admi/')) {
                header('Location: /');
                exit;
            }

			if($user != null) {
				// Global Ban Check
				if ($user->IsBanned() && !str_contains($_SERVER['SCRIPT_NAME'], "/banned.php")) {
					header("Location: /banned.php");
					exit;
				}

				// Global Warning Check
				include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
				$stmt_check_warn = $con->prepare("SELECT `id` FROM `user_warnings` WHERE `user_id` = ? AND `is_read` = 0 LIMIT 1");
				$stmt_check_warn->bind_param("i", $user->id);
				$stmt_check_warn->execute();
				$has_unread_warning = $stmt_check_warn->get_result()->num_rows > 0;

				if ($has_unread_warning && !str_contains($_SERVER['SCRIPT_NAME'], "/warning.php") && !str_contains($_SERVER['SCRIPT_NAME'], "/core/") && !str_contains($_SERVER['SCRIPT_NAME'], "/admi/")) {
					header("Location: /warning.php");
					exit;
				}

				if(!in_array($_SERVER['SCRIPT_NAME'], $pages) && !self::StringContainsFromArray($dont_catalog_ever, $_SERVER['SCRIPT_NAME']) && !str_starts_with(strtolower($_SERVER['SCRIPT_NAME']), "/admi/")) {
					die($_SERVER['SCRIPT_NAME']);
				} else {
					if(str_ends_with(strtolower($_SERVER['SCRIPT_NAME']), "/admi/")) {
						$page = "Doing secret admin stuff...";
					} else {
						if(!self::StringContainsFromArray($dont_catalog_ever, $_SERVER['SCRIPT_NAME'])) {
							$page = array_search($_SERVER['SCRIPT_NAME'], $pages);
							if($data instanceof User) {
								if($data->id != $user->id) {
									$user_id = $data->id;
									$user_name = $data->name;
									$page = str_replace("{username}", "<a href='/users/$user_id/profile'>$user_name</a>", $page);
								} else {
									$page = "Looking at their own profile";
								}
							}

							if($data instanceof Asset) {
								$asset_id = $data->id;
								$asset_name = $data->name;
								$asset_urlname = $data->GetURLTitle();
								$asset_link = "<a href='/$asset_urlname-item?id=$asset_id'>$asset_name</a>";

								$page = str_replace("{item}", $asset_link, $page);
								
							}

							if($data instanceof Place) {
								$asset_id = $data->id;
								$asset_name = $data->name;
								$asset_urlname = $data->GetURLTitle();
								$asset_link = "<a href='/$asset_urlname-place?id=$asset_id'>$asset_name</a>";

								$page = str_replace("{place}", $asset_link, $page);
								
							}

							self::RegisterAction($user, $page);
						}
					}
					
					
				}
				
			}
			
			return $user;
		}

		/**
		 * Track user activity (aka set current time when they entered new page)
		 * @param mixed $action What action took place?
		 * @return void
		 */
		public static function RegisterAction(User $reg_user, string $action = "Website"): void {
			if($reg_user != null) {
				include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
				// Check if row exists
				$stmt_check_row = $con->prepare('SELECT * FROM `activity` WHERE `userid` = ?');
				$stmt_check_row->bind_param('i', $reg_user->id);
				$stmt_check_row->execute();
				$stmt_check_row->store_result();

				// If it doesn't then create one
				if($stmt_check_row->num_rows == 0) {
					$stmt_insert_row = $con->prepare('INSERT INTO `activity`(`userid`, `action`, `action_time`) VALUES (?, ?, now())');
					$stmt_insert_row->bind_param('is', $reg_user->id, $action);
					$stmt_insert_row->execute();
				} else {
					// Else, Update row
					$stmt_update_row = $con->prepare('UPDATE `activity` SET `action` = ?,`action_time` = now() WHERE `userid` = ?');
					$stmt_update_row->bind_param('si', $action, $reg_user->id);
					$stmt_update_row->execute();
				}
			}
		}

		static function SetCookies(string $security): void {
			unset($_COOKIE['ANORRLSECURITY']);
			setcookie("ANORRLSECURITY", $security, time() + (460800* 30), "/", ".lambda.cam");
		}

		public static function RemoveCookies(): void {
			unset($_COOKIE['ANORRLSECURITY']);
			setcookie("ANORRLSECURITY", "", -1, "/", $_SERVER['SERVER_NAME']);
			setcookie("ANORRLSECURITY", "", -1, "/", ".lambda.cam");
		}

		public static function GetRandomUsers(int $count): array {
			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
			
			$stmt = $con->prepare('SELECT * FROM `users` ORDER BY RAND() LIMIT ?');
			$stmt->bind_param('i', $count);
			$stmt->execute();

			$result = $stmt->get_result();

			if($result->num_rows != 0) {
				$users =  [];

				while(($row = $result->fetch_assoc()) != null) {
					array_push($users, new User($row));
				}

				return $users;
			}

			return [];
		}


		public static function GetLatestUsers(int $count): array {
			include $_SERVER['DOCUMENT_ROOT'].'/core/connection.php';
			
			$stmt = $con->prepare('SELECT * FROM `users` ORDER BY `user_joindate` DESC LIMIT ?');
			$stmt->bind_param('i', $count);
			$stmt->execute();

			$result = $stmt->get_result();

			if($result->num_rows != 0) {
				$users =  [];

				while(($row = $result->fetch_assoc()) != null) {
					array_push($users, new User($row));
				}

				return $users;
			}

			return [];
		}

		public static function GetAllUsersPaged(int $pagenum, int $count, string $query = ""): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$queryfiltered = "%$query%";
			if($queryfiltered == "%%") {
				$queryfiltered = "%";
			}

			$stmt = $con->prepare("SELECT `user_id` FROM `users` WHERE 1;");
			$stmt->execute();

			$result_stmt = $stmt->get_result();

			while($row = $result_stmt->fetch_assoc()) {
				User::FromID(intval($row['user_id']))->IsOnline();
			}

			$stmt_getallusers = $con->prepare("SELECT * FROM `users` WHERE `user_name` LIKE ? ORDER BY `user_online` DESC, `user_joindate` DESC LIMIT ?, ?");
			$page = (($pagenum-1)*$count);
			
			$stmt_getallusers->bind_param('sii', $queryfiltered, $page, $count);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					array_push($result_array, new User($row));
				}
				
			}
			return $result_array;
		}

		public static function GetAllUsers(string $query = ""): array|null {
			include $_SERVER["DOCUMENT_ROOT"]."/core/connection.php";
			$queryfiltered = "%$query%";
			$stmt_getallusers = $con->prepare("SELECT * FROM `users` WHERE `user_name` LIKE ?");
			$stmt_getallusers->bind_param('s', $queryfiltered);
			$stmt_getallusers->execute();
			$result = $stmt_getallusers->get_result();
			$result_array = [];

			if($result->num_rows != 0) {
				while($row = $result->fetch_assoc()) {
					array_push($result_array, new User($row));
				}
				
			}
			return $result_array;
		}
	}

?>



