<?php
/**
 * 用户适配器 - 请在此处接入用户
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class User_Plugin{
    
    public function __construct(){
        if(!isset($_SESSION)){ 
            session_start();
        }
    }

    public function check(){
        if( isset($_SESSION['LOGIN_USER']) && !empty($_SESSION['LOGIN_USER']) ){
            return true;
        }
        return false;
    }

    public function login($account, $password){
        $users = array(
            'admin_xiaocai' => '123123',
            'admin'         => '123123',
        );
        if( !isset($users[$account]) ){
            throw new Exception("The account does not exist");
        }
        if( $users[$account] != $password ){
            throw new Exception("Inconsistent password");
        }
        $_SESSION['LOGIN_USER'] = $account;
        $_SESSION['LOGIN_TIME'] = time();
        return true;
    }

    public function logout(){
        unset($_SESSION['LOGIN_USER']);
        unset($_SESSION['LOGIN_TIME']);
    }

}