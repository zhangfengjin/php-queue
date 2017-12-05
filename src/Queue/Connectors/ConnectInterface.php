<?php
/**
 * 队列连接接口
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue\Connectors;


interface ConnectInterface
{
    function connections($config);
}