<?php
/**
 * User: zhangfengjin
 * Date: 2017/11/23
 * Time: 20:04
 */

namespace XYLibrary\Support\Redis;


use XYLibrary\Support\Factory;
use XYLibrary\Support\Redis\Connectors\PredisConnector;

class RedisManager implements Factory
{
    protected $package;//第三方包

    protected $config;//redis配置

    protected $connections;//redis连接数组

    function __construct($package, array $config)
    {
        $this->package = $package;
        $this->config = $config;
    }

    /**
     * 获取redis连接对象
     * @param string $driver
     * @return \Predis\Client
     */
    function connections($driver = "default")
    {
        if (isset($this->connections[$driver])) {
            return $this->connections[$driver];
        }
        //连接redis的可选参数 如超时限制等
        $options = array_merge([
            "timeout" => "10.0"
        ], isset($this->config["options"]) ? $this->config["options"] : []);

        return $this->connections[$driver] =
            $this->getConnection()->connect($this->config[$driver], $options);
    }

    /**
     * 获取连接对象
     * @return PredisConnector
     */
    protected function getConnection()
    {
        switch ($this->package) {
            case "predis":
                return new PredisConnector();
                break;
            case "phpredis":
                break;
        }
    }
}