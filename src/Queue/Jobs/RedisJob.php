<?php
/**
 * 任务-redis实现
 * User: zhangfengjin
 * Date: 2017/11/27
 */

namespace Queue\Queue\Jobs;


use Queue\IoC\Container;
use Queue\Queue\RedisQueue;

class RedisJob extends Job implements JobInterface
{
    protected $redisQueue;

    protected $queue;

    protected $reserved;

    function __construct(Container $app, RedisQueue $redisQueue, $payload, $reserved, $queue)
    {
        $this->app = $app;
        $this->redisQueue = $redisQueue;
        $this->payload = $payload;
        $this->reserved = $reserved;
        $this->queue = $queue;
    }

    /**
     * 运行
     */
    function run()
    {
        //运行任务
        parent::run();
        //运行成功后 将任务从reserved队列中删除
        $this->redisQueue->delete($this->queue, $this->reserved);
    }

    /**
     * 失败
     * @param 错误信息 $e
     */
    function failed($e)
    {
        //失败处理
        parent::failed($e);
        //运行失败后 将任务从reserved队列迁移至failed队列
        $this->redisQueue->failed($this->queue, $this->reserved);
    }

    /**
     * 重新尝试运行
     */
    function again()
    {
        $this->redisQueue->again($this->queue, $this->reserved);
    }

    /**
     * 任务尝试运行的次数
     * @return mixed
     */
    function attempts()
    {
        return json_decode($this->reserved, true)["attempts"];
    }
}