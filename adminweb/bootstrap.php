<?php

include APPLICATION_PATH.'/adapter/database.php';
include APPLICATION_PATH.'/adapter/user.php';
require dirname(dirname(__FILE__)).'/vendor/autoload.php';

class SimpleRoute{
    private $_uri;
    public function mapped($uri){
        $this->_uri = strtolower( str_replace('/', '_', $uri) );
        define('SYS_URI', $this->_uri);
    }

    public function callAction(){
        include 'action/'.$this->_uri.'.php';
        $className = str_replace('_', '', $this->_uri).'Action';
        new $className();
    }
}

class SimpleView{
    static $instance;
    static public function getInstance(){
        return self::$instance;
    }
    static public function assign($key, $name){
        self::$instance[$key] = $name;
    }
    static public function display($template){
        include APPLICATION_PATH.'/template/'.$template;
    }
}

class SimpleConfig{
    static $instance;
    static public function getInstance(){
        if(!self::$instance){
            self::$instance = require(APPLICATION_PATH.'/../config.php');
        }
        return self::$instance;
    }
}

class BaseAction{

    protected $Config;
    protected $Keys;

    public function __construct(){
        $this->User    = (new User_Plugin());
        $this->Config  = SimpleConfig::getInstance();
        $this->Keys    = $this->Config['redis_keys'];
        $this->_authLogin();
    }

    public function getUniqid(){
        if( !isset($_GET['id']) ){
            $this->responseJson(450, '请求中缺少有效的LOGID参数，请确保访问的URL地址正确。');
        }
        return $_GET['id'];
    }

    private function _authLogin(){
        if( in_array(SYS_URI, array('user_login','user_logout')) ){
            return true;
        }
        if(!$this->User->check()){
            header("Location:index.php?r=user/login");
        }
    }

    protected function responseJson($code, $message, $data=array()){
        header('Content-type:application/json;charset=utf-8');
        $json = json_encode(array('code'=>intval($code), 'message'=>$message, 'data'=>$data));
        exit($json);
    }
}

class Simple_ExceptionHandler{

    private $Exception;

    public function handler( $exception ) {
        $this->Exception = $exception;
        if( $this->_isAjax() ){
            $this->_responseJson();
        }else{
            $this->_responsePage();
        }
        exit();
    }

    private function _isAjax(){
        if ( isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
             $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ){
            return true;
        }else{
            return false;
        }
    }

    private function _responseJson(){
        header('Content-type:application/json;charset=utf-8');
        $result = array(
            'code'    => $this->Exception->getCode(), 
            'message' => $this->Exception->getMessage()
        );
        echo json_encode($result);
    }

    private function _responsePage(){
        SimpleView::assign('exception', $this->Exception);
        SimpleView::display('error.html');
    }

}

set_exception_handler( array(new Simple_ExceptionHandler(), 'handler') );