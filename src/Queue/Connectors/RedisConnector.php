<?php
/**
 * 队列连接--redis实现
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace Queue\Queue\Connectors;


use Queue\Queue\RedisQueue;
use Queue\Support\Redis\RedisManager;

class RedisConnector implements ConnectInterface
{
    protected $redisManager;

    public function __construct(RedisManager $redisManager)
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