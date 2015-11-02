<?php
/**
 * 用户登录请求
 * @author http://www.xiaocai.name/about/
 * @since  2015-09-28
 */
class UserLoginAction extends BaseAction{

    public function __construct(){
        parent::__construct();
        if( !empty($_POST) ){
            $this->_ajaxPost($_POST['account'], $_POST['password']);
            $this->responseJson(1, 'login success');
        }
        SimpleView::display('user_login.html');
    }

    private function _ajaxPost($account, $password){
        return (new User_Plugin())->login($account, $password);
    }

}