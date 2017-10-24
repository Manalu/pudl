<?php


////////////////////////////////////////////////////////////////////////////////
//STATIC API CALLS
////////////////////////////////////////////////////////////////////////////////
trait pudlStatic {




	////////////////////////////////////////////////////////////////////////////
	//UNKNOWN STATIC METHODS ARE CONVERTED INTO PUDLFUNCTION CALLS
	////////////////////////////////////////////////////////////////////////////
	public static function __callStatic($name, $arguments) {
		$value	= new pudlFunction();
		$name	= '_' . strtoupper($name);
		$value->$name = $arguments;
		return $value;
	}




	////////////////////////////////////////////////////////////////////////////
	//SAFELY PASS A COLUMN INTO A QUERY
	////////////////////////////////////////////////////////////////////////////
	public static function column($column, $value=false) {
		return (func_num_args() < 2)
			? new pudlColumn($column)
			: new pudlColumn($column, $value);
	}




	////////////////////////////////////////////////////////////////////////////
	//COMPARE A VALUE AGAINST MULTIPLE COLUMNS
	////////////////////////////////////////////////////////////////////////////
	public static function bravo($value, $columns /* ... */) {
		if (!pudl_array($columns)) {
			$columns = func_get_args();
			array_shift($columns);
		}

		foreach ($columns as &$column) {
			if (!is_string($column)) continue;
			$column = static::column($column);
		}

		return new pudlEquals($value, $columns, ' IN ');
	}




	////////////////////////////////////////////////////////////////////////////
	//UNSAFE - PASS RAW SQL INTO A QUERY - USE CAUTION WITH THIS METHOD!
	////////////////////////////////////////////////////////////////////////////
	public static function raw(/* ...$values */) {
		return (new ReflectionClass('pudlRaw'))
				->newInstanceArgs(func_get_args());
	}




	////////////////////////////////////////////////////////////////////////////
	//FORCE DATATYPE INTO STRING WHEN INSERTING INTO SQL QUERY
	////////////////////////////////////////////////////////////////////////////
	public static function text(/* ...$values */) {
		return (new ReflectionClass('pudlText'))
				->newInstanceArgs(func_get_args());
	}




	////////////////////////////////////////////////////////////////////////////
	//CONVERT A UNIX TIMESTAMP INTO A DATETIME
	////////////////////////////////////////////////////////////////////////////
	public static function date($timestamp=false) {
		return ($timestamp === false)
			? static::now()
			: static::from_unixtime($timestamp);
	}




	////////////////////////////////////////////////////////////////////////////
	//HELPER FUNCTION FOR DATE RANGES FROM UNIX TIMESTAMPS
	////////////////////////////////////////////////////////////////////////////
	public static function daterange($begin, $end) {
		return static::between(static::date($begin), static::date($end));
	}




	////////////////////////////////////////////////////////////////////////////
	//FIND IN SET
	////////////////////////////////////////////////////////////////////////////
	public static function find($column, $values) {
		if (!is_array($values)) $values = explode(',', $values);
		$return = [];
		foreach ($values as $item) {
			$return[] = static::find_in_set($item, static::column($column));
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//-NOT- FIND IN SET
	////////////////////////////////////////////////////////////////////////////
	public static function notFind($column, $values) {
		if (!is_array($values)) $values = explode(',', $values);
		$return = [];
		foreach ($values as $item) {
			$return[] = static::{'!find_in_set'}($item, static::column($column));
		}
		return $return;
	}




	////////////////////////////////////////////////////////////////////////////
	//EXTRACT KEYS FROM A GIVEN ARRAY
	////////////////////////////////////////////////////////////////////////////
	public static function extract($source, $keys) {
		$return = [];
		foreach ($source as $key => $value) {
			if (!array_key_exists($key, $keys)) continue;
			$return[$key] = $value;
		}
		return $return;
	}


}