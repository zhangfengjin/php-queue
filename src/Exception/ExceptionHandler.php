<?php
/**
 * 错误处理
 * User: zhangfengjin
 * Date: 2017/11/28
 */

namespace Queue\Exception;


class ExceptionHandler
{
    //错误级别 只能由errorShutdown捕获
    private $errorTypes = [E_COMPILE_ERROR, E_CORE_ERROR, E_ERROR, E_PARSE];

    /**
     * 异常
     */
    function handler()
    {
        error_reporting(-1);//ALL 报告所有错误
        set_error_handler([$this, "errorHandler"]);//自定义错误处理 自动捕获Warning\Notice级别错误
        set_exception_handler([$this, "exceptionHandler"]);//顶级错误捕获
        register_shutdown_function([$this, "shutDown"]);//致命错误  Fatal Error、Parse Error
        ini_set('display_errors', 'Off');//关闭错误信息提示
    }

    /**
     * 错误处理
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     */
    public function errorHandler($level, $message, $file = '', $line = 0)
    {
        //若error_reporting设置非0 且错误级别>0
        if (error_reporting() & $level) {
            throw new \ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * 顶级捕获错误
     * @param $e
     */
    public function exceptionHandler($e)
    {
        if (!$e instanceof \Exception) {
            $e = new \ErrorException($e["message"], 0, $e["type"], $e["file"], $e["line"]);
        }
        //调用自定义错误处理 如写入log 暂未处理
        echo $e->getMessage() . "--" . $e->getFile() . "--" . $e->getLine() . ",need try catch\r\n";
    }

    /**
     * 捕获致命错误
     */
    public function shutDown()
    {
        if (!is_null($error = error_get_last()) && in_array($error["type"], $this->errorTypes)) {
            $this->exceptionHandler($error);
        }
    }
}