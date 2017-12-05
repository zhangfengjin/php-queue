<?php
/**
 * Demo
 * User: zhangfengjin
 * Date: 2017/11/23
 */
require_once __DIR__ . "/../vendor/autoload.php";


//队列调用方式
$bootStrap = new \XYLibrary\Bootstrap\Bootstrap();
$bootStrap->bootstrap();
$job = new \XYLibrary\SendEmailJob();
\XYLibrary\Facade\Queue::push($job, "send1");
\XYLibrary\Facade\Queue::push($job, "send2");
\XYLibrary\Facade\Queue::push($job, "send1");
\XYLibrary\Facade\Queue::push($job, "send3");
\XYLibrary\Facade\Queue::later(5, $job, "send1");
\XYLibrary\Facade\Queue::later(5, $job, "send2");
\XYLibrary\Facade\Queue::later(5, $job, "send2");
\XYLibrary\Facade\Queue::later(5, $job, "send3");
echo "end\r\n";









