<?php

/**
 * 任务执行时间实体
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class taskExecuteTimeEntity{

    private $Form;
    public function __construct(TaskFormEntity $entity){
        $this->Form = $entity;
    }

    public function compute(){
        $param  = $this->Form->param;
        $type   = $this->Form->type;
        $action = '_compute_'.$type;
        return $this->$action($param);
    }

    private function _compute_circle($param){
        $add = 0;
        $add+= $param['second'];
        $add+= $param['minute'] *60;
        $add+= $param['hour']   *60 *60;
        $add+= $param['day']    *24 *60 *60;
        return time()+$add;
    }

    private function _compute_timing($param){
        $time       = time();
        if( $param['week'] > 0 ){
            $week       = intval($param['week']) - 1;
            $week_arr   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
            $str_time   = "{$param['hour']}:{$param['minute']}:{$param['second']}";
            $this_week  = date("Y-m-d", strtotime("this {$week_arr[$week]}"));
            $next_week  = date("Y-m-d", strtotime("+1 week last {$week_arr[$week]}"));
            if( strtotime($this_week.' '.$str_time) > $time ){
                return strtotime($this_week.' '.$str_time);
            }
            return strtotime($next_week.' '.$str_time);
        }else if( $param['hour'] > 0 ){
            $str_time = "{$param['hour']}:{$param['minute']}:{$param['second']}";
            $this_day = date('Y-m-d');
            $next_day = date('Y-m-d', strtotime('+1 day'));
            if( strtotime($this_day.' '.$str_time) > $time ){
                return strtotime($this_day.' '.$str_time);
            }
            return strtotime($next_day.' '.$str_time);
        }else if( $param['minute'] > 0 ){
            $str_time = "{$param['minute']}:{$param['second']}";
            $this_min = date('Y-m-d H');
            $next_min = date('Y-m-d H', strtotime('+1 hour'));
            if( strtotime($this_min.':'.$str_time) > $time ){
                return strtotime($this_min.':'.$str_time);
            }
            return strtotime($next_min.':'.$str_time);
        }else{
            return $time+intval($param['second']);
        }
    }

    private function _compute_once($param){
        return strtotime($param['once']);
    }

}