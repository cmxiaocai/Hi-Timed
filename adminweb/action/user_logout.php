<?php
/**
 * 用户登出请求
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class UserLogoutAction extends BaseAction{

    public function __construct(){
        parent::__construct();
        $this->User->logout();
        header("Location:index.php?r=user/login");
    }

}