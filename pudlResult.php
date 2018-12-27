<?php




require_once(is_owner(__DIR__.'/pudlData.php'));




abstract class	pudlResult
	implements	pudlData {




	////////////////////////////////////////////////////////////////////////////
	// CONSTRUCTOR. PASS IN A $PUDL OBJECT, AND THE $RESULT IF AVAIL
	////////////////////////////////////////////////////////////////////////////
	public function __construct(pudl $pudl, $result=false) {
		$this->result	= $result;
		$this->pudl		= $pudl;
		$this->query	= $pudl->query();
		$this->string	= $pudl->isString();
	}




	////////////////////////////////////////////////////////////////////////////
	// DESTRUCTOR - REQUIRED FOR INHERITANCE
	////////////////////////////////////////////////////////////////////////////
	public function __destruct() {
	}




	////////////////////////////////////////////////////////////////////////////
	// SHORTCUT METHOD FOR ACCESSING CURRENT ROW DATA
	////////////////////////////////////////////////////////////////////////////
	public function __invoke() {
		return $this->row();
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S COUNTABLE - GET THE NUMBER OF ROWS FROM THIS RESULT
	// http://php.net/manual/en/countable.count.php
	////////////////////////////////////////////////////////////////////////////
	abstract public function count();




	////////////////////////////////////////////////////////////////////////////
	// PHP'S SEEKABLEITERATOR - JUMP TO A ROW IN THIS RESULT
	// http://php.net/manual/en/seekableiterator.seek.php
	////////////////////////////////////////////////////////////////////////////
	abstract public function seek($row);




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR - GET THE CURRENT ROW IN THIS RESULT
	// http://php.net/manual/en/iterator.current.php
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		if ($this->row === false) $this->row();
		return $this->data;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR - GET THE KEY FOR THE CURRENT ROW IN THIS RESULT
	// http://php.net/manual/en/iterator.key.php
	////////////////////////////////////////////////////////////////////////////
	public function key() {
		return ($this->row === false) ? 0 : $this->row;
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR - MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW
	// http://php.net/manual/en/iterator.next.php
	////////////////////////////////////////////////////////////////////////////
	public function next() {
		return $this->row();
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR - MOVE TO THE FIRST ROW IN THIS RESULT
	// http://php.net/manual/en/iterator.rewind.php
	////////////////////////////////////////////////////////////////////////////
	public function rewind() {
		$this->seek(0);
	}




	////////////////////////////////////////////////////////////////////////////
	// PHP'S ITERATOR - TRUE IF THE CURRENT ROW IN THIS RESULT IS VALID
	// http://php.net/manual/en/iterator.valid.php
	////////////////////////////////////////////////////////////////////////////
	public function valid() {
		if ($this->row === false) $this->row();
		return pudl_array($this->data);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE NUMBER OF FIELD COLUMNS IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	abstract public function fields();




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON A PARTICULAR FIELD COLUMN IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	abstract public function getField($column);




	////////////////////////////////////////////////////////////////////////////
	// GET DETAILS ON ALL FIELD COLUMNS IN THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function listFields() {
		if (!$this->result) return false;

		if ($this->fields === false) {
			$this->fields = [];
			$total = $this->fields();
			for ($i=0; $i<$total; $i++) {
				$this->fields[] = $this->getField($i);
			}
		}

		return $this->fields;
	}




	////////////////////////////////////////////////////////////////////////////
	// TRUE IF THIS IS A STRING RESULT
	////////////////////////////////////////////////////////////////////////////
	public function isString() {
		return $this->string;
	}




	////////////////////////////////////////////////////////////////////////////
	// TRUE IF THIS RESULT CONTAINS DATA
	////////////////////////////////////////////////////////////////////////////
	public function hasRows() {
		return ($this->count() > 0);
	}




	////////////////////////////////////////////////////////////////////////////
	// FREE RESOURCES ASSOCIATED WITH THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	abstract public function free();




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	abstract public function cell($row=0, $column=0);




	////////////////////////////////////////////////////////////////////////////
	// GET A SINGLE CELL FROM THIS RESULT, AND FREE THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function completeCell($row=0, $column=0) {
		$cell = $this->cell($row, $column);
		$this->free();
		return $cell;
	}




	////////////////////////////////////////////////////////////////////////////
	// MOVE TO THE NEXT ROW IN THIS RESULT AND RETURN THAT ROW'S DATA
	////////////////////////////////////////////////////////////////////////////
	abstract public function row();




	////////////////////////////////////////////////////////////////////////////
	// GET ALL ROWS FROM THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function rows() {
		if (!$this->result) return false;
		$rows = [];
		while ($data = $this->row()) {
			$rows[] = $data;
		}
		return $rows;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET ALL ROWS FROM THIS RESULT, AND FREE THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function complete() {
		$rows = $this->rows();
		$this->free();
		return $rows;
	}




	////////////////////////////////////////////////////////////////////////////
	// COMBINE TWO RESULT COLUMNS INTO KEY-VALUE PAIR
	////////////////////////////////////////////////////////////////////////////
	public function collection() {
		$return = [];
		while ($data = $this->row()) {
			$return[reset($data)] = end($data);
		}
		$this->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// CONVERT RESULT DATA INTO A TREE
	////////////////////////////////////////////////////////////////////////////
	public function tree($separator='.') {
		$return = [];

		while ($data = $this->row()) {
			$keys = explode($separator, reset($data));
			$node = &$return;

			foreach ($keys as $count => $key) {
				if ($count === count($keys)-1) break;
				if (!array_key_exists($key, $node)) $node[$key] = [];
				if (!pudl_array($node[$key])) $node[$key] = [$node[$key]];
				$node = &$node[$key];
			}

			if (!array_key_exists($key, $node)) {
				$node[$key] = end($data);
			} else {
				$node[$key][] = end($data);
			}
		}

		$this->free();
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS JSON SERIALIZABLE DATA
	// http://php.net/manual/en/jsonserializable.jsonserialize.php
	////////////////////////////////////////////////////////////////////////////
	public function jsonSerialize() {
		return $this->rows();
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE JSON REPRESENTATION OF THIS RESULT
	// http://php.net/manual/en/function.json-encode.php
	////////////////////////////////////////////////////////////////////////////
	public function json() {
		return pudl::jsonEncode($this);
	}




	////////////////////////////////////////////////////////////////////////////
	// RETURNS THE JSON REPRESENTATION OF THIS RESULT, AND FREE THIS RESULT
	// http://php.net/manual/en/function.json-encode.php
	////////////////////////////////////////////////////////////////////////////
	public function completeJson() {
		$json = $this->json();
		$this->free();
		return $json;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET SQL QUERY THAT GENERATED THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function query() {
		return $this->query;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE RAW PHP RESOURCE FOR THIS REUSLT
	////////////////////////////////////////////////////////////////////////////
	public function result() {
		return $this->result;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE PUDL INSTANCE ASSOCIATED WITH THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function pudl() {
		return $this->pudl;
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR CODE FOR THIS RESULT - 0, FALSE, NULL ALL MEAN NO ERROR
	////////////////////////////////////////////////////////////////////////////
	public function errno() {
		return ($this->result === false)  ||  ($this->result === NULL);
	}




	////////////////////////////////////////////////////////////////////////////
	// GET THE ERROR MESSAGE FOR THIS RESULT
	////////////////////////////////////////////////////////////////////////////
	public function error() {
		return $this->errno() ? 'Unknown Error' : '';
	}




	////////////////////////////////////////////////////////////////////////////
	// MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	/** @var pudl */			protected $pudl;
	/** @var mixed */			protected $result;
	/** @var string */			protected $query;
	/** @var bool */			protected $string;
	/** @var array|false */		protected $fields	= false;
	/** @var int|false */		protected $row		= false;
	/** @var array|false */		protected $data		= false;
}
