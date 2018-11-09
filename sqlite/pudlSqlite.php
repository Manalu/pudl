<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlSqliteResult.php'));



class pudlSqlite extends pudl {


	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($data=[], $autoconnect=true) {

		if (!pudl_array($data)) $data = [$data];
		if (empty($data['database'])) {
			$data['database'] = empty($data[0]) ? 'sqlite.db' : $data[0];
		}

		if (!empty($data['identifier'])) {
			$this->identifier = $data['identifier'];
		}

		parent::__construct($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE AN INSTANCE OF THE PUDL SQLITE OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data=[], $autoconnect=true) {
		return new pudlSqlite($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// OPEN THE SQLITE DATABASE FILE
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		$auth = $this->auth();


		// Verify we have the Sqlite3 PHP extension installed
		pudl_require_extension('sqlite3');


		// Set READ-ONLY / READ-WRITE access
		$flags	= $auth['readonly']
				? SQLITE3_OPEN_READONLY
				: SQLITE3_OPEN_READWRITE;


		// Create Sqlite3 object instance
		try {
			$this->connection = new SQLite3(
				$auth['database'],
				SQLITE3_OPEN_CREATE | $flags,
				$auth['key']
			);

		// Convert PHP exception to PUDL exception
		} catch (Exception $e) {
			throw new pudlConnectionException(
				$this,
				'Unable to open Sqlite database file: ' . $auth['database']
			);
		}


		// Set a busy timeout for Sqlite to 'timeout' seconds
		$this->connection->busyTimeout($auth['timeout'] * 1000);
	}




	////////////////////////////////////////////////////////////////////////////
	// DISCONNECT FROM SQLITE SERVICE (UNLOAD/RELEASE FILE HANDLE)
	////////////////////////////////////////////////////////////////////////////
	public function disconnect($trigger=true) {
		parent::disconnect($trigger);
		if (!$this->connection) return;
		@$this->connection->close();
		$this->connection = NULL;
	}




	////////////////////////////////////////////////////////////////////////////
	// ESCAPE A VALUE
	////////////////////////////////////////////////////////////////////////////
	public function escape($str) {
		if (!$this->connection) return false;
		return @$this->connection->escapeString($str);
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT DATA TO BLOB
	////////////////////////////////////////////////////////////////////////////
	protected function blob($value) {
		return "x'" . bin2hex($value) . "'";
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS A QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		if (!$this->connection) return new pudlSqliteResult($this);
		$result = $this->connection->query($query);
		return new pudlSqliteResult($this, $result);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST AUTO INCREMENT NUMBER FROM INSERTED DATA
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		if (!$this->connection) return 0;
		return $this->connection->lastInsertRowID();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF ROWS UPDATED BY THE LAST QUERY
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		if (!$this->connection) return 0;
		return $this->connection->changes();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR NUMBER
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		if (!$this->connection) return 0;
		return $this->connection->lastErrorCode();
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE LAST ERROR MESSAGE
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		if (!$this->connection) return '';
		return $this->connection->lastErrorMsg();
	}




	////////////////////////////////////////////////////////////////////////////
	// GENERATE THE UPSERT PART OF THE QUERY
	////////////////////////////////////////////////////////////////////////////
	protected function _upsert($data) {
		if (!pudl_array($data)  ||  empty($data)) return false;

		return	' ON CONFLICT (' .
				$this->identifier(key($data)) .
				') DO UPDATE SET ' .
				$this->_update($data);
	}

}
