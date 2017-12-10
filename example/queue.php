<?php
/**
 * Demo
 * User: zhangfengjin
 * Date: 2017/11/23
 */
require_once __DIR__ . "/../vendor/autoload.php";


//队列调用方式 真实业务用参数不传递或true
$bootStrap = new  \XYLibrary\Queue\Bootstrap(false);
$bootStrap->bootstrap();
$job = new \XYLibrary\SendEmailJob();
\XYLibrary\Queue\Facade\Queue::push($job, "send1");
\XYLibrary\Queue\Facade\Queue::push($job, "send2");
\XYLibrary\Queue\Facade\Queue::push($job, "send1");
\XYLibrary\Queue\Facade\Queue::push($job, "send3");
\XYLibrary\Queue\Facade\Queue::later(5, $job, "send1");
\XYLibrary\Queue\Facade\Queue::later(5, $job, "send2");
\XYLibrary\Queue\Facade\Queue::later(5, $job, "send2");
\XYLibrary\Queue\Facade\Queue::later(5, $job, "send3");
echo "end\r\n";









