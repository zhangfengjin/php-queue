<?php
/**
 * 队列-redis实现
 * queues.delayed--延迟队--有序集合（可设置过期时间，到期自动迁移至default队列）
 * queues.default--待处理队列--列表
 * queues.reserved--处理中队列--有序集合（可设置过期时间，到期执行未完成则自动迁移到default队列）
 * queues.failed--失败队列--有序集合（可设置到期时间，可指定日期的失败任务迁移到default队列）
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue;


use XYLibrary\Contracts\Redis\Factory as Redis;
use XYLibrary\Queue\Jobs\RedisJob;

class RedisQueue extends Queue
{
    protected $redisManager;

    protected $config;

    public function __construct(Redis $redisManager, array $config)
    {
        $this->redisManager = $redisManager;
        $this->config = $config;
    }


    /**
     * 向待处理队列中压入任务
     * @param $job
     * @param null $queue
     */
    public function push($job, $queue = null)
    {
        $payload = $this->createPayload($job);
        return $this->pushRaw($payload, $queue);
    }

    /**
     * 向队列中直接压入任务JSON串
     * @param $payload
     * @param null $queue
     * @return mixed
     */
    public function pushRaw($payload, $queue = null)
    {
        $queue = $this->getQueue($queue);
        $this->getConnection()->rpush($queue, $payload);
        return json_decode($payload, true)["id"];
    }

    /**
     * 向延迟队列中压入任务
     * @param $delayed
     * @param $job
     * @param null $queue
     */
    public function later($delay, $job, $queue = null)
    {
        $payload = $this->createPayload($job);
        return $this->laterRaw($delay, $payload, $queue);
    }

    /**
     * 直接向延迟队列中压入任务JSON串
     * @param $delay
     * @param $payload
     * @param $queue
     * @return mixed
     */
    public function laterRaw($delay, $payload, $queue)
    {
        $delay = time() + $delay;
        $queue = $this->getQueue($queue) . ":delayed";
        $this->getConnection()->zadd($queue, $delay, $payload);
        return json_decode($payload, true)["id"];
    }

    /**
     * 弹出任务
     * 从default队列中弹出
     * 1、迁移数据
     *  1.1、将delayed队列中到期的任务迁移至default队列
     *  1.2、将reserved队列中执行超时（expired）的任务迁移至default队列
     * 2、从default中lpop任务 同时将弹出的任务attempts+1后放入reserved队列
     * @param null $queue
     */
    public function pop($queue = null)
    {
        $this->migerate($queue);
        list($payload, $reserved) = $this->getJob($queue);
        if ($reserved) {
            return new RedisJob($this->app, $this, $payload, $reserved, $queue);
        }
    }

    /**
     * 删除任务
     * 任务执行成功后 从reserved队列中删除该任务
     * @param $queue
     * @param $job
     */
    public function delete($queue, $job)
    {
        $queue = $this->getQueue($queue) . ":reserved";
        return $this->getConnection()->zrem($queue, $job);
    }

    /**
     * retry
     * 从failed>>default
     * @param $queue
     * @param null $time
     */
    public function retry($queue, $time = null)
    {
        $queue = $this->getQueue($queue);
        $failedQueue = $queue . ":failed";
        return $this->migrateExpiredJobs($failedQueue, $queue, $time);
    }

    /**
     * 重新尝试
     * 将执行出错但未超过最大执行次数的任务 放回到default队列
     * @param $queue
     * @param $job
     * @return mixed
     */
    public function again($queue, $job)
    {
        $queue = $this->getQueue($queue);
        return $this->getConnection()->eval(
            LuaScripts::migrateReservedToDefault(), 2, $queue . ":reserved", $queue, $job
        );
    }

    /**
     * 将失败的任务迁移到失败队列
     * @param $queue
     * @param $job
     */
    public function failed($queue, $job)
    {
        $queue = $this->getQueue($queue);
        $reservedQueue = $queue . ":reserved";
        $failedQueue = $queue . ":failed";
        return $this->getConnection()->eval(
            LuaScripts::migrateReservedToFailed(), 2, $reservedQueue, $failedQueue, $job, time() + 30
        );
    }

    /**
     * 迁移数据
     * @param $queue
     */
    protected function migerate($queue)
    {
        $queue = $this->getQueue($queue);
        $delayedQueue = $queue . ":delayed";
        $reservedQueue = $queue . ":reserved";
        $this->migrateExpiredJobs($delayedQueue, $queue);
        $this->migrateExpiredJobs($reservedQueue, $queue);
    }

    /**
     * 迁移数据
     * @param $form
     * @param $to
     * @return mixed
     */
    protected function migrateExpiredJobs($form, $to, $time = null)
    {
        $time = is_null($time) ? time() : $time;
        return $this->getConnection()->eval(
            LuaScripts::migrateExpiredJobs(), 2, $form, $to, $time);
    }

    /**
     * 获取任务
     * @param $queue
     * @return mixed
     */
    protected function getJob($queue)
    {
        $queue = $this->getQueue($queue);
        $reservedQueue = $queue . ":reserved";
        return $this->getConnection()->eval(
            LuaScripts::pop(), 2, $queue, $reservedQueue, time());
    }


    protected function createPayloadArray($job)
    {
        return array_merge(parent::createPayloadArray($job), [
            "id" => $this->getRandomId(),
            "attempts" => 0
        ]);
    }

    /**
     * 获取随机Id
     * @return string
     */
    protected function getRandomId()
    {
        return uniqid("queue", true);
    }

    /**
     * 获取队列
     * @param $queue
     * @return string
     */
    protected function getQueue($queue)
    {
        return "queues:" . ($queue ?: "default");
    }

    /**
     * 获取redis连接对象
     * @return \Predis\Client
     */
    protected function getConnection()
    {
        return $this->redisManager->connections();
    }
}