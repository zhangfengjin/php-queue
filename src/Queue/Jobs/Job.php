<?php
/**
 * 任务父类
 * User: zhangfengjin
 * Date: 2017/11/27
 */

namespace Queue\Queue\Jobs;


class Job
{
    protected $app;

    protected $payload;


    /**
     * 运行任务
     */
    function run()
    {
        $job = $this->getJob();
        $jobHandler = $job["handler"];
        list($class, $handler) = explode("@", $jobHandler);
        $this->resolve($class)->$handler($this, $job["data"]);
    }

    /**
     * 任务执行错误回调
     * @param $e
     */
    function failed($e)
    {
        $job = $this->getJob();
        $jobHandler = $job["handler"];
        list($class, $handler) = explode("@", $jobHandler);
        $this->resolve($class)->failed($this, $job["data"], $e);
    }

    /**
     * 允许任务允许的最大次数
     * @return mixed
     */
    function maxTries()
    {
        return $this->getJob()["maxTries"];
    }


    /**
     * 通过容器获取类对象
     * @param $class
     * @return mixed
     */
    protected function resolve($class)
    {
        return $this->app->make($class);
    }

    /**
     * 获取任务数组
     * @return mixed
     */
    protected function getJob()
    {
        return json_decode($this->payload, true);
    }


}