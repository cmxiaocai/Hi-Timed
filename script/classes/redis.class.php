<?php
class TimingCall_Redis{

    static public function getConnect(){
        $conf  = TimingCall_Config::getInstance();
        $redis = new Predis\Client([
            'scheme' => 'tcp',
            'host'   => $conf['redis_ip'],
            'port'   => $conf['redis_prot'],
        ]);
        return $redis;
    }

}