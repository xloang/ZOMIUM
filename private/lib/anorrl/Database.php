<?php
	namespace anorrl;

	require_once dirname(__DIR__, 2) . "/bootstrap.php";
	
	/**
	 * Lifted from fubuki by parakeet
	 */
	#[\AllowDynamicProperties]
	class Database {
		private static self|null $instance = null;
		public \PDO $pdo;

		public static function singleton(): self {
			if (!self::$instance) {
				self::$instance = new Database();
			}

			return self::$instance;
		}

		function __construct() {
			$database_config = $GLOBALS['__config']->database ?? null;
			$hostname = trim((string)($database_config->hostname ?? 'localhost'));
			$database = trim((string)($database_config->name ?? ''));
			$port = intval($database_config->port ?? 3306);

			if($database === '') {
				throw new \RuntimeException("Database name is missing in settings.json.");
			}

			$this->pdo = new \PDO(
				"mysql:host=" . $hostname . ";
				port=" . $port . ";
				dbname=" . $database . ";
				charset=utf8mb4", 
				(string)($database_config->username ?? 'root'), 
				(string)($database_config->password ?? '')
			);

			$this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
			$this->pdo->setAttribute(\PDO::ATTR_PERSISTENT, true);
		}

		private function getPDOType(mixed $data): int {
			if(is_int($data) || is_bool($data))
				return \PDO::PARAM_INT;

			return \PDO::PARAM_STR;
		}

		private function evaluateValue(mixed $data): mixed {
			if(is_bool($data))
				return $data ? 1 : 0;

			return $data;
		}

		function run($sql, $args = null): \PDOStatement {
			if (!$args) return $this->pdo->query($sql);
			
			$stmt = $this->pdo->prepare($sql);

			foreach ($args as $param => $value) {
				$stmt->bindValue(
					is_int($param) ? $param + 1 : $param,
					$this->evaluateValue($value), 
					$this->getPDOType($value)
				);
			}

			$stmt->execute();

			return $stmt;
		}

		function lastInsertId(): string {
			return $this->pdo->lastInsertId();
		}
	}
?>
