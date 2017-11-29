<?php
/**
 * 任务接口
 * User: zhangfengjin
 * Date: 2017/11/27
 */

namespace Queue\Queue\Jobs;


interface JobInterface
{
    /**
     * 运行
     * @return mixed
     */
    function run();

    /**
     * 失败
     * @param $e 错误信息
     * @return mixed
     */
    function failed($e);

    /**
     * 重新尝试运行
     * @return mixed
     */
    function again();

    /**
     * 最大次数
     * @return mixed
     */
    function maxTries();

    /**
     * 已尝试运行次数
     * @return mixed
     */
    function attempts();

}