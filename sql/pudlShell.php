<?php


if (!class_exists('pudl',false)) require_once(__DIR__.'/../pudl.php');
require_once(is_owner(__DIR__.'/pudlShellResult.php'));



class		pudlShell
	extends	pudl {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct($options) {
		parent::__construct($options);
		$this->path = empty($options['path']) ? '' : $options['path'];
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
		$this->disconnect();
		parent::__destruct();
	}




	////////////////////////////////////////////////////////////////////////////
	// VERIFY WE HAVE THE PROPER PHP EXTENSION INSTALLED
	// NOTE: NO ACTIVE CONNECTIONS ARE MADE WITH THIS UNTIL A REQUEST IS MADE
	////////////////////////////////////////////////////////////////////////////
	public function connect() {
		pudl_require_extension('json');
	}




	////////////////////////////////////////////////////////////////////////////
	// PERFORMS A QUERY ON THE DATABASE AND RETURNS A PUDLRESULT
	// http://php.net/manual/en/function.exec.php
	////////////////////////////////////////////////////////////////////////////
	protected function process($query) {
		$result = false;

		exec(implode(' ', [
			'php',
			escapeshellarg($this->path),
			escapeshellarg($query),
		]), $result);

		return $this->_process($result[0]);
	}




	////////////////////////////////////////////////////////////////////////////
	// CREATE THE PUDLRESULT FROM JSON DATA AND RETURN IT
	////////////////////////////////////////////////////////////////////////////
	protected function _process($json) {
		$item = new pudlShellResult($this, $json);
		$this->insertId	= $item->insertId();
		$this->updated	= $item->updated();
		$this->errno	= $item->errno();
		$this->error	= $this->errno ? $item->error() : '';
		return $item;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE AUTO GENERATED ID USED IN THE LATEST QUERY
	////////////////////////////////////////////////////////////////////////////
	public function insertId() {
		return $this->insertId;
	}



	////////////////////////////////////////////////////////////////////////////
	// GETS THE NUMBER OF AFFECTED ROWS IN A PREVIOUS MYSQL OPERATION
	////////////////////////////////////////////////////////////////////////////
	public function updated() {
		return $this->updated;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE ERROR CODE FOR THE MOST RECENT FUNCTION CALL
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		return $this->errno;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS A STRING DESCRIPTION OF THE LAST ERROR
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		global $__json_errors__;

		if (!empty($this->error)) return $this->error;

		$error = $this->errno();

		return isset($__json_errors__[$error])
			? $__json_errors__[$error]
			: $__json_errors__[-1];
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	protected $path		= '';
	protected $errno	= false;
	protected $error	= false;
	protected $insertId	= 0;
	protected $updated	= 0;
}
