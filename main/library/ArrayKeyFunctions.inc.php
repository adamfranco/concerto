<?php

 if (!function_exists("array_intersect_key")) {
  /**
   * Computes the intersection of arrays using keys for comparison
   *
   * implementation of PHP' CVS array_intersect_key()
   *
   * array_intersect_key() returns an array containing all the values
   * of array1 which have matching keys that are present in all the
   * arguments.
   *
   * might not be exactly equivalent with php.net/array_intersect_key
   * since we do not use === for comparison.
   *
   * will trigger an warning and return FALSE if one of the arguments
   * is not an array or if less than one argument is given.
   *
   * @see http://php.net/array_intersect_key
   *
   * @author: The Anarcat, modified by bishop
   * @license: public domain
   */
	function array_intersect_key() {
	   $numArgs = func_num_args();
	   if (2 <= $numArgs) {
		   $arrays = func_get_args();
		   for ($idx = 0; $idx < $numArgs; $idx++) {
			   if (! is_array($arrays[$idx])) {
				   trigger_error('Parameter ' . ($idx+1) . ' is not an array', E_USER_ERROR);
				   return false;
			   }
		   }
	
		   foreach ($arrays[0] as $key => $val) {
			   for ($idx = 1; $idx < $numArgs; $idx++) {
				   if (! array_key_exists($key, $arrays[$idx])) {
					   unset($arrays[0][$key]);
				   }
			   }
		   }
	
		   return $arrays[0];
	   }
	
	   trigger_error('Not enough parameters; two arrays expected', E_USER_ERROR);
	   return false;
	}
}

?>