<?php
/**
 * 门面--核心
 * User: zhangfengjin
 * Date: 2017/11/23
 * Time: 19:21
 */

namespace XYLibrary\Facade;


class Facade
{
    protected static $app;
    protected static $resolveInstances;

    /**
     * 绑定容器
     * @param $app
     */
    public static function setFacadeApplication($app)
    {
        self::$app = $app;
    }

    /**
     * 魔术方法
     * @param $name
     * @param $arguments
     */
    public static function __callStatic($method, $arguments)
    {
        if (!static::$app) {
            throw new \RuntimeException("must used setFacadeApplication set container.");
        }
        $instance = static::getFacadeInstance();
        if (!$instance) {
            throw new \RuntimeException('A facade root has not been set.');
        }
        return $instance->$method(...$arguments);
    }

    /**
     * 获取
     */
    protected static function getFacadeInstance()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * 解析
     * @param $abstract
     * @return mixed
     */
    protected static function resolveFacadeInstance($abstract)
    {
        if (is_object($abstract)) {
            return $abstract;
        }
        if (isset(static::$resolveInstances[$abstract])) {
            return static::$resolveInstances[$abstract];
        }
        return static::$resolveInstances[$abstract] = static::$app[$abstract];
    }

    /**
     * 获取门面key
     */
    protected static function getFacadeAccessor()
    {
        throw new \RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

}