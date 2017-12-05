<?php
/**
 * 队列实现门面
 * User: zhangfengjin
 * Date: 2017/11/24
 * Time: 18:48
 */

namespace XYLibrary\Facade;


class Queue extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "queue";
    }
}