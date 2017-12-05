<?php
/**
 * Created by PhpStorm.
 * User: fengjin1
 * Date: 2017/12/5
 * Time: 21:52
 */
return [
    'default' => '',//存储默认连接
    'connections' => [],
    'redis' => [
        'client' => 'predis',//使用predis组件连接redis
        'default' => [
            'host' => '127.0.0.1',
            'password' => null,
            'port' => '6379',
            'database' => 10,
        ],
    ],

];