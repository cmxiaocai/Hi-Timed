<?php
class TimingCall_Base{
    protected $Redis;
    protected $_conf;
    protected $_keys;
    protected $_time;

    public function __construct(){
        $this->_time = time();
        $this->_conf = TimingCall_Config::getInstance();
        $this->_keys = $this->_conf['redis_keys'];
        $this->Redis = TimingCall_Redis::getConnect();
    }
}