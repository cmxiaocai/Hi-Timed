<?php
require APPLICATION_PATH.'/entity/taskEntity.php';
require APPLICATION_PATH.'/entity/taskLogEntity.php';

/**
 * 任务日志查看
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class TaskLogAction extends BaseAction{

    public function __construct(){
        parent::__construct();

        $uniqid = $this->getUniqid();
        $result = $this->_readLogData($uniqid);
        
        SimpleView::assign('info', $result['info']);
        SimpleView::assign('files', $result['files']);
        SimpleView::assign('log_dir', $this->Config['log_dir']);
        SimpleView::display('task_log.html');
    }

    private function _readLogData($uniqid){
        $entity    = new TaskEntity($uniqid);
        $entityLog = new TaskLogEntity($entity);
        $info      = $entity->getData();
        $files     = $entityLog->getFiles();
        return array(
            'info'  => $info,
            'files' => $files
        );
    }

    

}