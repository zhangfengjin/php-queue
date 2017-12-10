<?php
/**
 * 启动
 * User: zhangfengjin
 * Date: 2017/11/29
 */

namespace XYLibrary\Queue;


class Bootstrap
{
    protected $app;
    protected $initConfig;
    protected $dirs = [
        'form' => __DIR__ . "/../../Config/",
        'to' => __DIR__ . "/../../../../../Config/"
    ];
    protected $bootStraps = [
        '\XYLibrary\Queue\QueueManagerServiceProvider' => '\XYLibrary\Queue\QueueManagerServiceProvider'
    ];

    public function __construct($initConfig = true)
    {
        $this->initConfig = $initConfig;
    }


    /**
     * 启动
     */
    public function bootstrap()
    {
        //缓存调用方式-启动XYLibrary类库
        $bootStrap = new \XYLibrary\Bootstrap\Bootstrap();
        if ($this->initConfig && file_exists($this->dirs["form"])) {
            //创建缓存基础配置文件
            copyDir($this->dirs["form"], $this->dirs["to"]);
        }
        $bootStrap->bootstrap($this->bootStraps);
        $this->app = $bootStrap->getContainer();
    }

    /**
     * 获取容器
     * @return mixed
     */
    public function getContainer()
    {
        return $this->app;
    }
}