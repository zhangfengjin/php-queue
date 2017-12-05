<?php
/**
 * User: zhangfengjin
 * Date: 2017/11/29
 * Time: 23:21
 */

namespace XYLibrary\Queue;


class WorkerOptions
{
    /**
     * @var string
     */
    public $queues;

    /**
     * 执行休眠时间
     * @var int
     */
    public $sleep;

    /**
     * 最大尝试次数
     * @var int
     */
    public $maxTries;

    public function __construct($queues = "default", $expired = 86400, $sleep = 3, $maxTries = 1)
    {
        $this->queues = $queues;
        $this->sleep = $sleep;
        $this->maxTries = $maxTries;
    }
}