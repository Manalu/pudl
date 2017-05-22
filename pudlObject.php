<?php


class pudlObject implements ArrayAccess, Iterator {


	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR
	////////////////////////////////////////////////////////////////////////////
	public function __construct(&$array=NULL, $copy=false) {
		$copy ? $this->copy($array) : $this->replace($array);
	}




	////////////////////////////////////////////////////////////////////////////
	//CLEARS ALL DATA WITHIN OBJECT - RESETTING BACK TO DEFAULTS
	////////////////////////////////////////////////////////////////////////////
	public function clear() {
		$this->__array		= [];
		$this->__snapshot	= false;
		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//REPLACES THE OBJECT'S ARRAY WITH THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function replace(&$array) {
		$this->clear();

		is_array($array)
			? $this->__array = &$array
			: $this->copy($array);

		return $this;
	}




	////////////////////////////////////////////////////////////////////////////
	//CLEARS THE OBJECT'S ARRAY, AND THEN COPIES THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function copy($array) {
		return $this->clear()->merge($array);
	}




	////////////////////////////////////////////////////////////////////////////
	//COPY THE GIVEN ARRAY INTO THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public function merge($array) {
		if (empty($array)  ||  !pudl_array($array)) return;
		foreach($array as $key => $value) {
			$this->__array[$key] = $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//COPIES THIS OBJECT INTO THE GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public function mergeInto(&$array) {
		if (empty($array)  ||  !pudl_array($array)) return;
		foreach($this->__array as $key => $value) {
			$array[$key] = $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//COPY THE GIVEN ARRAY INTO THIS OBJECT, ONLY FOR KEYS THAT ARE MISSING
	////////////////////////////////////////////////////////////////////////////
	public function append($array) {
		if (empty($array)  ||  !pudl_array($array)) return;
		foreach($array as $key => $value) {
			if (isset($this->__array[$key])) continue;
			$this->__array[$key] = $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//COPIES THIS OBJECT INTO THE GIVEN ARRAY, ONLY FOR KEYS THAT ARE MISSING
	////////////////////////////////////////////////////////////////////////////
	public function appendInto(&$array) {
		if (empty($array)  ||  !pudl_array($array)) return;
		foreach($this->__array as $key => $value) {
			if (isset($array[$key])) continue;
			$array[$key] = $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//GET THE RAW ARRAY FOR THIS OBJECT
	////////////////////////////////////////////////////////////////////////////
	public function &raw() {
		return $this->__array;
	}




	////////////////////////////////////////////////////////////////////////////
	//COUNT THE NUMBER OF ITEMS IN THIS OBJECT
	//http://php.net/manual/en/function.count.php
	////////////////////////////////////////////////////////////////////////////
	public function count() {
		return count($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PUSH ITEMS ONTO THE END OF THIS OBJECT
	//http://php.net/manual/en/function.array-push.php
	////////////////////////////////////////////////////////////////////////////
	public function push() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_push', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//POP ITEMS OUT OF THE END OF THIS OBJECT
	//http://php.net/manual/en/function.array-pop.php
	////////////////////////////////////////////////////////////////////////////
	public function pop() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_pop', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//POP ITEMS OUT OF THE END OF THIS OBJECT
	//http://php.net/manual/en/function.array-shift.php
	////////////////////////////////////////////////////////////////////////////
	public function shift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_shift', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//PUSH ITEMS ONTO THE BEGINNING OF THIS OBJECT
	//http://php.net/manual/en/function.array-unshift.php
	////////////////////////////////////////////////////////////////////////////
	public function unshift() {
		$args = func_get_args();
		array_unshift($args, NULL);
		$args[0] = &$this->__array;
		return call_user_func_array('array_unshift', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE ARRAYS
	//http://php.net/manual/en/function.array-diff.php
	////////////////////////////////////////////////////////////////////////////
	public function diff() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE ARRAYS WITH INDEX CHECK
	//http://php.net/manual/en/function.array-diff-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function diff_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_assoc', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE ARRAY KEYS
	//http://php.net/manual/en/function.array-diff-key.php
	////////////////////////////////////////////////////////////////////////////
	public function diff_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_diff_key', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//INTERSECTION OF ARRAYS
	//http://php.net/manual/en/function.array-intersect.php
	////////////////////////////////////////////////////////////////////////////
	public function intersect() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//INTERSECTION OF ARRAYS WITH INDEX CHECK
	//http://php.net/manual/en/function.array-intersect-assoc.php
	////////////////////////////////////////////////////////////////////////////
	public function intersect_assoc() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_assoc', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//INTERSECTION OF ARRAYS COMPARING ONLY KEYS
	////////////////////////////////////////////////////////////////////////////
	public function intersect_key() {
		$args = func_get_args();
		array_unshift($args, $this->__array);
		return call_user_func_array('array_intersect_key', $args);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET ALL OF THE KEYS IN THIS OBJECT
	//http://php.net/manual/en/function.array-keys.php
	////////////////////////////////////////////////////////////////////////////
	public function keys($search_value=null, $strict=false) {
		return array_keys($this->__array, $search_value, $strict);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A SLICE OF THE OBJECT
	//http://php.net/manual/en/function.array-slice.php
	////////////////////////////////////////////////////////////////////////////
	public function slice($offset, $length=NULL, $preserve_keys=false) {
		return array_slice($this->__array, $offset, $length, $preserve_keys);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP MAGIC METHOD - ACCESS AS OBJECT
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __set($key, $value) {
		$this->__array[$key]		= $value;
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ARRAY ACCESS
	//http://php.net/manual/en/class.arrayaccess.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetSet($key, $value) {
		if (is_null($key)) {
			$this->__array[]		= $value;
		} else {
			$this->__array[$key]	= $value;
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP MAGIC METHOD - ACCESS AS OBJECT
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function &__get($key) {
		return $this->__array[$key];
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ARRAY ACCESS
	//http://php.net/manual/en/class.arrayaccess.php
	////////////////////////////////////////////////////////////////////////////
	public function &offsetGet($key) {
		return $this->__array[$key];
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP MAGIC METHOD - ACCESS AS OBJECT
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __isset($key) {
		return isset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ARRAY ACCESS
	//http://php.net/manual/en/class.arrayaccess.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetExists($key, $isset=true) {
		return $isset
			? isset($this->__array[$key])
			: array_key_exists($key, $this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP MAGIC METHOD - ACCESS AS OBJECT
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __unset($key) {
		unset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ARRAY ACCESS
	//http://php.net/manual/en/class.arrayaccess.php
	////////////////////////////////////////////////////////////////////////////
	public function offsetUnset($key) {
		unset($this->__array[$key]);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ITERATOR
	//http://php.net/manual/en/class.iterator.php
	////////////////////////////////////////////////////////////////////////////
	public function rewind() {
		reset($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ITERATOR
	//http://php.net/manual/en/class.iterator.php
	////////////////////////////////////////////////////////////////////////////
	public function current() {
		return current($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ITERATOR
	//http://php.net/manual/en/class.iterator.php
	////////////////////////////////////////////////////////////////////////////
	public function key() {
		return key($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ITERATOR
	//http://php.net/manual/en/class.iterator.php
	////////////////////////////////////////////////////////////////////////////
	public function next() {
		return next($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP ITERATOR
	//http://php.net/manual/en/class.iterator.php
	////////////////////////////////////////////////////////////////////////////
	public function valid() {
		$key = key($this->__array);
		return ($key !== NULL && $key !== FALSE);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET A JSON REPRESENTATION OF THIS OBJECT
	//http://php.net/manual/en/function.json-encode.php
	////////////////////////////////////////////////////////////////////////////
	public function json() {
		return pudl::jsonEncode($this->__array);
	}




	////////////////////////////////////////////////////////////////////////////
	//GET AN ARRAY FROM THIS OBJECT OF THE GIVEN KEYS ONLY
	////////////////////////////////////////////////////////////////////////////
	public function extract($keys) {
		$return = [];
		if (!is_array($keys)) $keys = func_get_args();
		foreach ($keys as $key) {
			$return[$key] = $this->__array[$key];
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//COPY SOURCE ARRAY INTO OBJECT, BUT ONLY FOR A GIVEN SET OF KEYS
	////////////////////////////////////////////////////////////////////////////
	public function extend($source, $keys) {
		if (!pudl_array($keys)) $keys = [$keys];
		foreach ($keys as $key) {
			$this->__array[$key] = $source[$key];
		}
	}




	////////////////////////////////////////////////////////////////////////////
	//RUN A CALLBACK FUNCTION FOR EVERY ITEM
	////////////////////////////////////////////////////////////////////////////
	public function process($callback) {
		$return	= [];
		foreach ($this->__array as $key => $item) {
			$return[$key] = call_user_func($callback, $item, $key);
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//CHECK TO SEE IF THE GIVEN VALUE EXISTS
	////////////////////////////////////////////////////////////////////////////
	public function hasValue($value, $strict=false) {
		return in_array($value, $this->__array, $strict);
	}




	////////////////////////////////////////////////////////////////////////////
	//TRUE		CHECK TO SEE IF THE KEY EXISTS
	//FALSE		CHECK TO SEE IF THE KEY DOESN'T EXIST
	//OTHER		CHECK TO SEE IF THE KEY'S VALUE === GIVEN VALUE
	////////////////////////////////////////////////////////////////////////////
	public function has($key, $value=true) {
		if (is_array($value)) {
			if (!isset($value[$key])) return false;
			$value = $value[$key];

		} else if (is_object($value)) {
			if (!isset($value->{$key})) return false;
			$value = $value->{$key};
		}


		if (!isset($this->__array[$key])) {
			return $value === false;
		}

		if ($value === true) {
			return !empty($this->__array[$key]);
		}

		return $this->__array[$key] === $value;
	}




	////////////////////////////////////////////////////////////////////////////
	//PARTITION THE ROW INTO MULTIPE COLUMNS
	//http://php.net/manual/en/function.array-chunk.php#75022
	////////////////////////////////////////////////////////////////////////////
	public function partition($columns) {
		$columns = (int) $columns;
		if ($columns < 1) return [];

		$count	= count($this->__array);
		$length	= (int)($count / $columns);
		$mod	= $count % $columns;
		$return	= [];
		$offset	= 0;

		for ($i=0; $i<$columns; $i++) {
			$width		= ($i < $mod) ? $length + 1 : $length;
			$return[]	= array_slice($this->__array, $offset, $width);
			$offset		+= $width;
		}

		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//TRUE:		GET THE CURRENT SNAPSHOT
	//FALSE:	TAKE A NEW SNAPSHOT
	////////////////////////////////////////////////////////////////////////////
	public function snapshot($return=false) {
		if ($return) return $this->__snapshot;
		$this->__snapshot = $this->__array;
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE CURRENT DATA WITH SNAPSHOT DATA, RETURNING AN ARRAY OF CHANGES
	////////////////////////////////////////////////////////////////////////////
	public function compare() {
		if (empty($this->__snapshot)) return [];
		return array_diff_assoc($this->__array, $this->__snapshot);
	}




	////////////////////////////////////////////////////////////////////////////
	//PHP MAGIC METHOD - USED WITH VAR_DUMP
	//http://php.net/manual/en/language.oop5.magic.php
	////////////////////////////////////////////////////////////////////////////
	public function __debugInfo() {
		return $this->__array;
	}




	////////////////////////////////////////////////////////////////////////////
	//PRIVATE MEMBER VARIABLES
	////////////////////////////////////////////////////////////////////////////
	private $__array	= [];
	private $__snapshot	= false;

}
