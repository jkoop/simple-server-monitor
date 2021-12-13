<?php

const SQLITE_FILE = __DIR__ . '/../db.sqlite';
const SQLITE_TABLES = 'CREATE TABLE IF NOT EXISTS "data" (
	"hostname" TEXT NOT NULL,
	"time" INTEGER NOT NULL,
	"topic" TEXT NOT NULL,
	"normalizer" NUMERIC NOT NULL,
	"value_raw" NUMERIC,
	"value_normalized" NUMERIC,
	UNIQUE ("hostname", "time", "topic")
);
CREATE INDEX IF NOT EXISTS "data_hostname_time_topic" ON "data" ("hostname", "time", "topic");';

function db() {
	static $db = null;
	if ($db == null) $db = new DB;

	return $db;
}

class Db {
	public function __construct() {
		$this->filename = SQLITE_FILE;
		$this->sqlite = null;
	}

	private function assureTables(): void {
		$this->sqlite->exec(SQLITE_TABLES);
	}

	private function open(): void {
		if ($this->sqlite === null) {
			$this->sqlite = new SQLite3($this->filename);
			$this->sqlite->busyTimeout(30000); // 30s lock timeout
			$this->sqlite->exec('PRAGMA synchronous = ON; PRAGMA recursive_triggers = OFF;');
			$this->assureTables();
		}
	}

	private function bindValue(SQLite3Stmt $statement, string $key, string $value): void {
		$statement->bindValue(
			':' . $key,
			$value
		);
	}

	public function queryAll(string $query, array $values = []): array {
		$this->open();
		$statement = $this->sqlite->prepare($query);

		if ($statement === false) {
			return false;
		}

		foreach ($values as $key => $value) {
			$this->bindValue($statement, $key, $value);
		}

		$result = $statement->execute();

		if ($result === false) {
			return false;
		}

		$return = [];

		if ($result->numColumns()) {
			while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
				$return[] = (object) $row;
			}
		}

		return $return;
	}

	public function queryRow(string $query, array $values = []): ?object {
		$query = $this->queryAll($query . ' LIMIT 1', $values);

		if (count($query) == 0) {
			return null;
		}

		return (object) $query[0];
	}

	public function queryColumn(string $query, array $values = []): array {
		$query2 = $this->queryAll($query, $values);

		if ($query2 === false) return false;

		if ($query2 == []) return [];

		$key = array_keys((array) $query2[0])[0];

		$response = [];

		foreach ($query2 as $row) {
			$response[] = $row->$key;
		}

		return $response;
	}

	public function queryCell(string $query, array $values = []) {
		$response = (array) $this->queryRow($query, $values);

		if ($response === null || count($response) == 0) {
			return null;
		}

		return $response[array_keys($response)[0]];
	}

	public function exec(string $query, array $values = []): bool {
		$response = $this->queryAll($query, $values);
		if ($response !== false) {
			return true;
		} else {
			return false;
		}
	}

	public function escape(string $string): string {
		return SQLite3::escapeString($string);
	}
}
