# php-queue
php-queue 是一个基于php的包，目前仅支持redis存储（对于有其他存储需求的，可根据接口自行开发实现）.
# 环境要求
1、安装redis2.6.0以上<br/>
2、安装composer
# 安装
1、克隆本项目<br/>
2、cd 到项目目录，composer install
# 应用
开启队列：php art worker:start:1 --queue=send1,send2 --tries=3 --sleep=3<br/>
关闭队列：php art worker:stop<br/>
重新执行失败队列：php art worker:retry --queue=send1,send2 --tries=3 --sleep
# 目录结构
├── bin                         // art命令<br/>
├── example                     // 示例<br/>
│   ├── Queue.php               // 队列任务添加示例<br/>
├── src
|   ├── Bootstrap<br/>
......
