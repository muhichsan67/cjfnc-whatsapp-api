<?php
  if (!function_exists('validate_required')) {
    function validate_required($name, $string){
      if (!empty($string)) {
          return true;
      }
      error("$name is required");
    }
  }

	if (!function_exists('validate_date')) {
	    function validate_date($name, $string){
	    	if (strtotime($string)) {
		        return true;
	    	}
	    	error("$name is invalid date");
	    }
	}

  if (!function_exists('validate_number')) {
    function validate_number($name, $string){
      if (is_numeric($string)) {
          return true;
      }
      error("$name is invalid number");
    }
  }

?>