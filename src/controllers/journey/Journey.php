<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午1:38
 */

namespace apps\controllers\journey;

use apps\libs\Exception;
use apps\models\member\Member;

class Journey extends \apps\controllers\BaseController
{
    /**
     * 发起一次活动
     *
     */
    public function bAdd()
    {
        try {
            $aParam = \apps\utils\journey\JourneyUtils::getParam();

            \apps\utils\journey\JourneyUtils::bValid($aParam);

            $oQuery   = \apps\models\journey\Journey::query();
            $oJourney = $oQuery->create($aParam);

            if (null === $oJourney) {
                throw new Exception('', Exception::ERR_DB_ERROR);
            }

            // 队长作为第一个加入的成员
            $oQuery = \apps\models\member\Member::query();
            $aParam = [
                'journey_id' => $oJourney->id,
                'uid'        => \apps\libs\Request::mGetCookie('uid', 0),
                'portrait'   => \apps\libs\Request::mGetCookie('portrait', ''),
                'type'       => \apps\common\Constant::MEMBER_TYPE_LEADER,
            ];
            $oMember = $oQuery->create($aParam);

            if (null == $oMember) {
                throw new Exception('',Exception::ERR_DB_ERROR);
            }

            \apps\libs\BuildReturn::aBuildReturn(['id' => $oJourney->id]);

        } catch (\Exception $e) {
            \apps\libs\BuildReturn::aBuildReturn([], $e->getCode(), $e->getMessage());
        }
    }


}