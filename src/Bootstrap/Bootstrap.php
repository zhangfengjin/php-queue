<?php
/**
 * 启动
 * User: zhangfengjin
 * Date: 2017/11/29
 */

namespace Queue\Bootstrap;


use Queue\Daemon\Daemon;
use Queue\Exception\ExceptionHandler;
use Queue\Facade\Facade;
use Queue\Queue\Connectors\RedisConnector;
use Queue\Queue\Worker;
use Queue\Support\Redis\RedisManager;

class Bootstrap
{
    protected $app;

    public function __construct()
    {
        $this->app = new \Queue\IoC\Container();
        Facade::setFacadeApplication($this->app);
    }

    /**
     * 获取容器对象
     * @return IoC\Container
     */
    public function getContainer()
    {
        return $this->app;
    }

    /**
     * 启动
     */
    public function bootstrap()
    {
        $this->registerException();
        $this->registerConfig();
        $this->registerRedis();
        $this->registerQueue();
        $this->registerWorker();
        $this->registerDaemon();
    }

    /**
     * 注册错误
     */
    protected function registerException()
    {
        $exception = new ExceptionHandler();
        $exception->handler();
    }

    /**
     * 注册配置信息
     */
    protected function registerConfig()
    {
        $this->app->bind("config", function ($app) {
            return require __DIR__ . "/../Config/support.php";
        });
    }

    /**
     * 注册redis
     */
    protected function registerRedis()
    {
        $this->app->bind("redis", function ($app) {
            $driver = $app["config"]["support"]["default"];
            $config = $app["config"]["support"][$driver];
            return new RedisManager($config["client"], $config);
        });
    }

    /**
     * 注册队列
     */
    protected function registerQueue()
    {
        $this->app->bind("queue", function ($app) {
            return $this->tap(new \Queue\Queue\QueueManager($app), function ($manager) {
                $manager->addConnector("redis", function () {
                    return new RedisConnector($this->app["redis"]);
                });
            });
        });
    }

    /**
     * 注册Worker
     */
    protected function registerWorker()
    {
        $this->app->bind("worker", function ($app) {
            return new Worker($app["queue"]);
        });
    }

    /**
     * 注册Daemon
     */
    protected function registerDaemon()
    {
        $this->app->bind("daemon", function ($app) {
            return new Daemon($app["worker"]);
        });
    }

    /**
     * tap链方法
     * @param $value
     * @param $callback
     * @return mixed
     */
    protected function tap($value, $callback)
    {
        $callback($value);
        return $value;
    }
}