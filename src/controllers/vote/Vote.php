<?php
/**
 * Created by PhpStorm.
 * User: shiyibo
 * Date: 2018/9/1
 * Time: 上午9:01
 */

namespace apps\controllers\vote;


use apps\controllers\BaseController;
use apps\libs\Exception;

class Vote extends BaseController
{
    /**
     * 投票
     */
    public function aVote()
    {
        $aParam = [
            'journey_id' => intval(\apps\libs\Request::mGetParam('journey_id', 0)),
            'uid'        => \apps\libs\Request::mGetParam('uid', ''),
            'vote'       => intval(\apps\libs\Request::mGetParam('vote', 0)),
        ];
        try {
            $aJourney = \apps\models\journey\Journey::aGetDetail($aParam['journey_id']);

            $aParam['spot_id'] = $aJourney['spot_id'];

            $iNow  = time();
            $oVote = \apps\models\vote\Vote::oVote($aParam);
            $iVoteTime = strtotime($oVote->create_at);

//            if ($iNow > $iVoteTime) {
//                throw new Exception('', Exception::ERR_ALREADY_VOTED);
//            }

            \apps\libs\BuildReturn::aBuildReturn(['vote_id' => $oVote->id]);
        } catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            \apps\libs\Log::vWarning('Member::add fail', ['param' => $aParam, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }

    public function aVoteList()
    {
        $iJourneyId = intval(\apps\libs\Request::mGetParam('journey_id', 0));
        try {
            $aJourney   = \apps\models\journey\Journey::aGetDetail($iJourneyId);
            $iSpotId    = $aJourney['spot_id'];
            $aVoteList  = \apps\models\vote\Vote::aJourneyVote($iJourneyId, $iSpotId);
            $aUids      = array_column($aVoteList, 'uid');
            $aMember    = \apps\models\user\User::aGetUserByIds($aUids);
            $aMemberMap = \apps\utils\common\Util::array2map($aMember, 'uid');

            $aRet = [
                \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_NONE] => [],
                \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_NO]   => [],
                \apps\common\Config::$aVoteIndex[\apps\common\Constant::VOTE_STATUS_OK]   => [],
            ];
            foreach ($aVoteList as $vote) {
                $iVote = $vote['vote'];
                $index = \apps\common\Config::$aVoteIndex[$iVote];
                array_push($aRet[$index], $aMemberMap[$vote['uid']]);
            }

            \apps\libs\BuildReturn::aBuildReturn($aRet);

        } catch (Exception $e) {
            $errno  = $e->getCode();
            $errmsg = $e instanceof Exception ? $e->sGetUserErrmsg($e->getCode()) : $e->getMessage();
            \apps\libs\Log::vWarning('Member::add fail', ['journey_id' => $iJourneyId, 'errno' => $errno, 'msg' => $errmsg]);
            \apps\libs\BuildReturn::aBuildReturn([], $errno, $errmsg);
        }
    }
}