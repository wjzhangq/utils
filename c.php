<?php
class C implements Iterator{
	var $data = array();
	var $dataCount = 0;
	var $seq = 0;
	var $maxSeq = 0;
	
	function __construct(){
		$this->data = func_get_args();
		$this->dataCount = count($this->data);
		$max = 1;
		for($i=$this->dataCount - 1; $i>=0; $i--){
			$max *= count($this->data[$i]);
		}
		$this->maxSeq = $max;
	}
	
	public function rewind(){
		foreach($this->data as $k=>$v){
			reset($this->data[$k]);
		}
	}

	public function next($i=-1){
		if ($i == -1){
			$this->seq++;
			$i=$this->dataCount -1;
		}
		
		$ret = next($this->data[$i]);
		if ($ret === false){
			reset($this->data[$i]);
			if ($i > 0){
				$this->next($i - 1);
			}
		}
	}

	public function current(){
		$ret = array();
		for($i=0; $i < $this->dataCount; $i++){
			$ret[$i] = current($this->data[$i]);
		}
		return $ret;
	}

	public function key(){
		return $this->seq;
	}

	public function valid(){
		return $this->seq < $this->maxSeq;
	}
}




$a = new C(array('a', 'b'), array(1, 2), array('x', 'y', 'z'), array('u', 'i','v'));

foreach($a as $k=>$v){
	print_r($v);
}
?>