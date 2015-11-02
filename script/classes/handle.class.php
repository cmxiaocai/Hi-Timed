<?php
class TimingCall_Handle extends TimingCall_Base{

    private $_task;
    public function __construct($task){
        parent::__construct();
        $this->_task = $task;
    }

    public function Execute(){
        $task   = $this->_task;
        $action = $task['action'];
        if( !in_array($action, $this->_conf['action_list'] ) ){
            return false;
        }
        $action = '_action_'.$action;
        $this->$action($task['uniqid'], $task['content']);
    }

    public function refreshStatistics(){
        $uniqid = $this->_task['uniqid'];
        $this->Redis->incrby($this->_keys['task_item'].$uniqid.':runnum', 1);
        $this->Redis->set($this->_keys['task_item'].$uniqid.':lasttime', $this->_time);
    }

    public function upNextExecuteTime(){
        $task    = $this->_task;
        $uniqid  = $this->_task['uniqid'];
        $uptime  = $this->_computeNextExecuteTime($task['type'], $task['param']);
        $this->Redis->zAdd($this->_keys['task_list'], $uptime, $this->_keys['task_item'].$uniqid);
    }

    private function _computeNextExecuteTime($type, $param){
        $action = $type;
        if( !in_array($action, $this->_conf['task_type'] ) ){
            return 0;
        }
        $action = '_compute_'.$action;
        return $this->$action($param);
    }

    private function _compute_circle($param){
        $add = 0;
        $add+= $param['second'];
        $add+= $param['minute'] *60;
        $add+= $param['hour']   *60 *60;
        $add+= $param['day']    *24 *60 *60;
        return $this->_time+$add;
    }

    private function _compute_timing($param){
        $time     = $this->_time;
        $str_time = "{$param['hour']}:{$param['minute']}:{$param['second']}";
        if( $param['week'] > 0 ){
            $week      = intval($param['week']) - 1;
            $week_arr  = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            $next_week = date("Y-m-d", strtotime("+1 week last {$week_arr[$week]}"));
            return strtotime($next_week.' '.$str_time);
        }else if( $param['hour'] > 0 ){
            $next_day = date('Y-m-d', strtotime('+1 day'));
            return strtotime($next_day.' '.$str_time);
        }else if( $param['minute'] > 0 ){
            $str_time = "{$param['minute']}:{$param['second']}";
            $next_min = date('Y-m-d H', strtotime('+1 hour'));
            return strtotime($next_min.':'.$str_time);
        }else{
            return $this->_time+intval($param['second']);
        }
    }

    private function _compute_once($param){
        return 0;
    }

    private function _action_curl($uniqid, $url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT,300);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array(
            'time'   => $this->_time,
            'uniqid' => $uniqid
        ));
        $output = curl_exec($ch);
        if($output === FALSE) {
            $output = curl_error($ch);
        }
        $this->_writeLog($uniqid, $output);
        curl_close($ch);
    }

    private function _action_ssh($uniqid, $command){
    }

    private function _writeLog($uniqid, $content){
        $dir     = $this->_conf['log_dir'].'/'.$uniqid.'/';
        $file    = date('Ymd').'.log';
        $content = '['.date('Y-m-d H:i:s', $this->_time).'] '.$content."\r\n";
        $this->_mkdir($dir);
        file_put_contents($dir.$file, $content, FILE_APPEND);

        $dir     = $this->_conf['log_dir'].'/main/';
        $this->_mkdir($dir);
        file_put_contents($dir.$file, $content, FILE_APPEND);
    }

    private function _mkdir($dir){
        if( !file_exists($dir) ){
            mkdir($dir);
        }
    }
}