<?php

/**
 * 表单实体
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class TaskFormEntity{

    public function __construct($post){
        $this->_formatData($post);
        $this->_generateUniqid();
    }

    private function _generateUniqid(){
        $this->uniqid = uniqid();
    }

    private function _formatData($post){
        $param = $post['param'];
        $this->title   = $post['title'];
        $this->type    = $post['type'];
        $this->action  = $post['action'];
        $this->content = $post['content'];
        $this->ctime   = time();
        $this->param   = array(
            'month'  => isset($param['month'])  ? $param['month'] : 0,
            'week'   => isset($param['week'])   ? $param['week']  : 0,
            'day'    => isset($param['day'])    ? $param['day']   : 0,
            'hour'   => isset($param['hour'])   ? $param['hour']  : 0,
            'minute' => isset($param['minute']) ? $param['minute']: 0,
            'second' => isset($param['second']) ? $param['second']: 0,
            'once'   => isset($param['date'])   ? $param['date'].' '.$param['time']:0,
        );
    }

}