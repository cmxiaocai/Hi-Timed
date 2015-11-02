<?php
require APPLICATION_PATH.'/entity/taskEntity.php';
require APPLICATION_PATH.'/entity/taskFormEntity.php';
require APPLICATION_PATH.'/entity/taskExecuteTimeEntity.php';

/**
 * 创建定时任务
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskCreateAction extends BaseAction{

    public function __construct(){
        parent::__construct();
        if(!empty($_POST)){
            $this->_saveData();
            $this->responseJson(200, '创建成功');
        }
        SimpleView::display('task_create.html');
    }

    private function _saveData(){
        $Entity            = new TaskEntity();
        $FormEntity        = new TaskFormEntity($_POST);
        $ExecuteTimeEntity = new taskExecuteTimeEntity($FormEntity);
        $execute_time      = $ExecuteTimeEntity->compute();
        $Entity->create($FormEntity, $execute_time);
    }

}