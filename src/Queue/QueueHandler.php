<?php
/**
 * 任务处理通用类
 * User: zhangfengjin
 * Date: 2017/11/27
 */

namespace Queue\Queue;


class QueueHandler
{
    /**
     * 实例化具体任务并执行
     * @param $job
     * @param $data
     */
    public function run($job, array $data)
    {
        //序列化任务
        $instance = unserialize($data["command"]);
        //执行任务
        $instance->handler();
        //执行任务成功以后 可以做些通用操作 暂未实现
        //$job
    }

    /**
     * 实例化具体任务 并调用任务失败方法
     * @param $job
     * @param array $data
     * @param $e
     */
    public function failed($job, array $data, $e)
    {
        //序列化任务
        $instance = unserialize($data["command"]);
        if (method_exists($instance, "failed")) {
            $instance->failed($e);
        }
    }
}