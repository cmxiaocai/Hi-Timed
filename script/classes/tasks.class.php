<?php
class TimingCall_Tasks extends TimingCall_Base{

    public function checkEnv(){
        $log_dir = $this->_conf['log_dir'];
        if( !is_writable($log_dir) ){
            exit("目录'{$log_dir}'无法写入");
        }
    }

    public function upDaemonTime(){
        $this->Redis->set($this->_keys['last_time'], $this->_time);
    }

    public function readExecuteTasks(){
        $uniqids = $this->_readUniqids();
        if(!$uniqids){
            return array();
        }
        $tasks = $this->Redis->mGet($uniqids);
        if(empty($tasks)){
            return array();
        }
        foreach ($tasks as $key => $value) {
            $tasks[$key] = json_decode($value, true);
        }
        return $tasks;
    }

    private function _readUniqids(){
        //守护进程持续运行后zRangeByScore方法出现报错,之后守护进程出现假死现象?
        //$uniqids = $this->Redis->zRangeByScore($this->_keys['task_list'], 100, time(), array('withscores' => TRUE) );
        //return array_keys($uniqids);
        $time    = time();
        $uniqids = array();
        $list    = $this->Redis->zrange($this->_keys['task_list'], 0, -1, array('WITHSCORES'=>true));
        foreach ($list as $key => $value) {
            $score = intval($value);
            if( $score <= $time && $score > 0 ){
                $uniqids[] = $key;
            }
        }
        file_put_contents('/tmp/crontab_error.log', '['.date('H:i:s').'] =>'.count($uniqids)."\r\n", FILE_APPEND);
        return $uniqids;
    }

}