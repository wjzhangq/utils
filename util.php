<?php
class util_array{
	//二维数据一列
	function getCol(&$arr, $col=0){
		$col = self::getRealCol($arr, $col);
		$ret = array();
		if ($col !== null){
			foreach($arr as $v){
				$ret[] = $v[$col];
			}
		}
		return $ret;
	}
	
	//二维数据按照某列排序
	function sortByCol($col){
		$col = self::getRealCol($arr, $col);
	}
	
	//将指定列变为索引
	function ColToKey(&$arr, $col){
		$col = self::getRealCol($arr, $col);
		$ret = array();
		if ($col !== null){
			foreach($arr as $v){
				$ret[$v[$col]] = $v;
			}
		}
		
		return $ret;
	}
	
	//二维数组乘法
	function muti(&$arr1, &$arr2,$eq='0=0', $default_row = array()){
		list($col1, $col2) = explode('=', $eq, 2);
		$col1 = self::getRealCol($arr1, $col1);
		$col2 = self::getRealCol($arr2, $col2);
		$ret = array();
		if ($col1 !== null && $col2 !== null){
			$post_arr2 = self::ColToKey($arr2, $col2);
			foreach($arr1 as $k=>$v){
				$row = isset($post_arr2[$v[$col1]]) ? $post_arr2[$v[$col1]] : $default_row;
				$ret[$k] = array_merge($v, $row);
			}
		}
		return $ret;
	}
	
	function getRealCol(&$arr, $col){
		$real_col = null;
		$row = current($arr);
		if (ctype_digit($col) || is_int($col)){
			//如果是数字，检查第几列
			if (!isset($row[$col])){
				//col 为列数，转换为真实key
				$keys = array_keys($row);
				if (isset($keys[$col])){
					$real_col = $keys[$col];
				}
			}else{
				$real_col = $col;
			}
		}else{
			if (isset($row[$col])){
				$real_col = $col;
			}
		}
		
		return $real_col;
	}
}


$arr = array(
	array(1, 2,3),
	array(4,5,6),
	array(7,8,9),
);
$arr1 = array(
	array('a'=>1, 'b'=>2, 'c'=>3),
	array('a'=>4, 'b'=>5, 'c'=>6),
	array('a'=>7, 'b'=>8, 'c'=>9),
);

var_dump(util_array::muti($arr,$arr1, '1=1'));

?>