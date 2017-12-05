<?php
/**
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue;


class QueueManager
{
    protected $app;//容器对象

    protected $connectors = [];//具体实现队列对象

    protected $connections = [];//具体实现队列连接对象


    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 添加Queue实现
     * @param $name
     * @param \Closure $closure
     */
    public function addConnector($name, \Closure $closure)
    {
        $this->connectors[$name] = $closure;
    }

    /**
     * 魔术方法
     * @param $method
     * @param $arguments
     */
    public function __call($method, $arguments)
    {
        return $this->connections()->{$method}(...$arguments);
    }

    /**
     * 具体队列具体实现对象
     * @return mixed
     */
    public function connections()
    {
        $driver = $this->getDefaultDriver();
        if (!isset($this->connections[$driver])) {
            $this->connections[$driver] = $this->resolve($driver);
            $this->connections[$driver]->setContainer($this->app);
        }
        return $this->connections[$driver];
    }

    /**
     * 解析
     * @param $driver
     * @return mixed
     */
    protected function resolve($driver)
    {
        $config = $this->getConfig($driver);
        if (isset($this->connections[$driver])) {
            return $this->connections[$driver];
        }
        $instance = call_user_func($this->connectors[$driver]);
        return $instance->connections($config);
    }

    /**
     * 获取默认的队列
     * @return mixed
     */
    protected function getDefaultDriver()
    {
        return $this->app["config"]["queue"]["default"];
    }

    /**
     * 获取连接存储的配置信息
     * @param $driver
     * @return mixed
     */
    protected function getConfig($driver)
    {
        return $this->app["config"]["queue"]["connections"][$driver];
    }
}