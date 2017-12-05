<?php
/**
 * lua脚本
 * User: zhangfengjin
 * Date: 2017/11/24
 */

namespace XYLibrary\Queue;


class LuaScripts
{
    /**
     * 队列中任务个数
     * 包括延迟队列、待处理队列、处理中队列、失败队列
     */
    public static function size()
    {

    }

    /**
     * 迁移过期任务
     * 包括自动从delayed>>default、手动从failed>>default
     */
    public static function migrateExpiredJobs()
    {
        return <<<LUA
--从delayed或failed队列中获取截止到currentTime的任务
local jobs=redis.call('zrangebyscore',KEYS[1],'-inf',ARGV[1])
--若jobs不为空
if(next(jobs)~=nil) then
    --从delayed或failed队列中删除取出的任务
    redis.call('zremrangebyrank',KEYS[1],0,#jobs -1)
    --每次100条 循环将jobs插入到default队列中
    for i = 1, #jobs, 100 do
        redis.call('rpush',KEYS[2],unpack(jobs,i,math.min(i+99,#jobs)))
    end
end
return jobs
LUA;
    }

    /**
     * 获取要执行的任务
     */
    public static function pop()
    {
        return <<<LUA
--从default队列中lpop弹出一个任务
local payload=redis.call('lpop',KEYS[1])
local reserved=false
--若不为空
if(payload ~= false) then
    --job任务json串转化为数组
    reserved=cjson.decode(payload)
    --尝试次数+1
    reserved['attempts']=reserved['attempts']+1
    --reserved队列中job串中的过期时间=当前时间+过期设置 expired为s
    ARGV[1]=ARGV[1]+reserved['expired']
    reserved=cjson.encode(reserved)
    redis.call('zadd',KEYS[2],ARGV[1],reserved)
end
return {payload,reserved}
LUA;
    }

    /**
     * 迁移失败任务
     * 任务执行失败、但是执行次数未超过重试次数时
     */
    public static function migrateReservedToDefault()
    {
        return <<<LUA
--从reserved队列中删除指定任务
redis.call('zrem',KEYS[1],ARGV[1])
--将执行失败的任务压入default队列头部
redis.call('lpush',KEYS[2],ARGV[1])
return true
LUA;
    }

    /**
     * 迁移任务到失败队列
     * @return string
     */
    public static function migrateReservedToFailed()
    {
        return <<<LUA
--从reserved队列中删除指定任务
redis.call('zrem',KEYS[1],ARGV[1])
--设置执行失败的任务failed+1 attempts=0
local job=cjson.decode(ARGV[1])
job['failed']=job['failed']+1
job['attempts']=0
job=cjson.encode(job)
--将执行失败的任务写入failed队列
redis.call('zadd',KEYS[2],ARGV[2],job)
return true
LUA;
    }
}