<?php
/**
 * Demo
 * User: zhangfengjin
 * Date: 2017/11/23
 */
require_once __DIR__ . "/../vendor/autoload.php";


//队列调用方式
$bootStrap = new \Queue\Bootstrap\Bootstrap();
$bootStrap->bootstrap();
$job = new \Queue\SendEmailJob();
\Queue\Facade\Queue::push($job, "send1");
\Queue\Facade\Queue::push($job, "send2");
\Queue\Facade\Queue::push($job, "send1");
\Queue\Facade\Queue::push($job, "send3");
\Queue\Facade\Queue::later(5, $job, "send1");
\Queue\Facade\Queue::later(5, $job, "send2");
\Queue\Facade\Queue::later(5, $job, "send2");
\Queue\Facade\Queue::later(5, $job, "send3");
echo "end\r\n";









