<?php
/**
 * 队列父类
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue;


abstract class Queue
{
    protected $app;

    /**
     * 创建job json串
     * @param $job
     * @return string
     */
    protected function createPayload($job)
    {
        $payload = json_encode($this->createPayloadArray($job));
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("json encode error");
        }
        return $payload;
    }

    /**
     * 创建job数组
     * @param $job
     * @return array
     */
    protected function createPayloadArray($job)
    {
        return [
            "displayName" => $this->getDisplayName($job),
            "handler" => 'XYLibrary\Queue\QueueHandler@run',
            "maxTries" => isset($job->tries) ? $job->tries : null,
            "data" => [
                "commandName" => get_class($job),
                "command" => serialize(clone $job)
            ],
            "expired" => isset($job->expired) ? $job->expired : 86400,
            "failed" => 0
        ];
    }

    /**
     * 获取任务名称
     * @param $job
     * @return string
     */
    protected function getDisplayName($job)
    {
        return method_exists($job, "displayName") ? $job->displayName() : get_class($job);
    }

    /**
     * 给容器变量赋值
     */
    public function setContainer($app)
    {
        $this->app = $app;
    }
}