<?php
require APPLICATION_PATH.'/entity/taskQueryEntity.php';

/**
 * 查看任务列表
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class TaskListAction extends BaseAction{

    public function __construct(){
        parent::__construct();
        SimpleView::assign('tasks', $this->_readTasksList());
        SimpleView::display('task_list.html');
    }

    public function _readTasksList(){
        return (new TaskQueryEntity())->getList();
    }

}