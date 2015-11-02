<?php

ini_set('date.timezone','Asia/Shanghai');
define('ROOTDIRECTORY_PATH', dirname(dirname(__FILE__)));
require 'classes/base.class.php';
require 'classes/config.class.php';
require 'classes/handle.class.php';
require 'classes/redis.class.php';
require 'classes/tasks.class.php';
require ROOTDIRECTORY_PATH.'/vendor/autoload.php';

swoole_timer_add(500, function() {
    try {
        callback_timer_function();
    } catch (Exception $e) {
        file_put_contents('/tmp/crontab_error.log', '['.date('H:i:s').'] =>'.json_encode($e)."\r\n", FILE_APPEND);
    }
});


function callback_timer_function(){
    $Handle = new TimingCall_Tasks();
    $Handle->upDaemonTime();
    $tasks = $Handle->readExecuteTasks();
    foreach ($tasks as $task) {
        $process = new swoole_process('callback_process_function', true);
        $process->write(json_encode($task));
        $process->start();
        $process->wait(false);
    }
}

function callback_process_function(swoole_process $worker){
    set_time_limit(300);
    $task   = json_decode($worker->read(), true);
    $worker->name('crontab.php worker='.$task['uniqid']);
    $Handle = new TimingCall_Handle($task);
    $Handle->upNextExecuteTime();
    $Handle->refreshStatistics();
    $Handle->Execute();
    $worker->exit(0);
}

echo "start succuss.\r\n";