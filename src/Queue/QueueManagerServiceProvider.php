<?php
/**
 * Created by PhpStorm.
 * User: fengjin1
 * Date: 2017/12/10
 * Time: 23:24
 */

namespace XYLibrary\Queue;


use XYLibrary\Contracts\ServiceProvider\Factory as ServiceProvider;
use XYLibrary\IoC\Container;
use XYLibrary\Queue\Connectors\RedisConnector;
use XYLibrary\Queue\Daemon\Daemon;

class QueueManagerServiceProvider implements ServiceProvider
{
    protected $app;

    public function __construct(Container $app)
    {
        $this->app = $app;
    }

    /**
     * 注册服务提供者
     */
    public function register()
    {
        $this->registerQueue();
        $this->registerWorker();
        $this->registerDaemon();
    }

    /**
     * 注册队列
     */
    protected function registerQueue()
    {
        $this->app->bind("queue", function ($app) {
            return tap(new QueueManager($app), function ($manager) {
                $this->registerRedisQueue($manager);
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
     * 注册Redis实现队列
     * @param $manager
     */
    protected function registerRedisQueue($manager)
    {
        $manager->addConnector("redis", function () {
            return new RedisConnector($this->app["redis"]);
        });
    }
}