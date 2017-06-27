<?php



////////////////////////////////////////////////////////////////////////////////
//CUSTOMIZABLE INTERFACE TO FORWARD CALLS TO AN EXISTING PUDL INSTANCE
////////////////////////////////////////////////////////////////////////////////
class pudlClone {




	////////////////////////////////////////////////////////////////////////////
	//CONSTRUCTOR - PASS IN AN EXISTING PUDL INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function __construct($parent) {
		$this->pudl = $parent;
	}




	////////////////////////////////////////////////////////////////////////////
	//FORWARD ANY NON-DEFINED CALLS DIRECTLY TO ASSIGNED PUDL INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public function __call($name, $arguments) {
		return call_user_func_array([$this->pudl, $name], $arguments);
	}




	////////////////////////////////////////////////////////////////////////////
	//FORWARD STATIC CALLS DIRECTLY WITHOUT WORRY ABOUT PUDL INSTANCE
	////////////////////////////////////////////////////////////////////////////
	public static function __callStatic($name, $arguments) {
		return forward_static_call_array(['pudl', $name], $arguments);
	}




	////////////////////////////////////////////////////////////////////////////
	//LOCAL VARIABLES
	////////////////////////////////////////////////////////////////////////////
	protected $pudl;

}