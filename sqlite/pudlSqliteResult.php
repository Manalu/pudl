<?php


class pudlSqliteResult extends pudlResult {
	public function __construct($result, $db) {
		parent::__construct($result, $db);

		$this->row = 0;
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
			if ($row > $this->row) {
				$this->row = 0;
				$this->result->reset();
			}

			for ($i=$this->row; $i<=$row; $i++) {
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
		$this->data = false;
		switch ($type) {
			case PUDL_ARRAY:	$this->data = $this->result->fetchArray(SQLITE3_ASSOC);	break;
			case PUDL_NUMBER:	$this->data = $this->result->fetchArray(SQLITE3_NUM);	break;
			case PUDL_BOTH:		$this->data = $this->result->fetchArray(SQLITE3_BOTH);	break;
			default:			$this->data = $this->result->fetchArray();
		}
		if ($this->data !== false) {
			$this->row = ($this->row === false) ? 0 : $this->row+1;
		}
		return $this->data;
	}


}