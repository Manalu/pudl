<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlNullResult.php'));



////////////////////////////////////////////////////////////////////////////////
// EQUIV TO /dev/null - NOTHING IS READ, NOTHING IS WRITTEN, ANYWHERE
////////////////////////////////////////////////////////////////////////////////
class pudlNull extends pudl {



	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($data=[], $autoconnect=true) {
		if (!empty($data['identifier'])) {
			$this->identifier = $data['identifier'];
		}

		parent::__construct($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE INSTANCE OF THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public static function instance($data, $autoconnect=true) {
		return new pudlNull($data, $autoconnect);
	}




	////////////////////////////////////////////////////////////////////////////
	// PROCESS A QUERY (WE'RE NULL, WE DO NOTHING)
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		return new pudlNullResult($this);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURN A LIST OF TABLES
	////////////////////////////////////////////////////////////////////////////
	public function tables($clause=NULL) {
		if (!empty($this->string)) return parent::tables($clause);
		return ['dev_null'];
	}




	////////////////////////////////////////////////////////////////////////////
	// REQUIRED METHODS, RETURN DEFAULT VALUES FOR ALL
	////////////////////////////////////////////////////////////////////////////
	public function insertId()	{ return 0; }
	public function updated()	{ return 0; }
	public function errno()		{ return 0; }
	public function error()		{ return ''; }

}
