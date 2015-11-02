<?php

/**
 * 日志实体
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskLogEntity{

    private $task;
    public function __construct(TaskEntity $task){
        $this->task = $task;
    }

    public function getFiles(){
        $logpath = $this->task->Config['log_dir'].'/'.$this->task->getUniqid();
        $files   = array();
        $handle  = @opendir($logpath);
        if ($handle === false) {
            throw new Exception("抱歉，系统无法打开'{$logpath}'目录，请确保系统有该目录读取权限。");
        }
        while ( false !== ($file = readdir ( $handle )) ) {
            if(in_array($file, array('.', '..'))){
                continue;
            }
            $files[] = $file;
        }
        closedir ( $handle );
        return $files;
    }

    public function getContent($date){
        $filepath = $this->task->Config['log_dir'].'/'.$this->task->getUniqid().'/'.$date.'.log';
        $content  = file_get_contents($filepath);
        $content  = htmlentities($content);
        return $content;
    }

}