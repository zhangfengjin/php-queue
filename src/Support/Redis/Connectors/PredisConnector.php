<?php
/**
 * User: zhangfengjin
 * Date: 2017/11/23
 * Time: 20:09
 */

namespace Queue\Support\Redis\Connectors;


use Predis\Client;

class PredisConnector
{
    /**
     * 返回predis连接对象
     * @param $config
     * @param $options
     * @return Client
     */
    public function connect($config, $options)
    {
        return new Client($config, $options);
    }
}