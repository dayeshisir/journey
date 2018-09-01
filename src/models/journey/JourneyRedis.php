<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午11:22
 */

namespace apps\models\journey;


use Predis\Client;

class JourneyRedis
{
    const PREFIX = 'leader';

    protected static $oReids = null;

    public static function init()
    {
        if (null !== self::$oReids) {
            self::$oReids = new Client(\apps\common\Config::$aRedisConf);
        }

        return self::$oReids;
    }

    public static function bSave($aParam)
    {

    }

    protected static function sGetKey($aParam)
    {
        $iType      = intval($aParam['type']);
        $iJourneyId = intval($aParam['journey_id']);
        $iUid       = strval($aParam['uid']);
        $sSuffix    = '';
        switch ($iType) {
            case \apps\common\Constant::FORUMID_TYPE_CREATE :
                $sSuffix = \apps\common\Constant::FORUMID_TYPE_CREATE_SUFFIX;
                return sprintf('%s_%s_%d_%d', self::PREFIX,, $iJourneyId, $iUid,);
        }

        return sprintf("%s_%s_%d_%d", self::PREFIX, $sSuffix, $iJourneyId, $iUid);
    }
}