<?php

/**
 * 任务实体对象
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskEntity{

    private $_uniqid;
    private $_data;
    private $Database;
    public  $Config;
    public  $Keys;

    public function __construct($uniqid=false){
        $this->Database = (new Database_Plugin())->getConnect();
        $this->Config   = SimpleConfig::getInstance();
        $this->Keys     = $this->Config['redis_keys'];
        $this->_uniqid  = $uniqid;
        if($uniqid){
            $this->_readData();
        }
    }

    //读取任务信息
    private function _readData(){
        $this->_data = json_decode(
            $this->Database->get($this->Keys['task_item'].$this->_uniqid),
            true
        );
        if(!$this->_data){
            throw new Exception("没有找到ID等于'{$this->_uniqid}'的任务信息");
        }
    }

    //获得任务id
    public function getUniqid(){
        return $this->_uniqid;
    }

    //任务数据
    public function getData(){
        return $this->_data;
    }

    //任务附加数据
    public function getAdditional(){
        $runnum   = $this->Database->get($this->Keys['task_item'].$this->_uniqid.':runnum');
        $lasttime = $this->Database->get($this->Keys['task_item'].$this->_uniqid.':lasttime');
        return array(
            'runnum'   => $runnum,
            'lasttime' => $lasttime
        );
    }

    //创建任务
    public function create(TaskFormEntity $form, $execute_time){
        $uniqid = $form->uniqid;
        $this->Database->set(
            $this->Keys['task_item'].$uniqid, 
            json_encode($form)
        );
        $this->Database->zAdd(
            $this->Keys['task_list'], 
            $execute_time, 
            $this->Keys['task_item'].$uniqid
        );
    }

    //删除任务
    public function delete(){
        return $this->Database->zrem(
            $this->Keys['task_list'], 
            $this->Keys['task_item'].$this->_uniqid
        );
    }

    //记录被删除
    public function recordDeleted(){
        return $this->Database->sAdd(
            $this->Keys['task_list'].'_DELETE', 
            $this->Keys['task_item'].$this->_uniqid
        );
    }

}