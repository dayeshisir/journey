<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 下午9:27
 */

namespace apps\utils\strategy;

use Predis\Client;

class StrategyUtils
{
    protected static $oRedis = null;

    public static function init()
    {
        if (null === self::$oRedis) {
            self::$oRedis = new Client(\apps\common\Config::$aRedisConf);
        }

        return self::$oRedis;
    }

    public static function iZadd($journey, $spot)
    {
        self::init();

        $sKey = self::sGetKey($journey);

        return self::$oRedis->sadd($sKey, $spot);
    }

    public static function aGetSpot($jouney, $offset = 0)
    {
        self::init();

        $sKey = self::sGetKey($jouney);

        $aRet = self::$oRedis->sscan($sKey, $offset);

        return $aRet[1];
    }

    protected static function sGetKey($journey)
    {
        return sprintf("strategy_%d", $journey);
    }
}