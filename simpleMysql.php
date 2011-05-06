<?php
/**
 * 一个Db的简单封装
 * @author:zhangwenjin
 */


class simpleMysql{
	public  $conn;
	var $connect_args;
	var $db_name;
	var $charset = '';
	
	function __construct($charset=''){
		if ($charset){
			$this->charset = $charset;
		}
	}
	
	function getAll($sql, $assoc=true){
		$result = $this->query($sql);

		$ret = array();
		if ($assoc){
			while ($row = mysql_fetch_assoc($result)){
				$ret[] = $row;
			}
		}else{
			while ($row = mysql_fetch_row($result)){
				$ret[] = $row;
			}
		}
		
		return $ret;
	}
	
	function query($sql,$buffer=true){
		$query_fun = $buffer ? 'mysql_query' : 'mysql_unbuffered_query';
		for($i=0; $i < 2; $i++){
			$ret = $query_fun($sql, $this->conn);
			if ($ret !== false){
				break;
			}
			$msg = mysql_error($this->conn);
			$no = mysql_errno($this->conn);
			if ($no == 2006){
				// gone away
				call_user_func_array(array($this,'connect'), $this->connect_args);
				$this->select_db($this->db_name);
			}else{
				throw new Exception($msg, $no);
			}
		}
		
		return $ret;
	}
	
	function connect(){
		$args = func_get_args();
		if ($this->conn){
			mysql_close($this->conn);
			$this->conn = null;
		}
		
		$this->conn = call_user_func_array('mysql_connect', $args);
		if (!$this->conn) {
			throw new Exception(mysql_error(), mysql_errno());
		}
		$this->connect_args = $args;
		if ($this->charset){
			mysql_set_charset($this->charset, $this->conn);
		}
	}
	
	function select_db($name){
		$ret = mysql_select_db($name, $this->conn);
		if (!$ret){
			throw new Exception(mysql_error($this->conn), mysql_errno($this->conn));
		}
		$this->db_name = $name;
	}
	
	function __call($method, $param){
		$method = 'mysql_' . $method;
		if (!function_exists($method)){
			throw new Exception(sprintf('Unknown method "%s"', $method));
		}
		
		if ($method == 'mysql_connect'){
			$this->conn = call_user_func_array('mysql_connect', $param);
			if (!$this->conn) {
				throw new Exception(mysql_error(), mysql_errno());
			}
		}else{
			$ret = call_user_func_array($method, $param);
			if ($ret === false){
				throw new Exception(mysql_error());
			}
			return $ret;
		}
	}
}

?>