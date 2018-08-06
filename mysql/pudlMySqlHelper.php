<?php


////////////////////////////////////////////////////////////////////////////////
//COMMON FUNCTIONS FOR MYSQL, MYSQLI, AND GALERA OBJECTS
////////////////////////////////////////////////////////////////////////////////
trait pudlMySqlHelper {




	////////////////////////////////////////////////////////////////////////////
	//EXECUTE A RAW SQL QUERY WITHOUT ADDITIONAL PROCESSING
	////////////////////////////////////////////////////////////////////////////
	abstract function _query($query);




	////////////////////////////////////////////////////////////////////////////
	//SETS THE QUERY CACHING HINT TO THE DATABASE
	////////////////////////////////////////////////////////////////////////////
	protected function _cache() {
		if (!$this->cache)						return '';
		if ($this->isString())					return '';
		if ($this->inUnion())					return '';
		if (!is_object($this->redis))			return 'SQL_CACHE ';
		if ($this->redis instanceof pudlVoid)	return 'SQL_CACHE ';
		return 'SQL_NO_CACHE ';
	}




	////////////////////////////////////////////////////////////////////////////
	//GET FIELD TYPE INFORMATION FOR A PARITUCLAR COLUMN IN A TABLE
	////////////////////////////////////////////////////////////////////////////
	public function fieldType($table, $column) {
		if (substr($table, 0, 5) === 'pudl_') {
			$table = $this->prefix . substr($table, 5);
		}

		$auth = $this->auth();

		$return = $this->cell('INFORMATION_SCHEMA.COLUMNS', 'COLUMN_TYPE', [
			'TABLE_SCHEMA'	=> $auth['database'],
			'TABLE_NAME'	=> $table,
			'COLUMN_NAME'	=> $column,
		]);

		if (substr($return, 0, 5) === 'enum(') {
			$return = str_getcsv(substr($return, 5, -1), ',', "'");
		} else if (substr($return, 0, 4) === 'set(') {
			$return = str_getcsv(substr($return, 4, -1), ',', "'");
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//SET THE QUERY TIMEOUT VALUE - HELPS PREVENT DDOS ATTACKS
	////////////////////////////////////////////////////////////////////////////
	public function timeout($timeout) {
		if (pudl_array($timeout)) {
			if (empty($timeout['timeout'])) return $this;
			$timeout = $timeout['timeout'];
		}

		if (!empty($timeout)) {
			$this->set('max_statement_time', (int)$timeout);
		}

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE "READ ONLY" STATUS FROM THE SERVER
	////////////////////////////////////////////////////////////////////////////
	public function readonly() {
		$ro = $this->variables('read_only');
		if (!pudl_array($ro)  ||  empty($ro)) return false;
		$ro = strtoupper(reset($ro));
		return ($ro === 'ON')  ||  ($ro === '1');
	}




	////////////////////////////////////////////////////////////////////////////
	//SET STRICT SQL COMPATIBILITY MODE
	////////////////////////////////////////////////////////////////////////////
	public function strict() {
		$this->_query(
			"SET @@SQL_MODE = CONCAT(@@SQL_MODE, ',TRADITIONAL')"
		);

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//SHOULD WE DIE ON CONNECTION ERRORS? USED FOR QUERYING GALERA CLUSTER
	////////////////////////////////////////////////////////////////////////////
	public static function dieOnError($die) {
		self::$die = $die;
	}




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE STATIC VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private static $die	= true;

}
