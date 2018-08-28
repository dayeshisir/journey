<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/8/26
 * Time: 下午1:38
 */

namespace apps\controllers\journey;

use apps\libs\Exception;
use apps\libs\Log;

class Journey extends \apps\controllers\BaseController
{
    /**
     * 发起一次活动
     *
     */
    public function bAdd()
    {
        try {
            $aParam = \apps\utils\journey\JourneyUtils::aGetAddParam();

            \apps\utils\journey\JourneyUtils::bAddParamValid($aParam);

            $iInsertId = \apps\models\journey\Journey::bAdd($aParam);

            \apps\libs\BuildReturn::aBuildReturn(['id' => $iInsertId]);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Journey::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 我的旅行列表
     *
     */
    public function aJourneyList()
    {
        $iPage   = \apps\libs\Request::mGetParam('page', 1);
        $iSize   = \apps\libs\Request::mGetParam('size', 10);
        $iUserId = \apps\libs\Request::mGetParam('uid', 0);

        try {
            if (empty($iUserId) || $iUserId < 0) {

                throw new Exception('', Exception::ERR_UID_ERROR);
            }

            if ($iPage <= 0 || $iSize <= 0) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            // 第一步，根据用户的uid拉取该用户参与的旅行列表
            $aParam = ['uid' => $iUserId, 'page' => $iPage, 'size' => $iSize];
            $aList = \apps\models\member\Member::aGetUserList($aParam);
            if (empty($aList)) {
                return [];
            }

            // 根据上一步得到的旅行列表，得到journey_id,根据journey_id得到旅行信息，包括状态，队长
            $aJourneyIds = array_column($aList, 'journey_id');
            $aJourneyList  = \apps\models\journey\Journey::aGetJourneyByIds($aJourneyIds);
            $aJourneyMap = [];
            foreach ($aJourneyList as $journey) {
                $aJourneyMap[$journey['id']] = $journey;
            }

            // 第三步 获取队长的昵称、头像信息
            $aLeaderIds = array_column($aJourneyList, 'created_uid');
            $aUserInfo  = \apps\models\user\User::aGetUserByIds($aLeaderIds);
            $aUserMap = [];
            foreach ($aUserInfo as $user) {
                $aUserMap[$user['uid']] = $user;
            }

            $aRet = [];
            foreach ($aList as $list) {
                $aCurJourney = $aJourneyMap[$list['journey_id']];
                $aCurLeader  = $aUserMap[$aCurJourney['created_uid']];

                $aNew = [
                    'leader_id'       => $aCurLeader['uid'],
                    'leader_portrait' => $aCurLeader['portrait'],
                    'leader_nickname' => $aCurLeader['nick_name'],
                    'journey_status'  => $aCurJourney['status'],      // TODO 收敛到一个函数内
                ];

                $aRet[] = $aNew;
            }

            \apps\libs\BuildReturn::aBuildReturn($aRet);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => [
                'uid' => $iUserId, 'page' => $iPage, 'size' => $iSize,
            ], 'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 局友版收集用户信息时展示局头写的意向
     *
     */
    public function aGetLeaderIntention()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);

        try {
            if ($iJourneyId <= 0) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $iLeaderId = $aJourney[0]['created_uid'];
            $aParam = [
                'uid'        => $iLeaderId,
                'journey_id' => $iJourneyId,
            ];
            $aMember = \apps\models\member\Member::aGetDetail($aParam);
            if (empty($aMember)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            \apps\libs\BuildReturn::aBuildReturn($aMember);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 详情页-组局中
     */
    public function aGetJourneyIntention()
    {
        $iJourneyId = \apps\libs\Request::mGetParam('journey_id', 0);
        try {
            if ($iJourneyId <= 0) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aMember = \apps\models\member\Member::aGetJourneyGroup(['journey_id' => $iJourneyId]);
            $iAnyNum      = 0;
            $iChinaNum    = 0;
            $iInternalNum = 0;
            $aBusyTime    = [];
            foreach ($aMember as $member) {
                if (\apps\common\Constant::INTENTION_TYPE_ANY === intval($member['intention'])) {
                    $iAnyNum++;
                }
                if (\apps\common\Constant::INTENTION_TYPE_CHINA === intval($member['intention'])) {
                    $iChinaNum++;
                }
                if (\apps\common\Constant::INTENTION_TYPE_INTERNATION === intval($member['intention'])) {
                    $iInternalNum++;
                }

                $aCurBusyTime = json_decode($member['busy_time'], true);
                if (!empty($aCurBusyTime)) {
                    $aBusyTime = array_merge($aBusyTime, $aCurBusyTime);
                }
            }

            $aRet = [
                'create_time' => $aJourney[0]['created_at'],
                'duration'    => \apps\common\Constant::INTERVAL_WAIT_JOIN,
                'target_num'  => $aJourney[0]['people_num'],
                'current_num' => count($aMember),
                'intention'   => [
                    'any'         => $iAnyNum,
                    'china'       => $iChinaNum,
                    'internation' => $iInternalNum,
                ],
                'busy_time'  => $aBusyTime,
            ];

            \apps\libs\BuildReturn::aBuildReturn($aRet);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    public function iSetMemberFull()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        $iUid       = intval(\apps\libs\Request::mGetParam('uid', ''));

        try {
            if ($iJourneyId <= 0 || $iUid <= 0) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            // 拉取局的信息
            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
            if (empty($aJourney)) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }
            $aJourney = $aJourney[0];

            if ($iUid !== intval($aJourney['created_uid'])) {
                throw new Exception('', Exception::ERR_PERMISSION_ERROR);
            }

            $ret = \apps\models\journey\Journey::iSetMemberFull($iJourneyId);

            \apps\libs\BuildReturn::aBuildReturn($ret);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId, 'uid' => $iUid],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }
}