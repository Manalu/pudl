<?php


require_once('pudlResult.php');


class pudlSqliteResult extends pudlResult {
	public function __construct($result, $db) {
		parent::__construct($result, $db);

		$this->rownum = 0;
	}


	public function __destruct() {
		parent::__destruct();
		$this->free();
	}


	public function free() {
		if (is_object($this->result)) {
			$this->result->finalize();
			$this->result = NULL;
			return true;
		}
		return false;
	}


	public function cell($row=0, $column=0) {
		$return = false;

		if (is_object($this->result)) {
			if ($row > $this->rownum) {
				$this->rownum = 0;
				$this->result->reset();
			}

			for ($i=$this->rownum; $i<=$row; $i++) {
				$data = $this->row(PUDL_NUMBER);
			}

			if (pudl_array($data)  &&  array_key_exists($column, $data)) {
				$return = $data[$column];
			}
		}

		return $return;
	}


	public function seek($row) {
		return false;
		//TODO: IMPLEMENT THIS!
	}


	public function count() {
		return 0;
		//TODO: IMPLEMENT THIS (but it'll be hacky, since Sqlite doesn't support it!)
//		$rows = false;
//		if (is_object($this->result)) $rows = $this->result->numColumns();
//		return ($rows !== false) ? $rows : 0;
	}


	public function fields() {
		$fields = false;
		if (is_object($this->result)) $fields = $this->result->numColumns();
		return ($fields !== false) ? $fields : 0;
	}


	public function getField($column) {
		$field = false;
		if (is_object($this->result)) $field = $this->result->columnName($column);
		return ($field !== false) ? $field : false;
	}


	public function row($type=PUDL_ARRAY) {
		if (!is_object($this->result)) return false;

		$this->rownum++;

		$data = false;
		switch ($type) {
			case PUDL_ARRAY:	$data = $this->result->fetchArray(SQLITE3_ASSOC);	break;
			case PUDL_NUMBER:	$data = $this->result->fetchArray(SQLITE3_NUM);		break;
			case PUDL_BOTH:		$data = $this->result->fetchArray(SQLITE3_BOTH);	break;
			default:			$data = $this->result->fetchArray();
		}
		return is_array($data) ? $data : false;
	}


	private $rownum;

}
