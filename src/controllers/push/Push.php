<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 下午12:49
 */

namespace apps\controllers\push;


use apps\controllers\BaseController;

class Push extends BaseController
{
    public function aAddPush()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);
        $sUid       = \apps\libs\Request::mGetParam('uid', '');
        $sForumId   = \apps\libs\Request::mGetParam('forum_id', '');

        $iRet = \apps\models\push\Push::iAddForumId($iJourneyId, $sUid, $sForumId);

        \apps\libs\BuildReturn::aBuildReturn($iRet);
    }

    public function aConsumePush()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);
        $sUid       = \apps\libs\Request::mGetParam('uid', '');

        $iRet = \apps\models\push\Push::iConsumeForumId($iJourneyId, $sUid);

        \apps\libs\BuildReturn::aBuildReturn($iRet);
    }
}