<?php
class Process {
    //for parent
    protected $resource; //process resource
    public $pipes;  //process pipes
    public $php_path = 'php'; //system php path
    public $script_path = ''; //运行脚本
    protected $start_time; //subproces start time
    protected $no = 0; //subprocc no
    public $name = ''; //subprocc name
    
    static protected $list = array();
    static protected $list_count = 0;
    
    //for sub Process
    static protected $subName = '';
    static protected $argv = array();
    
    //判断是否是子进程
    static public function isSubProcess(){
        //记录结果
        static $ret = null;
        if ($ret === null){
            $ret = false;
            $name = '';
            foreach($GLOBALS['argv'] as $v){
                if (strncmp('__subProcessName=', $v, 17) == 0){
                    $name = substr($v, 17);
                    $ret = true;
                    break;
                }
            }

            if ($ret){
                self::initSubProcess($name);
            }else{
                self::initProcess();
            }
        }
        return $ret;
    }
    
    //for process
    //获取子进程列表
    static function getList(){
        return self::$list;
    }
    
    //获取活跃子进程
    static function getActiveList(){
        $list = array();
        foreach(self::$list as $v){
            if ($v->isRunning()){
                $list[] = $v;
            }
        }
        
        return $list;
    }
    
    //初始化函数
    function __construct($argv=array(), $name='') {
        if (!$name){
            $name = 'subProcess' . self::$list_count;
        }
        $this->name = $name;
        
        //设置php绝对路径
        if (isset($_ENV['_']) && is_file($_ENV['_'])){
            $this->php_path = $_ENV['_'];
        }

        if (!is_file($this->script_path)){
            $path = $_SERVER['PWD'] . '/' . basename($_SERVER['SCRIPT_FILENAME']);
            if (!is_file($path)){
                throw new Exception('Not locate script path');
            }
            $this->script_path = $path;
        }

        //检测script脚本
        
        //检测是否是linux
        if (file_exists('/dev/stdout')){
            $descriptorspec    = array(
                0 => array('pipe', 'r'),
                1 => array('file', '/dev/stdout', 'a'),
                2 => array('file', '/dev/stderr', 'a'),
            );          
        }else{
            $descriptorspec    = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            );
        }

        $sub_env = $_ENV;
        $sub_env['__argv'] = serialize($argv);
        
        $this->start_time = time(); 

        $this->resource    = @proc_open($this->php_path . ' ' . $this->script_path . ' __subProcessName=' . $name, $descriptorspec, $this->pipes, null, $sub_env);

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
    
    //终止子进程
    function kill(){
        proc_terminate($this->resource);
    }
    
    //关闭子进程
    function close(){
        proc_close($this->resource);
    }
    
    //获取执行时间
    function getRunTime(){
        return time() - $this->start_time;
    }
    
    //主程序初始化
    static function initProcess(){
        $class = __CLASS__;
        //主程序终止时候,终止子程序
        if (function_exists('pcntl_signal')){
            pcntl_signal(SIGTERM, array($class, 'closeAllSubProcess'));  
            pcntl_signal(SIGINT, array($class, 'closeAllSubProcess'));
        }
        register_shutdown_function(array($class, 'closeAllSubProcess'));

        
    }
    
    //关闭所有进程
    static function closeAllSubProcess(){
        //只执行一次
        static $is_first = 1;
        if ($is_first){
            foreach(self::$list as $v){
                echo "close " . $v->name . "\n";
                if ($v->isRunning()){
                    $v->kill();
                }
                $v->close();
            }
            $is_first = 0;
        }
    }
    
    //for subProcess
    //初始化子进程参数
    static  function initSubProcess($name){
        self::$subName = $name;
        $tmp = array();
        if (isset($_ENV['__argv'])){
            $tmp = unserialize($_ENV['__argv']);
        }
        self::$argv = $tmp;
    }
    
    //获取进程名称
    static function getName(){
        return self::$subName;
    }
    
    //获取参数
    static function getArgv(){
        return self::$argv;
    }
}

//return;
if (!Process::isSubProcess()){
    //主进程
    
    //开启2个子进程
    $h = new Process(array('sleep'=>8));
    $h1 = new Process(array('sleep'=>20));

    //等待子进程结束
    while(Process::getActiveList()){
        sleep(1);
    }
    
    echo "main process end\n";
}else{
    //子进程
    $argv = Process::getArgv();  //获取主进程中参数
    if (isset($argv['sleep'])){
        echo "Process " . Process::getName() . " will sleep " . $argv['sleep'] . "\n";
        for($i=0; $i< $argv['sleep']; $i++){
            sleep(1);
            echo sprintf("Pno. %s is sleep seq no. %s\n", Process::getName(), $i);
        }
        echo "Process " . Process::getName()  . " is end\n";
    }else{
        echo Process::getName() ."argv error!\n";
    }
}


?>