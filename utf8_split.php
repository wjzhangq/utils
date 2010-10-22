<?php
var_dump(utf8_sub_str('hello 小姐 韩语中字全集', 9));



function utf8_split($str){
	$bin11 = 0xC0;
	$bin10 = 0x80;
	
	$ch11 = chr($bin11);
	$ch10 = chr($bin10);
	
	$data = array();
	
	$str_len = strlen($str); //str length
	$pos = -1; //postion
	
	for($i=0; $i<$str_len; $i++){
		if(($str{$i} & $ch11) != $ch10){
			++$pos;
		}
		isset($data[$pos]) or $data[$pos] = '';
		$data[$pos] .= $str{$i};
	}

	return $data;
}


function utf8_width_split($str){
	$bin11 = 0xC0;
	$bin10 = 0x80;
	
	$ch11 = chr($bin11);
	$ch10 = chr($bin10);
	
	$data = array();
	
	$str_len = strlen($str); //str length
	$pos = -1; //postion
	
	for($i=0; $i<$str_len; $i++){
		$tmp = $str{$i} & $ch11;
		if ($tmp == $ch11){
			//muti btye header
			++$pos;
		}else if ($tmp == $ch10){
			//muti bye other
		}else{
			//ascii
			if ($pos < 0 || isset($data[$pos][1])){
				++$pos;
			}
		}
		
		isset($data[$pos]) or $data[$pos] = '';
		$data[$pos] .= $str{$i};
	}

	return $data;
}

function utf8_sub_str($str, $n=0){
	$ret = '';
	if ($n > 0){
		$list = utf8_width_split($str);
		$ret = implode('', array_slice($list, 0, $n));
	}
	
	return $n > 0 ? $ret : $str;
}


?>