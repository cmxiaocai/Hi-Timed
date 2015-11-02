<?php
class TimingCall_Config{
    static $instance;
    static public function getInstance(){
        if(!self::$instance){
            self::$instance = require(ROOTDIRECTORY_PATH.'/config.php');
        }
        return self::$instance;
    }
}