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

            $aUser = [
                'uid' => \apps\libs\Request::mGetParam('uid', ''),
                'portrait' => \apps\libs\Request::mGetParam('portrait', ''),
                'nick_name' => \apps\libs\Request::mGetParam('nick_name', ''),
            ];

            // 先插入用户的信息
            \apps\models\user\User::bAdd($aUser);

            // 然后是旅行团信息
            $iInsertId = \apps\models\member\Member::bAdd($aParam);

            \apps\libs\BuildReturn::aBuildReturn(['id' => $iInsertId]);

        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
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
}