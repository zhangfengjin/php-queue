<?php
/**
 * 队列连接--redis实现
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue\Connectors;


use XYLibrary\Contracts\Redis\Factory as Redis;
use XYLibrary\Queue\RedisQueue;

class RedisConnector implements ConnectInterface
{
    protected $redisManager;

    public function __construct(Redis $redisManager)
    {
        $this->redisManager = $redisManager;
    }

    /**
     * 获取队列具体实现对象
     * @param $config
     * @return RedisQueue
     */
    public function connections($config)
    {
        return new RedisQueue($this->redisManager, $config);
    }
}