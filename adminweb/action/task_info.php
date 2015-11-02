<?php
require APPLICATION_PATH.'/entity/taskEntity.php';

/**
 * 查看任务详细信息
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class TaskInfoAction extends BaseAction{

    public function __construct(){
        parent::__construct();

        $uniqid     = $this->getUniqid();
        $entity     = new TaskEntity($uniqid);
        $info       = $entity->getData();
        $additional = $entity->getAdditional();

        SimpleView::assign('info', $info);
        SimpleView::assign('runnum', $additional['runnum']);
        SimpleView::assign('lasttime', $additional['lasttime']);
        SimpleView::display('task_info.html');
    }

}