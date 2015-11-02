<?php
require APPLICATION_PATH.'/entity/taskEntity.php';

/**
 * 移除定时任务请求
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskDeleteAction extends BaseAction{

    public function __construct(){
        parent::__construct();
        $this->_deleteById( $this->getUniqid() );
        header("Location:index.php?r=task/list");
    }

    private function _deleteById($uniqid){
        $entity = new TaskEntity($uniqid);
        $entity->delete();
        $entity->recordDeleted();
    }

}