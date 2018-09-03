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
use apps\utils\strategy\StrategyUtils;

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

            // \apps\utils\journey\JourneyUtils::bAddParamValid($aParam);

            $iInsertId = \apps\models\journey\Journey::bAdd($aParam);

            // 发起之后加入一个forum_id用来发送推送，这个应该弄一个message，异步去搞
            if ($iInsertId) {
                \apps\models\push\Push::iAddForumId($iInsertId, $aParam['uid'], $aParam['forum_id']);
            }

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
    public function aPrepareJoin()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        $sUid       = \apps\libs\Request::mGetParam('uid', '');

        try {
            if ($iJourneyId <= 0) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aJourney = \apps\models\journey\Journey::aGetDetail($iJourneyId);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aRet = \apps\controllers\member\Member::aBuildUserStatus($iJourneyId, $sUid);
            $aRet['start_time'] = $aJourney['start_time'];
            $aRet['end_time']   = $aJourney['end_time'];
            $aRet['desc']       = $aJourney['desc'];

            \apps\libs\BuildReturn::aBuildReturn($aRet);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 产生一个决策
     *
     * @param $journey
     * @return mixed
     */
    protected function aGenSpot($journey)
    {
        return \apps\utils\journey\JourneyUtils::aGenSpot();

        /*$aCandidateSpots = \apps\controllers\strategy\Strategy::aGetCandidate();

        // $aCandidateMap = \apps\utils\common\Util::array2map($aCandidateSpots, 'id');

        $aSpotIds = array_keys($aCandidateSpots);

        $aUsedSpot = \apps\utils\strategy\StrategyUtils::aGetSpot($journey);

        $aUsedSpot = array_unique($aUsedSpot);

        $aNotUsedSpot = array_diff($aSpotIds, $aUsedSpot);

        // 如果随机用完了，重新来过
        if (empty($aNotUsedSpot)) {
            \apps\utils\strategy\StrategyUtils::iReset($journey);
            $aNotUsedSpot = $aSpotIds;
        }

        // 数组随机一下
        // shuffle($aNotUsedSpot);
        $iSelectedSpot = current($aNotUsedSpot);

        $aRecommandSpot = $aCandidateSpots[$iSelectedSpot]['spot'];
        $aRecommandTime = $aCandidateSpots[$iSelectedSpot]['time'];

        // 绑定到数据库
        \apps\models\journey\Journey::iUpdateSpot($journey, $aRecommandSpot['id'], $aRecommandTime);

        // 更新到redis缓存
        \apps\utils\strategy\StrategyUtils::iAdd($journey, $aRecommandSpot['id']);

        return $aRecommandSpot;*/
    }

    /**
     * 获取推荐给用户的spot
     *
     */
    public function aGetSpot()
    {
        $iJourney = intval(\apps\libs\Request::mGetParam('journey_id', 0));

        try {
            $aRecommandSpot = $this->aGenSpot($iJourney);

            \apps\libs\BuildReturn::aBuildReturn($aRecommandSpot);
        } catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Journey::aGetVoteJourney fail', ['param' => ['journey_id' => $iJourney,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 投票页面展示接口
     */
    public function aGetVoteJourney()
    {
        $iJourney = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        try {
            // 局信息
            $aJourney = \apps\models\journey\Journey::aGetDetail($iJourney);
            $aMember  = \apps\models\member\Member::aGetJourneyGroup(['journey_id' => $iJourney]);
            $aSpot    = \apps\models\spot\Spot::aGetDetail($aJourney['spot_id']);
            $aVote    = \apps\models\vote\Vote::aJourneyVote($iJourney, $aJourney['spot_id']);
            $aUid     = array_column($aMember, 'uid');
            $aUser    = \apps\models\user\User::aGetUserByIds($aUid);
            $aVoteMap = \apps\utils\journey\JourneyUtils::aGetVoteMap($aMember, $aVote, $aUser);
            $aRet = [
                'spot' => $aSpot,
                'vote_time' => $aJourney['vote_time'],
                'duration'  => \apps\common\Constant::INTERVAL_WAIT_VOTE,
                'target_num'=> count($aMember),
                'time'      => [
                    'start_time' => $aJourney['recommend_time'][0]['start_time'],
                    'end_time'   => $aJourney['recommend_time'][0]['end_time'],
                ],
                'vote' => $aVoteMap,
            ];
            \apps\libs\BuildReturn::aBuildReturn($aRet);
        }catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Journey::aGetVoteJourney fail', ['param' => ['journey_id' => $iJourney,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    public function aGetSuccJourney()
    {
        $iJourney = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        try {
            $aJourney = \apps\models\journey\Journey::aGetDetail($iJourney);
            $aSpot    = \apps\models\spot\Spot::aGetDetail($aJourney['spot_id']);
            $aMember = \apps\models\member\Member::aGetJourneyGroup(['journey_id' => $iJourney]);
            $iAnyNum      = 0;
            $iChinaNum    = 0;
            $iInternalNum = 0;
            $aFreeTime    = [];
            $aUids        = [];
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

                $aCurFreeTime = json_decode($member['free_time'], true);
                $aFreeTime[$member['uid']] = $aCurFreeTime;

                $aUids[] = $member['uid'];
            }

            $aFreeTime[$aJourney['id']] = [['start_time' => $aJourney['start_time'], 'end_time' => $aJourney['end_time']]];

            $aShowTime = \apps\utils\common\Time::aFindInValidTime($aFreeTime);

            // $iDays = \apps\utils\common\Time::iFindNearDay($aSpot['time']);

            $aRet = [
                'days'        => 10,
                'spot'        => $aSpot,
                'intention'   => [
                    'any'         => $iAnyNum,
                    'china'       => $iChinaNum,
                    'internation' => $iInternalNum,
                ],
                'busy_time'    => $aShowTime,
            ];

            \apps\libs\BuildReturn::aBuildReturn($aRet);
        } catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Journey::aGetVoteJourney fail', ['param' => ['journey_id' => $iJourney,],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 详情页-组局中
     */
    public function aGetJoinJourney()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        try {
            if ($iJourneyId <= 0) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aJourney = \apps\models\journey\Journey::aGetDetail($iJourneyId);
            if (empty($aJourney)) {

                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            $aMember = \apps\models\member\Member::aGetJourneyGroup(['journey_id' => $iJourneyId]);
            $iAnyNum      = 0;
            $iChinaNum    = 0;
            $iInternalNum = 0;
            $aFreeTime    = [];
            $aUids        = [];
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

                $aCurFreeTime = $member['free_time'];
                $aFreeTime[$member['uid']] = $aCurFreeTime;

                $aUids[] = $member['uid'];
            }

            $aFreeTime[$aJourney['id']] = [['start_time' => $aJourney['start_time'], 'end_time' => $aJourney['end_time']]];

            $aShowTime = \apps\utils\common\Time::aFindInValidTime($aFreeTime);

            // 获取用户信息
            $aUserInfo = \apps\models\user\User::aGetUserByIds($aUids);

            $aRet = [
                'create_time' => strtotime($aJourney['created_at']),
                'duration'    => \apps\common\Constant::INTERVAL_WAIT_JOIN,
                'target_num'  => $aJourney['people_num'],
                'user'        => $aUserInfo,
                'intention'   => [
                    'any'         => $iAnyNum,
                    'china'       => $iChinaNum,
                    'internation' => $iInternalNum,
                ],
                'member' => $aMember,
                'busy_time'  => $aShowTime,
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

    /**
     * 设置提前成局
     *
     */
    public function iSetMemberFull()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        $sUid       = \apps\libs\Request::mGetParam('uid', '');

        try {
            if ($iJourneyId <= 0 || empty($sUid)) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            // 拉取局的信息
            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
            if (empty($aJourney)) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }
            $aJourney = $aJourney[0];

            if ($sUid !== $aJourney['uid']) {
                throw new Exception('', Exception::ERR_PERMISSION_ERROR);
            }

            $ret = \apps\models\journey\Journey::iSetMemberFull($iJourneyId);

            // 成局，则绑定策略
            $aRecommendSpot = [];
            if ($ret) {
                $aRecommendSpot = $this->aGenSpot($iJourneyId);
            }

            $aRet = [
                'ret' => $ret,
                'recommend_spot' => $aRecommendSpot,
            ];
            \apps\libs\BuildReturn::aBuildReturn($aRet);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId, 'uid' => $sUid],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    /**
     * 提前成局
     *
     */
    public function aSetJourneySucc()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        $sUid       = \apps\libs\Request::mGetParam('uid', '');

        try {
            if ($iJourneyId <= 0 || empty($sUid)) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }

            // 拉取局的信息
            $aJourney = \apps\models\journey\Journey::aGetJourneyByIds([$iJourneyId]);
            if (empty($aJourney)) {
                throw new Exception('', Exception::ERR_PARAM_ERROR);
            }
            $aJourney = $aJourney[0];

            if ($sUid !== $aJourney['uid']) {
                throw new Exception('', Exception::ERR_PERMISSION_ERROR);
            }

            $ret = \apps\models\journey\Journey::iSucc($iJourneyId);

            \apps\libs\BuildReturn::aBuildReturn($ret);
        } catch (\Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            Log::vWarning('Member::add fail', ['param' => ['journey_id' => $iJourneyId, 'uid' => $sUid],
                'errno' => $errno, 'msg' => $errmsg]);

            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }
}