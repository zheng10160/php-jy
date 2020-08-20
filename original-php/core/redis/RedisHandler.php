<?php
namespace core\redis;
/**
 * Created by PhpStorm.
 * User: localuser1
 * Date: 2018/11/30
 * Time: 上午10:03
 */
class RedisHandler
{

    public static $_instance;

    protected static $redis;


    /**
     * 获取redis实例化
     * @param $redis_key
     * @param $redisHandler
     * @return Redis
     */
    public static function getInstance($redisHandler){

        if(!isset(self::$redis)) {
            self::$redis  = self::redisHandler($redisHandler);//当前配置文件
        }
        return self::$redis;
    }

    public static function redisHandler($redisHandler)
    {
        $redis = new \Redis();
        $redis->connect($redisHandler['hostname'], intval($redisHandler['port']));
        $redis->auth($redisHandler['password']);
        $redis->select(intval($redisHandler['database']));//测试

        return $redis;
    }
}