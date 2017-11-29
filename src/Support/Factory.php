<?php
/**
 * User: zhangfengjin
 * Date: 2017/11/23
 * Time: 20:02
 */

namespace Queue\Support;


interface Factory
{
    /**
     * @param string $driver 存储设备 如redis\mysql等
     * @return mixed
     */
    function connections($driver = "default");
}