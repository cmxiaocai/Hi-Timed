<?php

/**
 * 查询实体
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskQueryEntity{

    public function __construct(){
        $this->Database = (new Database_Plugin())->getConnect();
        $this->Config   = SimpleConfig::getInstance();
        $this->Keys     = $this->Config['redis_keys'];
    }

    public function getList(){
        $uniqids = $this->Database->zRangeByScore(
            $this->Keys['task_list'], 
            0, 
            9999999999999, 
            array('withscores' => TRUE) 
        );
        if(empty($uniqids)){
            return array();
        }
        $tasks = $this->Database->mGet(array_keys($uniqids));
        if(empty($tasks)){
            return array();
        }
        foreach ($tasks as $key => $value) {
            $info = json_decode($value, true);
            $info['last_run_time'] = $uniqids[ $this->Keys['task_item'].$info['uniqid'] ];
            $tasks[$key] = $info;
        }
        return $tasks;
    }

    public function getDelList(){
        
    }

}