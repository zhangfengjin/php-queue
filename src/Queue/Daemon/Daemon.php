<?php
/**
 * 守护进程
 * User: zhangfengjin
 * Date: 2017/11/28
 */

namespace XYLibrary\Queue\Daemon;


use XYLibrary\Queue\Worker;

class Daemon
{
    protected $dir;
    protected $file;//进程ID文件
    protected $workerCount;//工作进程数量
    protected $workers = [];
    protected $stoping = false;
    protected $worker;

    function __construct(Worker $worker, $dir = "/tmp")
    {
        $this->worker = $worker;
        $this->dir = $dir . "/php-queue";
        $this->file = $this->dir . "/daemon.pid";
    }

    /**
     * 启动
     * @param int $workerCount 工作进程个数
     */
    public function start($workerCount = 1, $options)
    {
        $this->workerCount = $workerCount;
        $masterId = pcntl_fork();//创建主进程
        if ($masterId < 0) {
            throw new \RuntimeException("fork process error");
        }
        if ($masterId > 0) {
            $this->createPidFile($masterId);
            exit();
        }
        posix_setsid();//设置进程组
        $this->registerMasterSignal();
        while (true) {
            pcntl_signal_dispatch();
            if ($this->workerCount > 0) {
                $this->workerCount--;
                echo "fork worker process\r\n";
                //创建工作进程
                $workerId = pcntl_fork();
                if ($workerId < 0) {
                    throw new \RuntimeException("fork process error");
                }
                if ($workerId) {
                    $worker = new WorkerProcess();
                    $worker->pid = $workerId;
                    $worker->status = 0;
                    $this->workers[$workerId] = $worker;
                } else {
                    $this->registerWorkerSignal();
                    while (true) {
                        pcntl_signal_dispatch();
                        $this->run($options);
                    }
                }
            } else {
                sleep(3);
            }
        }
    }

    /**
     * 重新执行失败的队列
     * @param $workerCount
     * @param $options
     */
    public function retry($workerCount, $options)
    {
        $this->stop();
        $this->worker->retry($options);
        $this->start($workerCount, $options);
    }

    /**
     * 停止进程
     */
    public function stop()
    {
        $pid = $this->readPidFile();
        if ($pid) {
            posix_kill($pid, 15);
        }
    }

    /**
     * 注册主进程信号监控
     * @param $signal
     */
    protected function registerMasterSignal()
    {
        pcntl_signal(SIGHUP, [$this, "masterSignalHandler"]);//终端结束
        pcntl_signal(SIGINT, [$this, "masterSignalHandler"]);//ctrl+c\kill -2
        pcntl_signal(SIGQUIT, [$this, "masterSignalHandler"]);//退出 ctrl+\
        pcntl_signal(SIGTERM, [$this, "masterSignalHandler"]);//终止 kill
        pcntl_signal(SIGCHLD, [$this, "masterSignalHandler"]);//子进程停止或退出时
    }

    /**
     * 主进程信号处理
     * @param $signal
     */
    private function masterSignalHandler($signal)
    {
        switch ($signal) {
            case 1:
            case 2:
            case 3:
                break;
            case 15:
                //向所有工作进程发送退出信号
                $this->stoping = true;
                foreach ($this->workers as $worker) {
                    //向指定进程发送SIGUSR1信号
                    posix_kill($worker->pid, SIGUSR1);
                }
                break;
            case 17:
                while (($pid = pcntl_waitpid(-1, $status, WUNTRACED | WNOHANG)) != 0) {
                    // 退出的子进程pid
                    if ($pid > 0) {
                        //工作进程退出时 判断是否所有工作进程都已退出 主进程退出
                        unset($this->workers[$pid]);
                        if ($this->stoping) {
                            //停止过程中 需要判断子进程是否全部退出
                            if (empty($this->workers)) {
                                //杀死主进程
                                $this->kill(getmypid());
                            }
                        } else {
                            $this->workerCount++;
                        }
                    } else {
                        echo "error:worker exit\r\n";
                        $this->kill(getmypid());
                    }
                }
                break;
        }
    }

    /**
     * 注册工作进程信号监控
     * @param $signal
     */
    protected function registerWorkerSignal()
    {
        pcntl_signal(SIGTERM, [$this, "workerSignalHandler"]);//终止 kill
        pcntl_signal(SIGUSR1, [$this, "workerSignalHandler"]);//父进程传递信号
    }

    /**
     * 工作进程信号处理
     * @param $signal
     */
    private function workerSignalHandler($signal)
    {
        switch ($signal) {
            case 10:
                $this->kill(getmypid());
                break;
            case 1:
            case 2:
            case 3:
            case 15:
                break;
        }
    }

    /**
     * 强制杀死进程
     * @param $pid 进程Id
     */
    private function kill($pid)
    {
        posix_kill($pid, 9);
    }

    /**
     * 创建进程文件
     * @param $pid
     */
    protected function createPidFile($pid)
    {
        if (!is_dir($this->dir)) {
            mkdir($this->dir);
        }
        $fp = fopen($this->file, "w+");
        fwrite($fp, $pid);
        fclose($fp);
    }

    /**
     * 读取pid文件
     * @return string
     */
    protected function readPidFile()
    {
        if (file_exists($this->file)) {
            $fp = fopen($this->file, "r");
            $pid = fread($fp, 20);
            fclose($fp);
            return $pid;
        }
    }

    /**
     * 运行
     */
    protected function run($options)
    {
        try {
            $this->worker->daemon($options);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\r\n";
            $this->stop();
        }
    }

}