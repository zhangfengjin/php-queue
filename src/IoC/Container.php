<?php
/**
 * 依赖注入--核心
 * User: zhangfengjin
 * Date: 2017/11/23
 * Time: 21:22
 */

namespace Queue\IoC;


class Container implements \ArrayAccess
{
    protected $bindings;

    protected $instances;

    /**
     * 将实现绑定到容器
     * @param $abstract
     * @param null $concrete
     * @param bool $shared
     */
    public function bind($abstract, $concrete = null, $shared = true)
    {
        if (!$concrete instanceof \Closure) {
            $concrete = $this->getClosure($abstract, $concrete);
        }
        $this->bindings[$abstract] = compact("concrete", "shared");
    }

    /**
     * 包装成闭包函数
     * @param $abstract
     * @param $concrete
     * @return \Closure
     */
    protected function getClosure($abstract, $concrete)
    {
        return function ($container) use ($abstract, $concrete) {
            $method = ($abstract == $concrete) ? "build" : "make";
            return $container->{$method}($concrete);
        };
    }

    /**
     * 初始化类
     * 返回类对象
     * @param $abstract
     * @return mixed
     */
    function make($abstract)
    {
        //若单例 且 已完成初始化
        if ($this->isShared($abstract) && isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }
        //获取具体实现
        $concrete = $this->getConcrete($abstract);
        if ($this->isAbleBuild($abstract, $concrete)) {
            $instance = $this->build($concrete);
        } else {
            //暂时不考虑 别名问题 即永远运行不到此处
            $instance = $this->make($abstract);
        }
        //若单例 则存储
        if ($this->isShared($abstract)) {
            $this->instances[$abstract] = $instance;
        }
        return $instance;
    }

    /**
     * 获取具体实现（具体类或者闭包函数）
     * @param $abstract
     * @return mixed
     */
    protected function getConcrete($abstract)
    {
        if (!isset($this->bindings[$abstract])) {
            return $abstract;
        }
        //返回包装的闭包函数
        return $this->bindings[$abstract]["concrete"];
    }

    /**
     * 判断是否可以builed
     * @param $abstract
     * @param $concrete
     * @return bool
     */
    protected function isAbleBuild($abstract, $concrete)
    {
        //别名与实现类一致 或 具体实现为闭包函数 则返回true
        return $abstract == $concrete || $concrete instanceof \Closure;
    }

    /**
     * 是否共享
     * 即单例
     * @param $abstract
     * @return bool
     */
    protected function isShared($abstract)
    {
        return isset($this->instances[$abstract]) ||
            (isset($this->bindings[$abstract]) &&
                (isset($this->bindings[$abstract]['shared']) &&
                    $this->bindings[$abstract]['shared'] === true));
    }

    /**
     * 实例化类
     * 存在递归解析类依赖
     * @param $concrete
     * @return mixed|object
     * @throws \Exception
     */
    protected function build($concrete)
    {
        if ($concrete instanceof \Closure) {
            return $concrete($this);
        }
        $reflection = new \ReflectionClass($concrete);
        //判断类是否可以实例化
        if (!$reflection->isInstantiable()) {
            throw new \Exception($concrete . " can not instance");
        }
        $construct = $reflection->getConstructor();
        //判断是否定义构造函数
        if (is_null($construct)) {
            return new $concrete;
        }
        //存在定义的构造函数 获取构造函数的参数
        $dependencies = $construct->getParameters();
        //获取依赖
        $args = $this->getDependencies($dependencies);
        //实例化类
        return $reflection->newInstanceArgs($args);
    }

    /**
     * 获取依赖
     * @param $params
     * @return array
     */
    protected function getDependencies($params)
    {
        $dependencies = [];
        foreach ($params as $param) {
            $dependency = $param->getClass();
            if (is_null($dependency)) {
                $dependencies[] = NULL;
            } else {
                //递归获取依赖
                $dependencies[] = $this->resolve($param);
            }
        }
        return $dependencies;
    }

    /**
     * 解析参数类
     * @param \ReflectionParameter $param
     * @return mixed
     */
    protected function resolve(\ReflectionParameter $param)
    {
        return $this->make($param->getClass()->name);
    }

    /**
     * 重写该方法 实现以数组形式访问类
     * @param mixed $name
     * @return mixed
     */
    function offsetGet($name)
    {
        // TODO: Implement offsetGet() method.
        return $this->make($name);
    }

    function offsetExists($offset)
    {
        // TODO: Implement offsetExists() method.
    }

    function offsetSet($offset, $value)
    {
        // TODO: Implement offsetSet() method.
    }

    function offsetUnset($offset)
    {
        // TODO: Implement offsetUnset() method.
    }

}