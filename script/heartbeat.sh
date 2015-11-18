#!/bin/sh

# 定时任务守护进程检测
# @author xiaocai <www.xiaocai.name/about>
# @since  2015-09-18
# 每分钟执行一次，用于检测swoole_timer进程是否存在，并确保始终只有一条进程在运行。


DIRNAME=$(cd `dirname $0`; pwd)
CURRENT_DATE=`date "+%Y%m%d %H:%M:%S"`
RONROOT=$DIRNAME'/cmd.php'
COMMAND='/usr/local/php/bin/php'

TODAYLOG="/tmp/crontab_last_time.log"

function fun_stop {
	RESULT=`ps -ef | grep -e 'cmd.php' | grep 'daemon' | awk '{print $2}' | xargs kill -9`&
	sleep 2
	echo 'stop success'
}

function fun_start {
	sleep 2
	ulimit -c unlimited
	echo "$COMMAND $RONROOT daemon"
	RESULT=`$COMMAND $RONROOT daemon `&
	echo 'start success'
}

#PROCESS_NUM=`ps -A -o stat,ppid,pid,cmd | grep 'cmd.php' | grep -v 'grep' | wc -l`
PROCESS_NUM=`ps -ef | grep 'cmd.php' | grep 'daemon' | wc -l`

echo "--START--"
echo "DATE:$CURRENT_DATE"
echo "NUM:$PROCESS_NUM"

if [ $# -gt 0 ]
then
	
	if [ "$1" = "stop" ];
	then
		echo "stop"
		fun_stop
	fi

	if [ "$1" = "start" ];
	then
		echo "start"
		fun_start
	fi

	if [ "$1" = "restart" ];
	then
		echo "restart"
		fun_stop
		fun_start
	fi

else

	echo 'CHECK'

	if [ $PROCESS_NUM -gt 1 ]
	then
		fun_stop
		fun_start
	fi

	if [ $PROCESS_NUM -lt 1 ]
	then
		fun_start
	fi

	PROCESS_NUM=`ps -ef | grep 'cmd.php' | grep 'daemon' | wc -l`
	echo "NUM:$PROCESS_NUM"

	echo "${CURRENT_DATE}"> ${TODAYLOG}

fi

echo "--END--"
echo ""

# 查看僵尸进程  ps -A -o stat,ppid,pid,cmd | grep php
# 杀死僵尸进程  ps -A -o stat,ppid,pid,cmd | grep php | grep 'Sl' | awk '{print $3}' | xargs kill -9
# 停止守护进程  ps -A -o stat,ppid,pid,cmd | grep -e 'cmd.php' | awk '{print $3}' | xargs kill -9
