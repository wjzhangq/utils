<?php
class Process {
    public $resource;
    public $pipes;	
	public $php_path = 'php';
    public $start_time;
	public $no = 0;
	
	protected $argv;
	
	static protected $list = array();
	static protected $list_count = 0;
	
	//判断是否是子进程
	static public function isChild(){
		$ret = false;
		foreach($GLOBALS['argv'] as $v){
			if (strncmp('__argv=', $v, 7) == 0){
				$tmp = urldecode(substr($v, 7));
				$res = json_decode($tmp);
				if ($res){
					$GLOBALS['argv'] = (array)$res;
					unset($res);
				}else{
					$GLOBALS['argv']  = $tmp;
				}
				$ret = true;
				break;
			}
		}
		
		return $ret;
	}
	
	//获取子进程列表
	static function getList(){
		return self::$list;
	}
	
	static function getActiveList(){
	    $list = array();
	    foreach(self::$list as $v){
	        if ($v->isRunning()){
	            $list[] = $v;
	        }
	    }
	    
	    return $list;
	}
	
	//
    function __construct($argv=array()) {
		$descriptorspec    = array(
            0 => array('pipe', 'r'),
            1 => array('file', '/dev/stdout', 'w'),
            2 => array('file', '/dev/stderr', 'w')
        );
        $argv['no'] = self::$list_count;
        $this->start_time = time();	
        $this->resource    = proc_open($this->php_path . ' ' . __FILE__ . ' __argv=' .urlencode(json_encode($argv)), $descriptorspec, $this->pipes, null, $_ENV);
		$this->no = self::$list_count;
		self::$list[self::$list_count] = &$this;
		self::$list_count ++;
    }

	function __destruct(){
		//$this->kill();
	}
	
    // is still running?
    function isRunning() {
        $status = proc_get_status($this->resource);
        return $status["running"];
    }

	function kill(){
	    proc_terminate($this->resource);
		proc_close($this->resource);
	}

	function getRunTime(){
		return time() - $this->start_time;
	}
}

if (!Process::isChild()){
	$h = new Process(array('sleep'=>10));
	$h1 = new Process(array('sleep'=>5));
	
	while(Process::getActiveList()){
	    sleep(1);
	}
	
	echo "main process end\n";
}else{
    if (isset($argv['sleep'])){
        echo "Process " . $argv['no'] . " will sleep " . $argv['sleep'] . "\n";
        sleep($argv['sleep']);
        echo "Process " . $argv['no'] . " is end\n";
    }else{
        echo "argv error!\n";
    }
}


?>