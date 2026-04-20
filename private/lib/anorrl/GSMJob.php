<?php
	namespace anorrl;

	use anorrl\Place;

	class GSMJob {
		public string $id;
		public int $port;
		public Place $place;
		public int $pid;

		public \DateTime $expiresAt;
		public \DateTime $lastHeartbeat;
		public bool $alive;

		function __construct(
			string $id,
			int $port,
			int $placeid,
			int $pid,
			\DateTime $expiresAt,
			\DateTime $lastHeartbeat,
			bool $alive
		) {
			$this->id = $id;
			$this->port = $port;
			$this->place = Place::FromID($placeid);
			$this->pid = $pid;
			$this->expiresAt = $expiresAt;
			$this->lastHeartbeat = $lastHeartbeat;
			$this->alive = $alive;
		}
	}
?>