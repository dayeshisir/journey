<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/11
 * Time: 上午10:06
 */

namespace apps\redis;

use Predis\Client;

class MyRedis
{
    protected static $oRedis = null;

    public static function init()
    {
        if (null === self::$oRedis) {
            self::$oRedis = new Client(\apps\common\Config::$aRedisConf);
        }

        return self::$oRedis;
    }

    public static function iSadd($key, $value)
    {
        self::init();

        $iRet = self::$oRedis->sadd($key, $value);

        return $iRet;
    }

    public static function aScan($key, $offset = 0)
    {
        self::init();

        $aRet = self::$oRedis->scan($key, $offset);

        return $aRet;
    }
}