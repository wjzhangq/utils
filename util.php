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
	
	//自动判断cols是数字还是字串key
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

	// 说明：PHP中二维数组的排序方法
	// 整理：http://www.CodeBit.cn
	 
	/**
	* @package BugFree
	* @version $Id: FunctionsMain.inc.php,v 1.32 2005/09/24 11:38:37 wwccss Exp $
	*
	*
	* Sort an two-dimension array by some level two items use array_multisort() function.
	*
	* sysSortArray($Array,"Key1","SORT_ASC","SORT_RETULAR","Key2"……)
	* @author Chunsheng Wang <wwccss@263.net>
	* @param array $ArrayData the array to sort.
	* @param string $KeyName1 the first item to sort by.
	* @param string $SortOrder1 the order to sort by("SORT_ASC"|"SORT_DESC")
	* @param string $SortType1 the sort type("SORT_REGULAR"|"SORT_NUMERIC"|"SORT_STRING")
	* @return array sorted array.
	*/
	function SortArray($ArrayData,$KeyName1,$SortOrder1 = "SORT_ASC",$SortType1 = "SORT_REGULAR")
	{
		if(!is_array($ArrayData))
		{
			return $ArrayData;
		}

		// Get args number.
		$ArgCount = func_num_args();

		// Get keys to sort by and put them to SortRule array.
		for($I = 1;$I < $ArgCount;$I ++)
		{
			$Arg = func_get_arg($I);
			if(!eregi("SORT",$Arg))
			{
				$KeyNameList[] = $Arg;
				$SortRule[] = '$'.$Arg;
			}
			else
			{
				$SortRule[] = $Arg;
			}
		}

		// Get the values according to the keys and put them to array.
		foreach($ArrayData AS $Key => $Info)
		{
			foreach($KeyNameList AS $KeyName)
			{
				${$KeyName}[$Key] = $Info[$KeyName];
			}
		}

		// Create the eval string and eval it.
		$EvalString = 'array_multisort('.join(",",$SortRule).',$ArrayData);';
		eval ($EvalString);
		return $ArrayData;
	}
	
	
	//把$i按位转换
	function mutSeq($i, $weight){
		$weight_len = count($weight);
		$ret = array_pad(array(), $weight_len, 0);

		for($j=0; $j < $weight_len; $j++){
			$tmp = floor($i / $weight[$j]);
			if ($tmp > 0){
				$ret[$j] = $i % $weight[$j];
				$i = $tmp;
			}else{
				$ret[$j] = $i;
				break;
			}
		}
		return $ret;
	}
	
	//数组乘法
	function array_mult(){
		$list = func_get_args();
		$num_list = count($list);
		if ($num_list < 2){
			throw new Exception('param count must be two at lister');
		}

		$new = array();
		$seq = array();
		$mutl = 1;
		foreach($list as $row){
			if (!is_array($row)){
				throw new Exception('param must be array');
			}
			if (empty($row)){
				throw new Exception('param is a empty array');
			}
			$seq[] = count($row);
			$mutl *= count($row);
		}

		$post_list = array();
		for($i=0; $i < $mutl; $i++){
			$tmp = self::mutSeq($i, $seq);
			$item = array();
			foreach($tmp as $j=>$v){
				$item[] = $list[$j][$v];
			}
			$post_list[] = $item;
		}

		var_dump($post_list);
		return $post_list;
	}
}


$arr = array(
	array(1,2,3),
	array(4,5,6),
	array(7,8,9),
);
$arr1 = array(
	array('a'=>1, 'b'=>2, 'c'=>3),
	array('a'=>4, 'b'=>5, 'c'=>6),
	array('a'=>7, 'b'=>8, 'c'=>9),
);

var_dump(util_array::muti($arr,$arr1, '1=1'));
print_r(util_array::array_mult(array(1,2), array(3,4), array(5,6)));

?>