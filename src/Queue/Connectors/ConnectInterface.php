<?php
/**
 * 队列连接接口
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace Queue\Queue\Connectors;


interface ConnectInterface
{
    function connections($config);
}