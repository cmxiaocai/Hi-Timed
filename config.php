<?php

return array(
    'redis_ip'   => '127.0.0.1',
    'redis_prot' => '6379',
    'log_dir'    => '/data/crontab_log',

    'redis_keys' => array(
        'last_time' => 'DAEMON:LAST_RUNTIME',
        'task_list' => 'DAEMON:TASK_LIST',
        'task_item' => 'DAEMON:TASK_ITME:'
    ),
    'action_list'     => array('ssh', 'curl'),
    'task_type'       => array('circle', 'once', 'timing'),
    'execute_timeout' => 300
);