## 项目说明

当你拥有足够复杂的业务系统时往往需要在上百台服务器上部署不同的crontab任务，那么在接下来将会给你带来许许多多的问题：

*忘记了crontab部署在哪台服务器上？
*不知道线上跑了哪些任务，都做些什么，负责人是谁？
*不能确定任务有没有在运行?
*没有足够的权限查看或部署crontab任务!
*忘记配置开启启动，宕机后未执行任务！
*输出的日志不统一，并且无法得知是否执行成功？
*存在单点的风险，但又不得不单点执行crontab

Hi Timed的目标是为自身没有基础建设支撑的团队提供一套便于二次开发的低成本的定时任务 调度与管理系统。系统本身是基于swoole实现，并且自身不处理业务逻辑只负责任务的调度和管理，每个任务可设置多样化的周期执行时间和调度方式异步运行。

github: [https://github.com/cmxiaocai/Hi-Timed](https://github.com/cmxiaocai/Hi-Timed "https://github.com/cmxiaocai/Hi-Timed")

## 目录结构

> adminweb和script分别是管理后台、Cli脚本，两个目录公用config和composer，可根据实际情况决定是否部署。

    -adminweb               可视化web管理后台
        ├─ +action          请求控制器
        ├─ +entity          实体类
        ├─ +adapter         适配器
        ├─ +template        后台视图模板
        └─ +webroot         对外nginx解析路径
    -script                 命令行客户端脚本
        ├─ +classes         执行脚本逻辑类
        ├─ cmd.php          运行脚本
        └─ heartbeat.sh     守护进程检测脚本
    composer.json           composer
    config.php              配置文件


## 部署步骤

**1.composer安装依赖:**

> 依赖predis类库，采用composer装载。详见：https://getcomposer.org/

    cd /{your_directory}/
    composer install

**2.安装swoole扩展:**

> 依赖swoole扩展，请先确保swoole扩展可用。详见：http://www.swoole.com/

    wget http://pecl.php.net/get/swoole-1.7.19.tgz
    tar -zvxf swoole-1.7.19.tgz
    cd swoole-1.7.19
    /usr/local/php/bin/phpize
    ./configure --with-php-config=/usr/local/php/bin/php-config
    make && make install

**3.配置nginx解析路径:**

> 请将root指向到 adminweb/webroot/ 目录下

    location / {
        index index.php;
        root /{your_directory}/adminweb/webroot/;
    }

**3.修改config.php配置:**

> 修改配置中的redis服务地址和日志存储目录，若部署多台需要确保log_dir目录共享存储

    return array(
        'redis_ip'   => '172.0.0.1',
        'redis_prot' => '6379',
        'log_dir'    => '/data/crontab_log',
        ...
    );

**4.部署crontab检测脚本:**

> 将script/heartbeat.sh脚本添加至crontab中，用于检测守护进程是否中断，若中断则重启守护进程
> 注意: 需要确保heartbeat.sh脚本中COMMAND参数路径正确(默认是/usr/local/php/bin/php)。

    */1 * * * * root /bin/sh /{your_directory}/script/heartbeat.sh >> /tmp/crontab_run.log

> heartbeat.sh脚本使用说明

    /bin/sh /{your_directory}/script/heartbeat.sh        #用于自动检测守护进程状态
    /bin/sh /{your_directory}/script/heartbeat.sh start  #启动一条新守护进程
    /bin/sh /{your_directory}/script/heartbeat.sh stop   #终止守护进程


**5.访问管理后台:**

> 系统自身不提供账户体系，可修改adminweb/adapter/user.php接入第三方账户。

    http://127.0.0.1/index.php?r=user/login
    默认账号:admin
    默认密码:123123
    