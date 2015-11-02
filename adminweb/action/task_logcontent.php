<?php
require APPLICATION_PATH.'/entity/taskEntity.php';
require APPLICATION_PATH.'/entity/taskLogEntity.php';

/**
 * 查看日志内容
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class TaskLogContentAction extends BaseAction{

    public function __construct(){
        parent::__construct();

        $uniqid    = $this->getUniqid();
        $date      = intval($_GET['date']);
        $entity    = new TaskEntity($uniqid);
        $entityLog = new TaskLogEntity($entity);
        $info      = $entity->getData();
        $content   = $entityLog->getContent($date);

        SimpleView::assign('info', $info);
        SimpleView::assign('date', $date);
        SimpleView::assign('content', $content);
        SimpleView::assign('log_dir', $this->Config['log_dir']);
        SimpleView::display('task_logcontent.html');
    }



}