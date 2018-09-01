<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午11:56
 */

namespace apps\models\push;

use Predis\Client;

class Push
{
    protected static $oRedis = null;

    public static function init()
    {
        if (null === self::$oRedis) {
            self::$oRedis = new Client(\apps\common\Config::$aRedisConf);
        }

        return self::$oRedis;
    }

    public static function iAddForumId($iJourneyId, $sUid, $sForumId)
    {
        self::init();

        $sKey = self::sGetKey($iJourneyId, $sUid);

        return self::$oRedis->rpush($sKey, $sForumId);
    }

    public static function iConsumeForumId($iJourneyId, $sUid)
    {
        self::init();

        $sKey = self::sGetKey($iJourneyId, $sUid);

        return self::$oRedis->lpop($sKey);
    }

    protected static function sGetKey($journey, $uid)
    {
        return sprintf("%d_%s", $journey, $uid);
    }
}