<?php
/**
 * 链接redis适配器
 * @author http://www.xiaocai.name/about/
 * @since  2015-11-02
 */
class Database_Plugin{
    
    private $_connect;

    public function __construct(){
        $config = SimpleConfig::getInstance();
        $redis  = new Predis\Client([
            'scheme' => 'tcp',
            'host'   => $config['redis_ip'],
            'port'   => $config['redis_prot'],
        ]);
        $this->_connect = $redis;
    }

    public function getConnect(){
        return $this->_connect;
    }

}