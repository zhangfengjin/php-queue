<?php
/**
 * 任务处理
 * User: zhangfengjin
 * Date: 2017/11/23
 */

namespace XYLibrary\Queue;


use XYLibrary\Queue\Jobs\JobInterface;

class Worker
{
    protected $manager;

    public function __construct(QueueManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * retry
     * failed>>default
     * @param WorkerOptions $options
     */
    public function retry(WorkerOptions $options)
    {
        foreach (explode(',', $options->queues) as $queue) {
            $this->manager->connections()->retry($queue);
        }
    }

    /**
     * 执行任务
     * @param WorkerOptions $options
     */
    public function daemon(WorkerOptions $options)
    {
        $job = $this->getJob($options->queues, $this->manager->connections());
        if ($job) {
            $this->runJob($job, $options);
        } else {
            sleep($options->sleep);
        }
    }

    /**
     * 获取队列任务
     * @param $queues
     * @param $connection
     * @return mixed
     */
    protected function getJob($queues, $connection)
    {
        foreach (explode(',', $queues) as $queue) {
            if (!is_null($job = $connection->pop($queue))) {
                return $job;
            }
        }
    }

    /**
     * 运行任务
     * @param JobInterface $job
     * @param array $options
     */
    protected function runJob(JobInterface $job, WorkerOptions $options)
    {
        try {
            $job->run();
        } catch (\Exception $e) {
            $maxTries = !is_null($job->maxTries()) ? $job->maxTries() : $options->maxTries;
            if ($maxTries === 0 || $job->attempts() < $maxTries) {
                //第一次执行或已执行次数<最大允许执行的次数
                $job->again();//重新尝试执行
            } else {
                //任务执行失败
                $job->failed($e);
            }
        }
    }
}