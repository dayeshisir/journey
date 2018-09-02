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
use apps\models\push\Push;
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
            // \apps\utils\member\MemberUtils::bAddParamValid($aParam);

            $aParam['free_time'] = json_decode($aParam['free_time'], true);

            $iUid = intval(\apps\libs\Request::mGetParam('uid', ''));

            // 首先获取旅局信息，指不定没有这个局呢
            $aJourney = \apps\models\journey\Journey::aGetDetail($aParam['journey_id']);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $iTargetNum = intval($aJourney['people_num']);

            // 获取该局是否人员满了
            $aMember = \apps\models\member\Member::aGetJourneyGroup($aParam);
            $iCurNum = count($aMember);
            if ($iCurNum >= $iTargetNum || \apps\common\Constant::JOURNEY_STATUS_VOTE === intval($aJourney['status'])) {

                throw new Exception('', Exception::ERR_MEMBER_FULL_ERROR);
            }

            // 第一个加入的成员不是局的发起者，呃，什么地方出问题了
            if (!$iCurNum && $iUid !== intval($aJourney['uid'])) {

                throw  new Exception('', Exception::ERR_PERMISSION_ERROR);
            }

            // 然后是旅行团信息
            $iForumId  = $aParam['forum_id'];
            unset($aParam['forum_id']);
            $iInsertId = \apps\models\member\Member::bAdd($aParam);

            // 队长加入了，局的状态由初始状态转为 等待成员加入
            if ($iInsertId) {
                \apps\models\journey\Journey::iWaitMember($aParam['journey_id']);
            }

            // 所有人都入局，局的状态由等待用户加入转为等待投票
            $iCurNum++;
            if ($iCurNum >= $iTargetNum) {
                \apps\models\journey\Journey::iSetMemberFull($aParam['journey_id']);

                \apps\utils\journey\JourneyUtils::aGenSpot();
            }

            // 缓存一个forum_id用来保存推送的forum_id
            \apps\models\push\Push::iAddForumId($aParam['journey_id'], $aParam['uid'], $iForumId);

            \apps\libs\BuildReturn::aBuildReturn(['id' => $iInsertId]);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 保存用户的forum_id
     */
    public function aSaveForumId()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);
        $sUid       = \apps\libs\Request::mGetParam('uid', '');
        $sForumId   = \apps\libs\Request::mGetParam('forum_id', '');

        $iRet = \apps\models\push\Push::iAddForumId($iJourneyId, $sUid, $sForumId);

        \apps\libs\BuildReturn::aBuildReturn(['ret' => $iRet]);
    }

    /**
     * 获取用户的状态信息
     *
     */
    public function aUserStatus()
    {
        $iJourney = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        $sUid     = \apps\libs\Request::mGetParam('uid', '');


        $aStatus = self::aBuildUserStatus($iJourney, $sUid);

        \apps\libs\BuildReturn::aBuildReturn($aStatus);
    }

    /**
     * @param $journey
     * @param $spot
     * @param $uid
     * @return array
     */
    public static function aBuildUserStatus($journey, $uid)
    {
        $aRet = [
            'journey_status' => \apps\common\Constant::JOURNEY_STATUS_INIT,
            'user_status'    => \apps\common\Constant::USER_STATUS_INIT,
            'is_leader'      => 0,
        ];

        $aJourney = \apps\models\journey\Journey::aGetDetail($journey);
        if (empty($aJourney)) {

            return $aRet;
        }

        $aRet['journey_status'] = $aJourney['status'];
        $aRet['is_leader'] = strval($aJourney['uid']) == strval($uid) ? 1 : 0;

        // 还没人开始加入，直接返回
        if ($aJourney['status'] < \apps\common\Constant::JOURNEY_STATUS_JOIN) {

            return $aRet;
        }

        $aMember = \apps\models\member\Member::aGetDetail(['journey_id' => $journey, 'uid' => $uid]);
        if (empty($aMember)) {

            return $aRet;
        }
        $aRet['user_status'] = \apps\common\Constant::USER_STATUS_JOIN;

        // 还没开始投票，可以返回了
        if ($aJourney['status'] < \apps\common\Constant::JOURNEY_STATUS_VOTE) {

            return $aRet;
        }

        $spot  = intval($aJourney['spot_id']);
        $aVote = \apps\models\vote\Vote::aGetUserVote($journey, $spot, $uid);
        if (empty($aVote)) {

            return $aRet;
        }

        $aRet['user_status'] = \apps\common\Constant::USER_STATUS_VOTED;

        return $aRet;
    }

}