<?php
/**
 * 队列实现门面
 * Created by PhpStorm.
 * User: fengjin1
 * Date: 2017/12/10
 * Time: 23:13
 */

namespace XYLibrary\Queue\Facade;


use XYLibrary\Facade\Facade;

class Queue extends Facade
{
    protected static function getFacadeAccessor()
    {
        return "queue";
    }
}