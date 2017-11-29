<?php
/**
 * 守护队列
 * User: zhangfengjin
 * Date: 2017/11/29
 */

namespace Queue;


use Queue\Bootstrap\Bootstrap;

class DaemonQueue
{
    protected $app;

    /**
     * 启动
     * 容器注册
     */
    protected function bootstrap()
    {
        $bootStrap = new Bootstrap();
        $bootStrap->bootstrap();
        $this->app = $bootStrap->getContainer();
    }

    /**
     * 守护队列
     * @param $argvs
     */
    public function daemon($argvs)
    {
        if (count($argvs) > 1) {
            $this->arrayChangValueCase($argvs);
            $handlers = explode(":", $argvs[1]);
            $workerCount = 1;
            $method = "";
            switch (count($handlers)) {
                case 2:
                    $method = $handlers[1];
                    break;
                case 3:
                    $method = $handlers[1];
                    $workerCount = $handlers[2];
                    break;
                default:
                    $this->printLog();
                    break;
            }
            $options = $this->resolveOptions($argvs);
            $this->bootstrap();
            switch ($method) {
                case "stop":
                    $this->app["daemon"]->stop();
                    break;
                case "start":
                    $this->app["daemon"]->start($workerCount, $options);
                    break;
                case "retry":
                    $this->app["daemon"]->retry($workerCount, $options);
                    break;
                default:
                    $this->printLog();
                    break;
            }

        } else {
            $this->printLog();
        }
    }

    /**
     * 解析参数
     * @param $argvs
     * @return Queue\WorkerOptions
     */
    protected function resolveOptions($argvs)
    {
        $workerOptions = new \Queue\Queue\WorkerOptions();
        for ($idx = 2; $idx < count($argvs); $idx++) {
            $attributes = explode("=", str_replace("--", "", $argvs[$idx]));
            if (count($attributes) == 2) {
                $key = $attributes[0];
                $values = $attributes[1];
                switch ($key) {
                    case "queue":
                        $workerOptions->queues = $values;
                        break;
                    case "sleep":
                        $workerOptions->sleep = $values;
                        break;
                    case "tries":
                        $workerOptions->maxTries = $values;
                        break;
                }
            } else {
                $this->printLog();
            }
        }
        return $workerOptions;
    }

    /**
     * 转换为小写
     * @param $argvs
     */
    protected function arrayChangValueCase(&$argvs)
    {
        foreach ($argvs as &$argv) {
            $argv = strtolower($argv);
        }
    }

    /**
     * 打印log
     */
    protected function printLog()
    {
        echo "queue error:please use [php art worker:start:1 --queue=default --sleep=3 --tries=3] start up queue\r\n";
        exit();
    }


}