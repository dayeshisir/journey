<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午5:31
 */

namespace apps\controllers\member;

use apps\libs\Exception;
use apps\libs\Log;
use apps\models\user\User;

class Member extends \apps\controllers\BaseController
{
    /**
     * 收集旅游意向，也可认为是加入一个旅行
     *
     */
    public function bAdd()
    {
        $aParam = \apps\utils\member\MemberUtils::aGetAddParam();

        try {
            \apps\utils\member\MemberUtils::bAddParamValid($aParam);

            $iUid = intval(\apps\libs\Request::mGetParam('uid', ''));

            // 首先获取旅局信息，指不定没有这个局呢
            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$aParam['journey_id']]);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }
            $aJourney = $aJourney[0];
            $iTargetNum = intval($aJourney['people_num']);

            // 获取该局是否人员满了
            $aMember = \apps\models\member\Member::aGetJourneyGroup($aParam);
            $iCurNum = count($aMember);
            if ($iCurNum >= $iTargetNum || \apps\common\Constant::JOURNEY_STATUS_VOTE === intval($aJourney['status'])) {

                throw new Exception('', Exception::ERR_MEMBER_FULL_ERROR);
            }

            // 第一个加入的成员不是局的发起者，呃，什么地方出问题了
            if (!$iCurNum && $iUid !== intval($aJourney['created_uid'])) {

                throw  new Exception('', Exception::ERR_PERMISSION_ERROR);
            }

            // 先插入用户的信息
            $aUser = [
                'uid' => \apps\libs\Request::mGetParam('uid', ''),
                'portrait' => \apps\libs\Request::mGetParam('portrait', ''),
                'nick_name' => \apps\libs\Request::mGetParam('nick_name', ''),
            ];
            \apps\models\user\User::bAdd($aUser);

            // 然后是旅行团信息
            $iInsertId = \apps\models\member\Member::bAdd($aParam);

            // 队长加入了，局的状态由初始状态转为 等待成员加入
            if ($iInsertId) {
                \apps\models\journey\Journey::iWaitMember($aParam['journey_id']);
            }

            // 所有人都入局，局的状态由等待用户加入转为等待投票
            $iCurNum++;
            if ($iCurNum >= $iTargetNum) {
                \apps\models\journey\Journey::iWaitVote($aParam['journey_id']);
            }

            \apps\libs\BuildReturn::aBuildReturn(['id' => $iInsertId]);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }
}