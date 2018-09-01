<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午11:56
 */

namespace apps\models\push;

use Predis\Client;

class Forum
{
    protected static $oRedis = null;

    public static function init()
    {
        if (null !== self::$oRedis) {
            self::$oRedis = new Client(\apps\common\Config::$aRedisConf);
        }

        return self::$oRedis;
    }

    public static function iAddForumId($iJourneyId, $sUid, $sForumId)
    {
        self::init();

        $sKey = sprintf("%d_%s", $iJourneyId, $sUid);

        return self::$oRedis->rpush($sKey, $sForumId);
    }
}