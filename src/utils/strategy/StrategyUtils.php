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

    public static function iAdd($journey, $spot)
    {
        self::init();

        $sKey = self::sGetKey($journey);

        return self::$oRedis->rpush($sKey, $spot);
    }

    public static function aGetSpot($jouney, $start = 0, $end = 1000)
    {
        self::init();

        $sKey = self::sGetKey($jouney);

        $aRet = self::$oRedis->lrange($sKey, $start, $end);

        $aSpots = [];
        foreach ($aRet as $spot) {
            $aSpots[] = intval($spot);
        }

        return array_unique($aSpots);
    }

    public static function iReset($journey)
    {
        self::init();

        $sKey = self::sGetKey($journey);

        $iRet = self::$oRedis->del($sKey);

        return $iRet;
    }

    protected static function sGetKey($journey)
    {
        return sprintf("strategy_%d", $journey);
    }
}