<?php
class ParamsUtils {
	
	static function refValues($arr) {
		if (version_compare(PHP_VERSION, '5.3.0') >= 0) {
			$refs = array();
			foreach($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	
}
?>